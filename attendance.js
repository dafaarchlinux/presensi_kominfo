console.log("attendance.js LOADED (ADMIN)");

// ================= DOM =================
const video = document.getElementById("video");
const statusEl = document.getElementById("status");
const alertBox = document.getElementById("alert");
const btnMasuk = document.getElementById("btnMasuk");
const btnPulang = document.getElementById("btnPulang");

if (!video || !statusEl || !alertBox || !btnMasuk || !btnPulang) {
    throw new Error("DOM tidak lengkap");
}

// ================= STATE =================
let stream = null;
let selectedType = null;
let processing = false;

let faceMatcher = null;
let detectedPnsId = null;

// smoothing
let lastMatchId = null;
let matchCount = 0;

// ================= INIT =================
(async function init() {
    statusEl.innerText = "Memuat model wajah...";
    await loadModels();
    await initFaceMatcher();
    statusEl.innerText = "Silakan pilih Absen Masuk / Pulang";
})();

// ================= BUTTON =================
btnMasuk.onclick = async () => start("masuk");
btnPulang.onclick = async () => start("pulang");

async function start(type) {
    if (processing) return;

    selectedType = type;
    detectedPnsId = null;
    lastMatchId = null;
    matchCount = 0;

    processing = true;
    statusEl.innerText = `Mode Absen ${type.toUpperCase()}`;

    await startCamera();
    requestAnimationFrame(recognizeLoop);
}

// ================= FACE API =================
async function loadModels() {
    const MODEL_URL = "/face-api/models";
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
}

async function initFaceMatcher() {
    const res = await fetch("/face/embeddings", {
        headers: { Accept: "application/json" },
    });

    const data = await res.json();

    const labeledDescriptors = Object.keys(data).map((pnsId) => {
        const descriptors = data[pnsId].map(d => new Float32Array(d));
        return new faceapi.LabeledFaceDescriptors(pnsId, descriptors);
    });

    faceMatcher = new faceapi.FaceMatcher(labeledDescriptors, 0.6);
}

// ================= CAMERA =================
async function startCamera() {
    if (stream) return;

    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user" },
            audio: false,
        });

        video.srcObject = stream;
        await video.play();
    } catch {
        processing = false;
        tampilkanAlert("Kamera tidak dapat diakses.", "error");
    }
}

// ================= FACE RECOGNITION LOOP =================
async function recognizeLoop() {
    if (!processing || !faceMatcher || !stream) return;

    const detection = await faceapi
        .detectSingleFace(
            video,
            new faceapi.TinyFaceDetectorOptions({
                inputSize: 320,
                scoreThreshold: 0.5,
            })
        )
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!detection || detection.detection.box.area < 3500) {
        statusEl.innerText = "Dekatkan wajah ke kamera";
        return requestAnimationFrame(recognizeLoop);
    }

    const match = faceMatcher.findBestMatch(detection.descriptor);

    if (match.label === "unknown" || match.distance > 0.6) {
        lastMatchId = null;
        matchCount = 0;
        statusEl.innerText = "Wajah tidak dikenali";
        return requestAnimationFrame(recognizeLoop);
    }

    if (match.label === lastMatchId) {
        matchCount++;
    } else {
        lastMatchId = match.label;
        matchCount = 1;
    }

    statusEl.innerText = `Mengenali wajah... (${matchCount}/3)`;

    if (matchCount < 3) {
        return requestAnimationFrame(recognizeLoop);
    }

    detectedPnsId = match.label;
    processing = false;

    statusEl.innerText = "Wajah dikenali. Memverifikasi presensi...";
    await submitAttendance();
}

// ================= SUBMIT =================
async function submitAttendance() {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    try {
        const res = await fetch("/admin/presensi", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf,
                Accept: "application/json",
            },
            body: JSON.stringify({
                pns_id: detectedPnsId,
                type: selectedType,
            }),
        });

        const data = await res.json();
        const nama = data.nama ?? "PNS";

        alertBox.style.display = "block";

        // ================= PESAN MANUSIAWI =================
        if (!res.ok || !data.success) {
            let pesan = data.message;

            if (pesan.includes("Belum saatnya")) {
                pesan = `${nama} belum dapat melakukan presensi ${selectedType.toUpperCase()} karena belum saatnya jam absen.`;
            } else if (pesan.includes("sudah melakukan presensi")) {
                pesan = `${nama} telah melakukan presensi ${selectedType.toUpperCase()} hari ini.`;
            }

            alertBox.className = "alert error";
            alertBox.innerText = pesan;
            return;
        }

        // ================= PRESENSI BERHASIL =================
        let pesanSukses = data.message;

        if (pesanSukses.includes("TERLAMBAT")) {
            pesanSukses = `${nama} terlambat melakukan presensi ${selectedType.toUpperCase()} hari ini.`;
        } else {
            pesanSukses = `Selamat, ${nama} berhasil melakukan presensi ${selectedType.toUpperCase()} tepat waktu.`;
        }

        alertBox.className = "alert success";
        alertBox.innerText = pesanSukses;

        stopCamera();
        setTimeout(() => location.reload(), 1500);

    } catch {
        alertBox.className = "alert error";
        alertBox.innerText = "Terjadi kesalahan sistem.";
    }
}

// ================= ALERT =================
function tampilkanAlert(pesan, tipe) {
    alertBox.style.display = "block";
    alertBox.className = `alert ${tipe}`;
    alertBox.innerText = pesan;
}

// ================= STOP =================
function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(t => t.stop());
        stream = null;
    }
    video.srcObject = null;
}
