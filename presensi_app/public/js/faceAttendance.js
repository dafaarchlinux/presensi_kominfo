console.log("faceAttendance.js Loaded - Non-Inverted Typography Engine");

let currentMode = "masuk";
let labeledFaceDescriptors = [];
let faceMatcher = null;
let isProcessingAttendance = false;
let attendanceCooldown = false;

const video = document.getElementById("video-attendance");
const overlay = document.getElementById("overlay-attendance");
const notifEl = document.getElementById("screen-notification");
const infoMode = document.getElementById("info-current-mode");
const infoStatus = document.getElementById("info-status-detail");
const infoNama = document.getElementById("info-nama");
const infoKet = document.getElementById("info-keterangan");

window.gantiModePresensi = function(mode) {
    currentMode = mode;
    const btnMasuk = document.getElementById("btn-mode-masuk");
    const btnPulang = document.getElementById("btn-mode-pulang");

    if (mode === "masuk") {
        btnMasuk.style.background = "#16a34a";
        btnMasuk.style.borderColor = "#16a34a";
        btnMasuk.style.color = "#ffffff";
        btnMasuk.style.boxShadow = "0 4px 12px rgba(22, 163, 74, 0.25)";

        btnPulang.style.background = "#f8fafc";
        btnPulang.style.borderColor = "#cbd5e1";
        btnPulang.style.color = "#64748b";
        btnPulang.style.boxShadow = "none";

        if (infoMode) {
            infoMode.innerText = "PRESENSI MASUK";
            infoMode.style.color = "#16a34a";
            infoMode.style.background = "#f0fdf4";
            infoMode.style.borderColor = "#bbf7d0";
        }
    } else {
        btnPulang.style.background = "#dc2626";
        btnPulang.style.borderColor = "#dc2626";
        btnPulang.style.color = "#ffffff";
        btnPulang.style.boxShadow = "0 4px 12px rgba(220, 38, 38, 0.25)";

        btnMasuk.style.background = "#f8fafc";
        btnMasuk.style.borderColor = "#cbd5e1";
        btnMasuk.style.color = "#64748b";
        btnMasuk.style.boxShadow = "none";

        if (infoMode) {
            infoMode.innerText = "PRESENSI PULANG";
            infoMode.style.color = "#dc2626";
            infoMode.style.background = "#fef2f2";
            infoMode.style.borderColor = "#fecaca";
        }
    }
};

async function initFaceAttendanceSystem() {
    try {
        if (notifEl) {
            notifEl.innerText = "Memuat modul kecerdasan buatan biometrik...";
            notifEl.style.background = "#1e293b";
        }

        const MODEL_URL = "/face-api/models";
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

        if (notifEl) notifEl.innerText = "Menyinkronkan basis data biometrik pegawai...";
        await loadLabeledFaces();

        if (notifEl) notifEl.innerText = "Menghubungkan perangkat kamera...";
        startAttendanceCamera();
    } catch (err) {
        console.error("Inisialisasi presensi gagal:", err);
        if (notifEl) {
            notifEl.innerText = "Gagal memuat modul kecerdasan buatan: " + err.message;
            notifEl.style.background = "#b91c1c";
        }
    }
}

async function loadLabeledFaces() {
    const res = await fetch("/api/get-pns-faces");
    const json = await res.json();

    if (!json.success || !json.data || json.data.length === 0) {
        console.warn("Belum ada data biometrik pegawai yang terdaftar di sistem.");
        return;
    }

    labeledFaceDescriptors = json.data.map(pns => {
        let rawEmbeddings = pns.face_embedding;
        if (typeof rawEmbeddings === "string") {
            try { rawEmbeddings = JSON.parse(rawEmbeddings); } catch(e){}
        }

        const descriptors = [];
        if (Array.isArray(rawEmbeddings)) {
            rawEmbeddings.forEach(item => {
                if (Array.isArray(item)) {
                    descriptors.push(new Float32Array(item));
                } else if (typeof item === "object" && item !== null) {
                    descriptors.push(new Float32Array(Object.values(item)));
                }
            });
        }

        return descriptors.length > 0
            ? new faceapi.LabeledFaceDescriptors(`${pns.id}___${pns.nama}___${pns.nip}`, descriptors)
            : null;
    }).filter(item => item !== null);

    if (labeledFaceDescriptors.length > 0) {
        faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.52);
    }
}

function startAttendanceCamera() {
    if (!video) return;

    navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
        .then(stream => {
            video.srcObject = stream;
            video.play();

            if (notifEl) {
                notifEl.innerText = "Sistem Aktif. Silakan posisikan wajah menghadap kamera.";
                notifEl.style.background = "#047857";
            }

            video.addEventListener("play", () => {
                runAttendanceDetection();
            });
        })
        .catch(err => {
            console.error("Kamera gagal diakses:", err);
            if (notifEl) {
                notifEl.innerText = "Akses kamera gagal: " + err.message;
                notifEl.style.background = "#b91c1c";
            }
        });
}

