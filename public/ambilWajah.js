console.log("ambilWajah.js LOADED");

// ================= DOM =================
const video = document.getElementById("video");
const statusEl = document.getElementById("status");
const preview = document.getElementById("preview");
const alertBox = document.getElementById("alert");
const overlay = document.getElementById("overlay");

const container = document.querySelector("[data-pns-id]");
const pnsId = container?.dataset.pnsId;

if (!pnsId) {
    console.error("PNS ID TIDAK DITEMUKAN");
}

// ================= STATE =================
let stream = null;
let displaySize = null;
let isRunning = false;

// pose wajib
const REQUIRED_POSES = ["front", "left", "right"];
let currentPoseIndex = 0;

// simpan hasil
const captured = {
    front: null,
    left: null,
    right: null,
};

// ================= START =================
async function ambilWajah() {
    if (isRunning) return;
    isRunning = true;

    setStatus("Memuat model wajah...");
    await loadModels();

    await startCamera();
    setStatus("Hadapkan wajah ke kamera (DEPAN)");

    requestAnimationFrame(detectLoop);
}

// ================= MODEL =================
async function loadModels() {
    const MODEL_URL = "/face-api/models";

    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
}

// ================= CAMERA =================
async function startCamera() {
    stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: "user" },
        audio: false,
    });

    video.srcObject = stream;
    await video.play();

    displaySize = {
        width: video.videoWidth,
        height: video.videoHeight,
    };

    overlay.width = displaySize.width;
    overlay.height = displaySize.height;

    faceapi.matchDimensions(overlay, displaySize);
}

// ================= DETECT =================
async function detectLoop() {
    const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();

    clearOverlay();

    if (!detection) {
        setStatus("Wajah tidak terdeteksi");
        return requestAnimationFrame(detectLoop);
    }

    const resized = faceapi.resizeResults(detection, displaySize);
    drawOverlay(resized);

    const pose = detectPose(resized);
    const expectedPose = REQUIRED_POSES[currentPoseIndex];

    setStatus(
        `Pose terdeteksi: ${pose.toUpperCase()} | Arahkan: ${expectedPose.toUpperCase()}`
    );

    if (pose === expectedPose && !captured[pose]) {
        captured[pose] = {
            embedding: Array.from(detection.descriptor),
            image: captureImage(resized),
        };

        previewFace(resized);
        notify(`Pose ${pose.toUpperCase()} berhasil direkam`);

        currentPoseIndex++;

        if (currentPoseIndex >= REQUIRED_POSES.length) {
            await submitAll();
            finish();
            return;
        }

        setTimeout(() => requestAnimationFrame(detectLoop), 800);
        return;
    }

    requestAnimationFrame(detectLoop);
}

// ================= POSE DETECTION =================
function detectPose(detection) {
    const landmarks = detection.landmarks;
    const box = detection.detection.box;

    const leftEye = landmarks.getLeftEye();
    const rightEye = landmarks.getRightEye();
    const nose = landmarks.getNose();

    const eyeCenterX = (leftEye[0].x + rightEye[3].x) / 2;
    const noseX = nose[3].x;

    const ratio = (noseX - eyeCenterX) / box.width;

    if (ratio < -0.08) return "left";
    if (ratio > 0.08) return "right";
    return "front";
}

// ================= SUBMIT =================
async function submitAll() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrf) {
        notify("CSRF token tidak ditemukan", "error");
        return;
    }

    for (const pose of REQUIRED_POSES) {
        const payload = captured[pose];

        await fetch("/face/enroll", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf,
                Accept: "application/json",
            },
            body: JSON.stringify({
                pns_id: pnsId,
                embedding: payload.embedding,
                image: payload.image,
            }),
        });
    }

    notify("Semua pose wajah berhasil disimpan");
}

// ================= UTILS =================
function captureImage(detection) {
    const box = detection.detection.box;
    const canvas = document.createElement("canvas");

    canvas.width = box.width;
    canvas.height = box.height;

    const ctx = canvas.getContext("2d");
    ctx.drawImage(
        video,
        box.x,
        box.y,
        box.width,
        box.height,
        0,
        0,
        box.width,
        box.height
    );

    return canvas.toDataURL("image/png");
}

function previewFace(detection) {
    if (!preview) return;

    const img = document.createElement("img");
    img.src = captureImage(detection);
    img.className = "preview-face";
    preview.appendChild(img);
}

function finish() {
    stopCamera();
    setStatus("Wajah berhasil didaftarkan (3 pose)");
    setTimeout(() => location.reload(), 1500);
}

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(t => t.stop());
        stream = null;
    }
    video.srcObject = null;
}

function setStatus(text) {
    statusEl.innerText = text;
}

function notify(text, type = "success") {
    alertBox.style.display = "block";
    alertBox.innerText = text;
    alertBox.className =
        "alert " + (type === "success" ? "success" : "error");
}

function clearOverlay() {
    overlay.getContext("2d").clearRect(0, 0, overlay.width, overlay.height);
}

function drawOverlay(detection) {
    faceapi.draw.drawDetections(overlay, detection);
    faceapi.draw.drawFaceLandmarks(overlay, detection);
}
