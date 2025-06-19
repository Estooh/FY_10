<template>
  <div class="enrollment-form">
    <h2 class="title">Enroll New User</h2>

    <form @submit.prevent="submitEnrollment">
      <input v-model="fullName" type="text" placeholder="Full Name" required class="input" />
      <input v-model="email" type="email" placeholder="Email Address" required class="input" />

      <center>
        <div class="input radio-group">
        <label><input type="radio" value="face" v-model="biometricChoice" /> Use Face</label>
        <label><input type="radio" value="fingerprint" v-model="biometricChoice" style="margin-left: 20px;" /> Use Fingerprint</label>
      </div>
      </center>

      <div class="video-section" v-if="biometricChoice === 'face'">
        <video v-show="!capturedImage" ref="videoRef" autoplay playsinline muted></video>
        <img v-show="capturedImage" :src="capturedImage" alt="Captured" class="captured-img" />
      </div>

      <div class="video-section" v-if="biometricChoice === 'fingerprint'">
        <img v-if="fingerprintPattern" :src="fingerprintPattern" alt="Fingerprint pattern" class="captured-img" />
        <img v-else src="/src/assets/fingerprint.jpeg" alt="Scan Fingerprint" class="fingerprint-placeholder" />
      </div>

      <div v-if="loading" class="progress-bar">
        <div class="progress"></div>
      </div>

      <div class="buttons">
        <button
          type="button"
          class="capture-btn"
          @click="handleBiometricAction"
          :disabled="!biometricChoice || loading"
        >
          {{ biometricChoice === 'face'
              ? (livenessConfirmed ? '📸 Capture Face' : 'Start Liveness Check')
              : 'Capture Fingerprint'
          }}
        </button>
      </div>

      <p :class="['info-text', messageColor]">{{ message }}</p>
      <button type="submit" class="submit-btn" :disabled="loading">Enroll User</button>
    </form>

    <footer class="footer">&copy; 2025 Final Year Project:10. All rights reserved.</footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import * as faceapi from 'face-api.js';

const router = useRouter();

const videoRef = ref(null);
const fullName = ref('');
const email = ref('');
const biometricChoice = ref('face');

const faceDescriptor = ref(null);
const faceImage = ref(null);
const fingerprintCredential = ref(null);
const fingerprintTemplate = ref(null);
const fingerprintPattern = ref(null);

const message = ref('');
const messageColor = ref('');
const livenessConfirmed = ref(false);
const loading = ref(false);
const capturedImage = ref(null);
const challenge = ref('');

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
    .then(stream => {
      if (videoRef.value) videoRef.value.srcObject = stream;
    })
    .catch(err => {
      console.error('Video error:', err);
      message.value = 'Cannot access webcam.';
      messageColor.value = 'error';
    });
};

const getRandomChallenge = () => {
  const challenges = ['turn left', 'turn right', 'blink'];
  return challenges[Math.floor(Math.random() * challenges.length)];
};

const detectSharpChanges = (prev, current, w, h) => {
  let diff = 0;
  const threshold = 50;
  for (let i = 0; i < prev.data.length; i += 4) {
    const delta = Math.abs(prev.data[i] - current.data[i]) +
                  Math.abs(prev.data[i + 1] - current.data[i + 1]) +
                  Math.abs(prev.data[i + 2] - current.data[i + 2]);
    if (delta > threshold) diff++;
  }
  return diff > w * h * 0.05;
};

const isSpoofed = (landmarks, ctx, prevFrame, currentFrame, w, h) => {
  const eyePts = landmarks.getLeftEye().concat(landmarks.getRightEye());
  let totalB = 0;
  eyePts.forEach(pt => {
    const px = ctx.getImageData(Math.floor(pt.x), Math.floor(pt.y), 1, 1).data;
    totalB += (px[0] + px[1] + px[2]) / 3;
  });
  const avg = totalB / eyePts.length;
  const glare = avg > 240;
  const sharpChange = prevFrame && detectSharpChanges(prevFrame, currentFrame, w, h);
  return glare || sharpChange;
};

