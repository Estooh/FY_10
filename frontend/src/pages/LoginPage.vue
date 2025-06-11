<template>
  <div class="auth-form">
    <h2 class="title">Biometric Authentication</h2>

    <div class="video-section">
      <video v-show="!authenticatedImage" ref="videoRef" autoplay playsinline muted></video>
      <img v-show="authenticatedImage" :src="authenticatedImage" alt="Captured" class="captured-img" />
    </div>

    <div v-if="loading" class="progress-bar">
      <div class="progress"></div>
    </div>

    <div class="buttons">
      <button @click="handleLivenessThenDetect" class="auth-btn">
        <Eye size="20" /> Authenticate Face
      </button>
      <button @click="authenticateFingerprint" class="auth-btn">
        <Fingerprint size="20" /> Authenticate Fingerprint
      </button>
    </div>

    <p class="help-text">
      Already registered?
      <router-link to="/enroll-user" class="enroll-link">Enroll Now!</router-link>
    </p>

    <p class="message" :class="{ success: faceDetected, error: !faceDetected && message }">{{ message }}</p>

    <footer class="footer">&copy; 2025 Final Year Project:10. All rights reserved.</footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Eye, Fingerprint } from 'lucide-vue-next';
import * as faceapi from 'face-api.js';

const router = useRouter();
const videoRef = ref(null);
const loading = ref(false);
const livenessConfirmed = ref(false);
const authenticatedImage = ref(null);
const faceDescriptor = ref(null);
const message = ref('');
const challenge = ref('');
const faceDetected = ref(false);

const loadFaceModels = async () => {
  const MODEL_URL = '/models';
  await Promise.all([
    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
  ]);
};

const startVideo = () => {
  navigator.mediaDevices.getUserMedia({ video: true })
    .then((stream) => {
      if (videoRef.value) videoRef.value.srcObject = stream;
    })
    .catch((err) => console.error('Webcam access error:', err));
};

const getRandomChallenge = () => {
  const challenges = ['blink', 'turn left', 'turn right'];
  return challenges[Math.floor(Math.random() * challenges.length)];
};

const detectSharpChanges = (prevFrame, currentFrame, width, height) => {
  let diffPixels = 0;
  const threshold = 50;
  for (let i = 0; i < prevFrame.data.length; i += 4) {
    const diff = Math.abs(prevFrame.data[i] - currentFrame.data[i]) +
                 Math.abs(prevFrame.data[i + 1] - currentFrame.data[i + 1]) +
                 Math.abs(prevFrame.data[i + 2] - currentFrame.data[i + 2]);
    if (diff > threshold) diffPixels++;
  }
  return diffPixels > (width * height * 0.05);
};

const isSpoofed = (landmarks, canvasCtx, prevFrame, currentFrame, width, height) => {
  const glarePoints = landmarks.getLeftEye().concat(landmarks.getRightEye());
  let totalBrightness = 0;
  glarePoints.forEach(p => {
    const pixel = canvasCtx.getImageData(p.x, p.y, 1, 1).data;
    totalBrightness += (pixel[0] + pixel[1] + pixel[2]) / 3;
  });
  const avgBrightness = totalBrightness / glarePoints.length;
  return avgBrightness > 240 || detectSharpChanges(prevFrame, currentFrame, width, height);
};

