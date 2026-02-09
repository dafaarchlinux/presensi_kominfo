console.log("attendance-user.js LOADED");

/* ===============================
   DOM
================================ */
const video = document.getElementById("video");
const statusEl = document.getElementById("status");
const alertBox = document.getElementById("alert");
const btnMasuk = document.getElementById("btnMasuk");
const btnPulang = document.getElementById("btnPulang");
const locationText = document.getElementById("locationText");

if (!video || !statusEl || !alertBox || !btnMasuk || !btnPulang || !locationText) {
    throw new Error("DOM tidak lengkap");
}

/* ===============================
   LOGIN DATA
================================ */
const PNS_LOGIN_ID = document.querySelector('meta[name="pns-id-login"]')?.content;
const PNS_LOGIN_NAMA = document.querySelector('meta[name="pns-nama-login"]')?.content;

/* ===============================
   STATE
================================ */
let stream = null;
let selectedType = null;
let processing = false;

let faceMatcher = null;
let detectedPnsId = null;
let lastMatchId = null;
let matchCount = 0;

/* ===============================
   MAP & GPS STATE
================================ */
let map, userMarker, officeMarker, officeCircle;
let watchId = null;
let currentLocation = null;

/* ===============================
   OFFICE CONFIG
================================ */
const OFFICE_LAT = -7.423633899458331;   // Kantor Terpadu Pemda Sragen
const OFFICE_LNG = 111.00744679061334;
const OFFICE_RADIUS = 1000; // meter

/* ===============================
   INIT
================================ */
(async function init() {
    disableButtons();
    statusEl.innerText = "Memuat sistem...";

    await loadModels();
    await initFaceMatcher();
    initMap();
    aktifkanGPS(); // 🔥 GPS NYALA DARI AWAL

    statusEl.innerText = "Menunggu lokasi & pilihan presensi";
})();

/* ===============================
   BUTTON
================================ */
btnMasuk.onclick = () => startPresensi("masuk");
btnPulang.onclick = () => startPresensi("pulang");

function disableButtons() {
    btnMasuk.disabled = true;
    btnPulang.disabled = true;
}

function enableButtons() {
    btnMasuk.disabled = false;
    btnPulang.disabled = false;
}

/* ===============================
   FACE API
================================ */
async function loadModels() {
    const MODEL_URL = "/face-api/models";
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
}

async function initFaceMatcher() {
    const res = await fetch("/face/embeddings");
    const data = await res.json();

    const labeledDescriptors = Object.keys(data).map(pnsId => {
        const descriptors = data[pnsId].map(d => new Float32Array(d));
        return new faceapi.LabeledFaceDescriptors(pnsId, descriptors);
    });

    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);
}

/* ===============================
   CAMERA
================================ */
async function startCamera() {
    if (stream) return;

    stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: "user" },
        audio: false,
    });
    video.srcObject = stream;
    await video.play();
}

/* ===============================
   PRESENSI FLOW
================================ */
async function startPresensi(type) {
    if (!currentLocation) {
        tampilkanAlert("GPS belum aktif.", "error");
        return;
    }

    if (!isInsideOffice(currentLocation)) {
        tampilkanAlert(
            "Anda berada di luar area Kantor Terpadu Pemda Sragen.",
            "error"
        );
        return;
    }

    selectedType = type;
    processing = true;
    matchCount = 0;
    lastMatchId = null;

    statusEl.innerText = "Validasi wajah...";
    disableButtons();

    await startCamera();
    requestAnimationFrame(recognizeLoop);
}

async function recognizeLoop() {
    if (!processing) return;

    const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!detection) {
        statusEl.innerText = "Arahkan wajah ke kamera";
        return requestAnimationFrame(recognizeLoop);
    }

    const match = faceMatcher.findBestMatch(detection.descriptor);

    if (match.label !== String(PNS_LOGIN_ID)) {
        statusEl.innerText = "Wajah tidak sesuai akun";
        return requestAnimationFrame(recognizeLoop);
    }

    matchCount++;
    if (matchCount < 3) {
        statusEl.innerText = `Verifikasi wajah (${matchCount}/3)`;
        return requestAnimationFrame(recognizeLoop);
    }

    stopCamera();
    processing = false;

    submitPresensi(currentLocation);
}

/* ===============================
   MAP & GPS
================================ */
function initMap() {
    map = L.map("map").setView([OFFICE_LAT, OFFICE_LNG], 17);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);

    officeMarker = L.marker([OFFICE_LAT, OFFICE_LNG]).addTo(map);
    officeCircle = L.circle([OFFICE_LAT, OFFICE_LNG], {
        radius: OFFICE_RADIUS,
        color: "#2563eb",
        fillOpacity: 0.2,
    }).addTo(map);
}

function aktifkanGPS() {
    locationText.innerText = "Mengaktifkan GPS...";

    watchId = navigator.geolocation.watchPosition(
        pos => {
            currentLocation = {
                latitude: pos.coords.latitude,
                longitude: pos.coords.longitude,
                accuracy: pos.coords.accuracy,
            };

            locationText.innerText =
                `GPS aktif ±${Math.round(pos.coords.accuracy)} m`;

            if (!userMarker) {
                userMarker = L.marker([
                    currentLocation.latitude,
                    currentLocation.longitude
                ]).addTo(map);
            } else {
                userMarker.setLatLng([
                    currentLocation.latitude,
                    currentLocation.longitude
                ]);
            }

            map.setView([
                currentLocation.latitude,
                currentLocation.longitude
            ], 17);

            enableButtons();
        },
        () => {
            locationText.innerText = "GPS gagal diaktifkan";
        },
        {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 10000,
        }
    );
}

/* ===============================
   DISTANCE
================================ */
function isInsideOffice(loc) {
    const R = 6371000;
    const dLat = toRad(loc.latitude - OFFICE_LAT);
    const dLng = toRad(loc.longitude - OFFICE_LNG);

    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(toRad(OFFICE_LAT)) *
        Math.cos(toRad(loc.latitude)) *
        Math.sin(dLng / 2) ** 2;

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const distance = R * c;

    return distance <= OFFICE_RADIUS;
}

function toRad(deg) {
    return deg * Math.PI / 180;
}

/* ===============================
   SUBMIT
================================ */
async function submitPresensi(lokasi) {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const res = await fetch("/pns/presensi", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrf,
        },
        body: JSON.stringify({
            pns_id: PNS_LOGIN_ID,
            latitude: lokasi.latitude,
            longitude: lokasi.longitude,
            accuracy: lokasi.accuracy,
            type: selectedType,
        }),
    });

    const data = await res.json();
    tampilkanAlert(data.message, data.success ? "success" : "error");
}

/* ===============================
   UTIL
================================ */
function tampilkanAlert(pesan, tipe) {
    alertBox.style.display = "block";
    alertBox.className = `alert ${tipe}`;
    alertBox.innerText = pesan;
}

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(t => t.stop());
        stream = null;
    }
    video.srcObject = null;
}