const startLivenessCheck = async () => {
  loading.value = true;
  livenessConfirmed.value = false;
  challenge.value = getRandomChallenge();
  message.value = `Please ${challenge.value}...`;
  messageColor.value = 'info';

  const start = Date.now();
  const timeout = 20000;
  let blinked = false, turned = false, prev = null;

  const loop = async () => {
    if (Date.now() - start > timeout) {
      message.value = 'Face is not detected. Try again!';
      messageColor.value = 'error';
      loading.value = false;
      return;
    }

    const detection = await faceapi
      .detectSingleFace(videoRef.value, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
      .withFaceLandmarks();

    if (!detection) {
      requestAnimationFrame(loop);
      return;
    }

    const { landmarks } = detection;
    const canvas = document.createElement('canvas');
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(videoRef.value, 0, 0);
    const current = ctx.getImageData(0, 0, canvas.width, canvas.height);

    if (prev && isSpoofed(landmarks, ctx, prev, current, canvas.width, canvas.height)) {
      message.value = 'Face not detected. Try again!';
      messageColor.value = 'error';
      loading.value = false;
      return;
    }
    prev = current;

    const ear = eye => Math.abs(eye[1].y - eye[5].y) / Math.abs(eye[0].x - eye[3].x);
    const avgEAR = (ear(landmarks.getLeftEye()) + ear(landmarks.getRightEye())) / 2;
    if (avgEAR < 0.23) blinked = true;

    const noseX = landmarks.getNose()[3].x;
    if (noseX < canvas.width * 0.4 || noseX > canvas.width * 0.6) turned = true;

    if ((challenge.value === 'blink' && blinked) ||
        (challenge.value === 'turn left' && noseX > canvas.width * 0.6) ||
        (challenge.value === 'turn right' && noseX < canvas.width * 0.4)) {
      livenessConfirmed.value = true;
      message.value = 'Face successfully detected. Now capture face!';
      messageColor.value = 'success';
      loading.value = false;
      return;
    }
    requestAnimationFrame(loop);
  };

  loop();
};

const captureFace = async () => {
  const result = await faceapi
    .detectSingleFace(videoRef.value, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
    .withFaceLandmarks().withFaceDescriptor();

  if (!result) {
    message.value = 'Only one face must be visible.';
    messageColor.value = 'error';
    return;
  }

  faceDescriptor.value = Array.from(result.descriptor);
  const canvas = document.createElement('canvas');
  canvas.width = videoRef.value.videoWidth;
  canvas.height = videoRef.value.videoHeight;
  canvas.getContext('2d').drawImage(videoRef.value, 0, 0);
  faceImage.value = canvas.toDataURL('image/jpeg');
  capturedImage.value = faceImage.value;
  message.value = 'Face captured successfully.';
  messageColor.value = 'success';
};

const captureFingerprint = async () => {
  loading.value = true;
  try {
    const publicKey = {
      challenge: crypto.getRandomValues(new Uint8Array(32)),
      rp: { name: 'Biometric App (localhost)' },
      user: {
        id: new TextEncoder().encode(email.value || 'user@localhost'),
        name: email.value || 'user@localhost',
        displayName: fullName.value || 'Test User',
      },
      pubKeyCredParams: [{ alg: -7, type: 'public-key' }],
      authenticatorSelection: {
        authenticatorAttachment: 'platform',
        userVerification: 'required',
      },
      timeout: 60000,
      attestation: 'none',
    };

    const credential = await navigator.credentials.create({ publicKey });
    if (!credential) throw new Error('No credential returned.');

    fingerprintCredential.value = credential.id;
    fingerprintTemplate.value = '/fingerprint.avif';
    capturedImage.value = fingerprintTemplate.value;
    message.value = 'Fingerprint captured successfully.';
    messageColor.value = 'success';
  } catch (err) {
    message.value = `Fingerprint capture failed: ${err.name} - ${err.message}`;
    messageColor.value = 'error';
  } finally {
    loading.value = false;
  }
};

const handleBiometricAction = async () => {
  if (biometricChoice.value === 'face') {
    if (!livenessConfirmed.value) {
      await startLivenessCheck();
    } else {
      await captureFace();
    }
  } else {
    await captureFingerprint();
  }
};

const submitEnrollment = async () => {
  if (!fullName.value || !email.value) {
    message.value = 'Enter name and email.';
    messageColor.value = 'error';
    return;
  }
  if (biometricChoice.value === 'face' && (!faceDescriptor.value || !faceImage.value)) {
    message.value = 'Capture face before submitting.';
    messageColor.value = 'error';
    return;
  }
  if (biometricChoice.value === 'fingerprint' && (!fingerprintCredential.value || !fingerprintTemplate.value)) {
    message.value = 'Capture fingerprint before submitting.';
    messageColor.value = 'error';
    return;
  }

  loading.value = true;
  const payload = {
    full_name: fullName.value,
    email: email.value,
    biometric_method: biometricChoice.value,
    face_descriptor: biometricChoice.value === 'face' ? faceDescriptor.value : null,
    face_image: biometricChoice.value === 'face' ? faceImage.value : null,
    fingerprint_template: biometricChoice.value === 'fingerprint' ? fingerprintTemplate.value : null,
    fingerprint_credential: biometricChoice.value === 'fingerprint' ? fingerprintCredential.value : null,
  };

  try {
    const res = await fetch('http://localhost:8000/api/enroll-user', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    if (res.ok) {
      message.value = 'User enrolled successfully!';
      messageColor.value = 'success';
      setTimeout(() => router.push('/auth/'), 1500);
    } else {
      message.value = 'Enrollment failed.';
      messageColor.value = 'error';
    }
  } catch (err) {
    console.error(err);
    message.value = 'Network error. Try again.';
    messageColor.value = 'error';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await loadFaceModels();
  startVideo();
});
</script>

<style scoped>
.title { font-size: 20px; font-family: Arial, sans-serif; }
.enrollment-form { max-width: 420px; margin: auto; padding: 30px; text-align: center; }
.input { width: 80%; padding: 10px; margin-bottom: 15px; border: 1px solid #2c7be5; color: #000; }
.radio-group { text-align: center; margin-bottom: 20px; }
.video-section { margin: 20px auto; border: 2px solid #ccc; border-radius: 8px; height: 150px; width: 150px; }
video, .captured-img { width: 100%; height: 100%; object-fit: cover; }
.progress-bar { width: 100%; height: 8px; background: #eee; border-radius: 4px; overflow: hidden; }
.progress { width: 100%; height: 100%; background: #2c7be5; animation: progressAnim 1.4s linear infinite; }
@keyframes progressAnim { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
.capture-btn, .submit-btn { margin-top: 10px; padding: 10px 20px; color: white; border: none; border-radius: 5px; }
.fingerprint-placeholder {
  width: 120px;
  height: 125px;
  margin: 10px auto;
  display: block;
}

.capture-btn { background-color: #2c7be5; }
.capture-btn:hover { background-color: #1a5cbf; }
.submit-btn { background-color: #28a745; }
.submit-btn:hover { background-color: #218838; }
.info-text { margin-top: 10px; font-weight: bold; }
.success { color: green; }
.error { color: red; }
.info { color: gray; }
.footer { margin-top: 30px; font-size: 12px; color: #aaa; }
</style>