const performLivenessCheck = async () => {
  loading.value = true;
  challenge.value = getRandomChallenge();
  message.value = `Please ${challenge.value}...`;

  const timeout = 30000;
  const startTime = Date.now();
  let blinked = false;
  let turned = false;
  let prevFrame = null;

  const checkLoop = async () => {
    if (Date.now() - startTime > timeout) {
      message.value = '❌ Liveness check failed.';
      loading.value = false;
      return;
    }

    const detections = await faceapi
      .detectAllFaces(videoRef.value, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
      .withFaceLandmarks();

    if (detections.length === 0) {
      requestAnimationFrame(checkLoop);
      return;
    }

    if (detections.length > 1) {
      message.value = '❌ Multiple faces detected. Only one face allowed.';
      loading.value = false;
      return;
    }

    const detection = detections[0];
    const { landmarks } = detection;
    const nose = landmarks.getNose();
    const leftEye = landmarks.getLeftEye();
    const rightEye = landmarks.getRightEye();

    const canvas = document.createElement('canvas');
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(videoRef.value, 0, 0);
    const currentFrame = ctx.getImageData(0, 0, canvas.width, canvas.height);

    if (prevFrame && isSpoofed(landmarks, ctx, prevFrame, currentFrame, canvas.width, canvas.height)) {
      message.value = '❌ Spoofing detected!';
      loading.value = false;
      return;
    }

    prevFrame = currentFrame;

    const ear = (eye) => Math.abs(eye[1].y - eye[5].y) / Math.abs(eye[0].x - eye[3].x);
    const avgEAR = (ear(leftEye) + ear(rightEye)) / 2;
    if (avgEAR < 0.23) blinked = true;

    const noseX = nose[3].x;
    if (noseX < canvas.width * 0.4 || noseX > canvas.width * 0.6) turned = true;

    const passed =
      (challenge.value === 'blink' && blinked) ||
      (challenge.value === 'turn right' && noseX < canvas.width * 0.4) ||
      (challenge.value === 'turn left' && noseX > canvas.width * 0.6);

    if (passed) {
      livenessConfirmed.value = true;
      message.value = '✅ Liveness passed!';
      loading.value = false;
    } else {
      requestAnimationFrame(checkLoop);
    }
  };

  requestAnimationFrame(checkLoop);
};

const authenticateFace = async () => {
  loading.value = true;

  try {
    const detections = await faceapi
      .detectAllFaces(videoRef.value, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
      .withFaceLandmarks()
      .withFaceDescriptors();

    if (detections.length === 0) {
      message.value = '❌ No face detected.';
      return;
    }

    if (detections.length > 1) {
      message.value = '❌ Multiple faces detected. Only one face allowed.';
      return;
    }

    const detection = detections[0];
    faceDescriptor.value = Array.from(detection.descriptor);

    const canvas = document.createElement('canvas');
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    canvas.getContext('2d').drawImage(videoRef.value, 0, 0);
    const imageData = canvas.toDataURL('image/jpeg');
    authenticatedImage.value = imageData;

    const res = await fetch('http://127.0.0.1:8000/api/auth/face', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ descriptor: faceDescriptor.value }),
    });

    if (res.ok) {
      faceDetected.value = true;
      message.value = '✅ Face authenticated. Redirecting...';
      setTimeout(() => router.push('/dashboard'), 1500);
    } else {
      faceDetected.value = false;
      message.value = '❌ Face not recognized.';
    }
  } catch (e) {
    console.error('Error:', e);
    message.value = '❌ Authentication error.';
  } finally {
    loading.value = false;
  }
};

const handleLivenessThenDetect = async () => {
  if (!livenessConfirmed.value) {
    await performLivenessCheck();
  } else {
    await authenticateFace();
  }
};

const authenticateFingerprint = async () => {
  try {
    const cred = await navigator.credentials.get({ publicKey: { challenge: new Uint8Array(32) } });
    if (cred) {
      message.value = '✅ Fingerprint authenticated. Redirecting...';
      setTimeout(() => router.push('/dashboard'), 1500);
    } else {
      message.value = '❌ Fingerprint not verified.';
    }
  } catch {
    message.value = '❌ Fingerprint authentication failed.';
  }
};

onMounted(async () => {
  await loadFaceModels();
  startVideo();
});
</script>

<style scoped>
.title{
  font-size: 40px;
  font-family: Arial, Helvetica, sans-serif;
}
.auth-form {
  max-width: 460px;
  margin: auto;
  text-align: center;
  padding: 30px;
}
.video-section {
  margin: 20px auto;
  width: 180px;
  height: 180px;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid #ccc;
}
video, .captured-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.progress-bar {
  width: 100%;
  height: 8px;
  background-color: #eee;
  margin-top: 12px;
}
.progress {
  width: 100%;
  height: 100%;
  background-color: #2c7be5;
  animation: loadingAnim 2s linear infinite;
}
@keyframes loadingAnim {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}
.buttons {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-top: 20px;
}
.auth-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background-color: #2c7be5;
  color: white;
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
}
.auth-btn:hover {
  background-color: #1a5cbf;
}
.help-text {
  margin-top: 12px;
  color: #666;
}
.enroll-link {
  color: #2c7be5;
  text-decoration: none;
}
.message {
  margin-top: 10px;
  font-weight: bold;
}
.message.success {
  color: green;
}
.message.error {
  color: red;
}
.footer {
  margin-top: 50px;
  font-size: 13px;
  color: #aaa;
}

</style>