function drawMirroredBox(ctx, canvasWidth, box, text, color) {
    const mirroredX = canvasWidth - box.x - box.width;
    const y = box.y;
    const w = box.width;
    const h = box.height;

    // Gambar Border Box
    ctx.lineWidth = 3;
    ctx.strokeStyle = color;
    ctx.strokeRect(mirroredX, y, w, h);

    // Hitung Dimensi Header Label Teks
    ctx.font = "bold 14px 'Plus Jakarta Sans', sans-serif";
    const textWidth = ctx.measureText(text).width;
    const padding = 6;
    const headerHeight = 24;

    // Background Label Teks
    ctx.fillStyle = color;
    ctx.fillRect(mirroredX - 1.5, Math.max(0, y - headerHeight), textWidth + (padding * 2), headerHeight);

    // Font Label Teks
    ctx.fillStyle = "#ffffff";
    ctx.textBaseline = "middle";
    ctx.fillText(text, mirroredX + padding, Math.max(0, y - headerHeight) + (headerHeight / 2));
}

function runAttendanceDetection() {
    if (!overlay || !video) return;

    const displaySize = { width: video.clientWidth || 640, height: video.clientHeight || 480 };
    faceapi.matchDimensions(overlay, displaySize);

    setInterval(async () => {
        if (isProcessingAttendance || attendanceCooldown) return;

        const detections = await faceapi.detectAllFaces(video).withFaceLandmarks().withFaceDescriptors();
        const ctx = overlay.getContext("2d");
        ctx.clearRect(0, 0, overlay.width, overlay.height);

        if (detections && detections.length > 0) {
            const resizedDetections = faceapi.resizeResults(detections, displaySize);

            resizedDetections.forEach(detection => {
                const box = detection.detection.box;
                let label = "Memverifikasi...";
                let boxColor = "#eab308"; // Kuning default

                if (faceMatcher) {
                    const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                    if (bestMatch.label !== "unknown") {
                        const [id, nama, nip] = bestMatch.label.split("___");
                        const akurasi = Math.round((1 - bestMatch.distance) * 100);
                        label = `${nama} (${akurasi}%)`;
                        boxColor = "#16a34a"; // Hijau terverifikasi
                        prosesAbsensiOtomatis(id, nama, nip);
                    } else {
                        label = "Wajah Belum Terdaftar";
                        boxColor = "#dc2626"; // Merah belum terdaftar
                    }
                }

                drawMirroredBox(ctx, overlay.width, box, label, boxColor);
            });
        }
    }, 200);
}

async function prosesAbsensiOtomatis(pnsId, nama, nip) {
    if (isProcessingAttendance || attendanceCooldown) return;
    isProcessingAttendance = true;

    if (notifEl) {
        notifEl.innerText = `Memproses verifikasi: ${nama}...`;
        notifEl.style.background = "#d97706";
    }

    try {
        const response = await fetch("/api/simpan-presensi", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                pns_id: pnsId,
                mode: currentMode
            })
        });

        const result = await response.json();

        if (infoNama) infoNama.innerText = `${nama} (${nip})`;
        if (infoKet) infoKet.innerText = result.waktu || new Date().toLocaleTimeString();

        if (result.success) {
            if (notifEl) {
                notifEl.innerText = `? ${result.message} - ${nama}`;
                notifEl.style.background = "#15803d";
            }
            if (infoStatus) {
                infoStatus.innerHTML = `<b style="color: #16a34a;">${result.message}</b><br>Waktu: ${result.waktu}`;
            }
        } else {
            if (notifEl) {
                notifEl.innerText = result.message;
                notifEl.style.background = "#b91c1c";
            }
            if (infoStatus) {
                infoStatus.innerHTML = `<b style="color: #dc2626;">${result.message}</b><br>${result.waktu ? "Tercatat: " + result.waktu : ""}`;
            }
        }
    } catch (err) {
        console.error("Gagal melakukan pencatatan:", err);
    } finally {
        isProcessingAttendance = false;
        attendanceCooldown = true;
        setTimeout(() => {
            attendanceCooldown = false;
            if (notifEl) {
                notifEl.innerText = "Sistem Aktif. Silakan posisikan wajah menghadap kamera.";
                notifEl.style.background = "#047857";
            }
        }, 4000);
    }
}

if (typeof faceapi !== "undefined") {
    initFaceAttendanceSystem();
} else {
    window.addEventListener("load", () => {
        if (typeof faceapi !== "undefined") initFaceAttendanceSystem();
    });
}
