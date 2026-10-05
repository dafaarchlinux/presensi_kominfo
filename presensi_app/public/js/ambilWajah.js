console.log("ambilWajah.js LOADED - Fully Automatic Multi-Angle Capture");

const video = document.getElementById("video");
const statusEl = document.getElementById("status");

let collectedDescriptors = [];
const targetAngles = ["Tampak Depan", "Samping Kiri", "Samping Kanan"];
let currentAngleIndex = 0;
let isCapturing = false;
let stableFrames = 0;

async function loadFaceApiModels() {
    try {
        if (statusEl) statusEl.innerText = "Memuat model AI wajah...";
        const MODEL_URL = "/face-api/models";

        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);

        if (statusEl) statusEl.innerText = "Model siap. Menyalakan kamera...";
        startWebcam();
    } catch (err) {
        console.error("Gagal memuat model AI:", err);
        if (statusEl) statusEl.innerText = "Gagal memuat model AI: " + err.message;
    }
}

function startWebcam() {
    if (!video) return;
    if (video.srcObject) return;

    navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } })
        .then(stream => {
            video.srcObject = stream;
            video.play();
            updateInstruction();

            video.addEventListener("play", () => {
                initFaceDetection();
            });
        })
        .catch(err => {
            console.error("Gagal kamera:", err);
            if (statusEl) statusEl.innerText = "Gagal akses kamera: " + err.message;
        });
}

function updateStepUI(index) {
    for (let i = 0; i < 3; i++) {
        const stepEl = document.getElementById("step-" + i);
        if (stepEl) {
            if (i < index) {
                stepEl.style.backgroundColor = "#16a34a";
                stepEl.style.color = "#ffffff";
                stepEl.innerHTML = (i + 1) + ". Selesai Terekam ?";
            } else if (i === index) {
                stepEl.style.backgroundColor = "#eab308";
                stepEl.style.color = "#000000";
                stepEl.style.fontWeight = "bold";
            } else {
                stepEl.style.backgroundColor = "";
                stepEl.style.color = "";
            }
        }
    }
}

function updateInstruction() {
    updateStepUI(currentAngleIndex);
    if (currentAngleIndex < targetAngles.length) {
        if (statusEl) statusEl.innerHTML = "Posisikan wajah: <b style=\"color: #facc15;\">" + targetAngles[currentAngleIndex] + "</b> (Tahan sebentar...)";
    } else {
        if (statusEl) statusEl.innerText = "Semua sampel wajah terkumpul. Menyimpan ke database...";
    }
}

function initFaceDetection() {
    let canvas = document.getElementById("overlay");
    if (!canvas) return;

    const displaySize = { width: video.videoWidth || 640, height: video.videoHeight || 480 };
    faceapi.matchDimensions(canvas, displaySize);

    const detectionInterval = setInterval(async () => {
        if (currentAngleIndex >= targetAngles.length || isCapturing) return;

        const detections = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
        const ctx = canvas.getContext("2d");
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if (detections) {
            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            faceapi.draw.drawDetections(canvas, resizedDetections);
            faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);

            stableFrames++;
            if (stableFrames > 12) {
                stableFrames = 0;
                isCapturing = true;

                collectedDescriptors.push(Array.from(detections.descriptor));
                currentAngleIndex++;

                if (currentAngleIndex < targetAngles.length) {
                    updateInstruction();
                    setTimeout(() => { isCapturing = false; }, 1200);
                } else {
                    updateInstruction();
                    clearInterval(detectionInterval);
                    kirimDataOtomatis();
                }
            }
        } else {
            stableFrames = 0;
            if (statusEl && currentAngleIndex < targetAngles.length) {
                statusEl.innerHTML = "Mencari wajah... Posisikan: <b style=\"color: #facc15;\">" + targetAngles[currentAngleIndex] + "</b>";
            }
        }
    }, 100);
}

async function kirimDataOtomatis() {
    try {
        const pnsId = document.getElementById("face-container")?.getAttribute("data-pns-id");
        if (!pnsId) throw new Error("ID PNS tidak ditemukan.");

        const response = await fetch("/api/simpan-wajah-pns", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                pns_id: pnsId,
                descriptors: collectedDescriptors
            })
        });

        const result = await response.json();
        if (result.success) {
            alert("Berhasil! 3 sudut wajah berhasil direkam dan disimpan ke database.");
            window.location.href = "/admin/p-n-s";
        } else {
            throw new Error(result.message || "Gagal menyimpan");
        }
    } catch (err) {
        console.error("Gagal menyimpan:", err);
        alert("Terjadi kesalahan saat menyimpan ke database: " + err.message);
    }
}

const checkReadyInterval = setInterval(() => {
    const videoEl = document.getElementById("video");
    if (videoEl && typeof faceapi !== "undefined") {
        loadFaceApiModels();
        clearInterval(checkReadyInterval);
    }
}, 300);

