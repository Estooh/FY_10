<template>
  <div class="auth-form">
    <h2 class="title">Biometric Authentication Framework</h2>

    <div class="video-section">
      <video v-show="!authenticatedImage" ref="videoRef" autoplay playsinline muted></video>
      <img v-show="authenticatedImage" :src="authenticatedImage" alt="Captured" class="captured-img" />
    </div>

    <div v-if="loading" class="progress-bar">
      <div class="progress"></div>
    </div>

    <div class="buttons">
      <button @click="handleLivenessThenAuthenticate" class="auth-btn">
        <Eye size="20" /> Authenticate Face
      </button>
      <button @click="authenticateFingerprint" class="auth-btn">
        <Fingerprint size="20" /> Authenticate Fingerprint
      </button>
    </div>

    <p class="help-text">
      Don't have an account?
      <router-link to="/enroll-user" class="enroll-link">Enroll Now!</router-link>
    </p>

    <p :class="['message', messageColor]">{{ message }}</p>

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
const authenticatedImage = ref(null);
const faceDescriptor = ref(null);
const message = ref('');
const messageColor = ref('');
const challenge = ref('');
const livenessPassed = ref(false);
const nonce = ref('');

const getCookie = (name) => {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
};

const loadModels = async () => {
  const MODEL_URL = '/models';
  await Promise.all([
    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
  ]);
};

const startVideo = () => {
  navigator.mediaDevices.getUserMedia({ video: true }).then((stream) => {
    if (videoRef.value) videoRef.value.srcObject = stream;
  }).catch((err) => {
    console.error('Webcam error:', err);
    message.value = 'Webcam access denied!';
    messageColor.value = 'error';
  });
};

const getRandomChallenge = () => {
  const challenges = ['blink', 'turn left', 'turn right'];
  return challenges[Math.floor(Math.random() * challenges.length)];
};

const performLivenessCheck = async () => {
  challenge.value = getRandomChallenge();
  message.value = `Please ${challenge.value}...`;
  messageColor.value = '';
  loading.value = true;

  const timeout = 30000;
  const startTime = Date.now();
  let blinked = false;
  let turned = false;

  const check = async () => {
    if (Date.now() - startTime > timeout) {
      message.value = 'Face not detected. Try again...!';
      messageColor.value = 'error';
      loading.value = false;
      return;
    }

    const detections = await faceapi.detectAllFaces(videoRef.value, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 })).withFaceLandmarks();

    if (detections.length !== 1) {
      requestAnimationFrame(check);
      return;
    }

    const { landmarks } = detections[0];
    const leftEye = landmarks.getLeftEye();
    const rightEye = landmarks.getRightEye();
    const noseX = landmarks.getNose()[3].x;
    const canvasWidth = videoRef.value.videoWidth;

    const EAR = (eye) => Math.abs(eye[1].y - eye[5].y) / Math.abs(eye[0].x - eye[3].x);
    const avgEAR = (EAR(leftEye) + EAR(rightEye)) / 2;
    if (avgEAR < 0.23) blinked = true;
    if (noseX < canvasWidth * 0.4 || noseX > canvasWidth * 0.6) turned = true;

    const passed =
      (challenge.value === 'blink' && blinked) ||
      (challenge.value === 'turn left' && noseX > canvasWidth * 0.6) ||
      (challenge.value === 'turn right' && noseX < canvasWidth * 0.4);

    if (passed) {
      livenessPassed.value = true;
      message.value = 'Face is successfully detected. Now authenticate...!';
      messageColor.value = 'success';
      loading.value = false;
    } else {
      requestAnimationFrame(check);
    }
  };

  requestAnimationFrame(check);
};

const fetchNonce = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/auth/nonce');
    if (response.ok) {
      const data = await response.json();
      nonce.value = data.nonce;
    } else {
      throw new Error('Failed to fetch nonce');
    }
  } catch (error) {
    console.error('Nonce fetch failed:', error);
    message.value = 'Security error: unable to fetch nonce.';
    messageColor.value = 'error';
  }
};

const authenticateFace = async () => {
  loading.value = true;

  const detections = await faceapi
    .detectAllFaces(videoRef.value, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
    .withFaceLandmarks()
    .withFaceDescriptors();

  if (detections.length !== 1) {
    message.value = 'Ensure only your face is visible!';
    messageColor.value = 'error';
    loading.value = false;
    return;
  }

  const descriptor = Array.from(detections[0].descriptor);
  faceDescriptor.value = descriptor;

  const canvas = document.createElement('canvas');
  canvas.width = videoRef.value.videoWidth;
  canvas.height = videoRef.value.videoHeight;
  canvas.getContext('2d').drawImage(videoRef.value, 0, 0);
  authenticatedImage.value = canvas.toDataURL('image/jpeg');

  try {
    await fetch('http://localhost:8000/sanctum/csrf-cookie', { credentials: 'include' });
    const csrfToken = getCookie('XSRF-TOKEN');

    const res = await fetch('http://localhost:8000/api/auth/face', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify({ descriptor, nonce: nonce.value }),
    });

    if (res.ok) {
      const data = await res.json();
      localStorage.setItem('token', data.token || 'dummy-token');
      message.value = 'Authenticated successfully!';
      messageColor.value = 'success';
      await router.push('/dashboard');
    } else {
      const errData = await res.json();
      message.value = errData.message || 'Face not recognized.';
      messageColor.value = 'error';
    }
  } catch (error) {
    console.error(error);
    message.value = 'Authentication failed. Server error.';
    messageColor.value = 'error';
  }

  loading.value = false;
};

const handleLivenessThenAuthenticate = async () => {
  if (!nonce.value) {
    await fetchNonce();
    if (!nonce.value) return; // Stop if nonce failed to fetch
  }

  if (!livenessPassed.value) {
    await performLivenessCheck();
  }

  if (livenessPassed.value) {
    await authenticateFace();
  }
};


const authenticateFingerprint = async () => {
  try {
    const cred = await navigator.credentials.get({ publicKey: { challenge: new Uint8Array(32) } });
    if (cred) {
      message.value = 'Fingerprint is recognized successfully!';
      messageColor.value = 'success';
      localStorage.setItem('token', 'dummy-fingerprint-token');
      await router.push('/dashboard');
    } else {
      message.value = 'Fingerprint is not recognized. Try again...!';
      messageColor.value = 'error';
    }
  } catch (err) {
    message.value = 'Fingerprint error!';
    messageColor.value = 'error';
  }
};

onMounted(async () => {
  await loadModels();
  startVideo();
});
</script>



<style scoped>
.title {
  font-size: 20px;
  font-family: Arial, sans-serif;
}
.auth-form {
  max-width: 480px;
  margin: auto;
  text-align: center;
  padding: 30px;
}
.video-section {
  margin: 20px auto;
  width: 150px;
  height: 150px;
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
  color: #e42424;
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
