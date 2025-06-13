<template>
  <div class="enrollment-form">
    <h2 class="title">Enroll New User</h2>

    <form @submit.prevent="submitEnrollment">
      <input v-model="fullName" type="text" placeholder="Full Name" required class="input" />
      <input v-model="email" type="email" placeholder="Email Address" required class="input" />

      <center>
        <div class="input" style="text-align: center;">
        <label><input type="radio" value="face" v-model="biometricChoice" /> Use Face</label>
        <label style="margin-left: 20px;"><input type="radio" value="fingerprint" v-model="biometricChoice" /> Use Fingerprint</label>
      </div>
      </center>

      <div class="video-section" v-if="biometricChoice === 'face'" style="text-align: center;">
        <video v-show="!capturedImage" ref="videoRef" autoplay playsinline muted></video>
        <img v-show="capturedImage" :src="capturedImage" alt="Captured" class="captured-img" />
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

// Refs and State
const videoRef = ref(null);
const fullName = ref('');
const email = ref('');
const biometricChoice = ref('face');
const faceDescriptor = ref(null);
const faceImage = ref(null);
const fingerprintCredential = ref(null);
const fingerprintTemplate = ref(null);
const message = ref('');
const messageColor = ref('');
const livenessConfirmed = ref(false);
const loading = ref(false);
const capturedImage = ref(null);
const challenge = ref('');

// Load models
const loadFaceModels = async () => {
  const MODEL_URL = '/models';
  await Promise.all([
    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
  ]);
};

// Start webcam
const startVideo = () => {
  navigator.mediaDevices.getUserMedia({ video: true }).then(stream => {
    if (videoRef.value) videoRef.value.srcObject = stream;
  });
};

// Challenge generator
const getRandomChallenge = () => {
  const challenges = ['turn left', 'turn right', 'blink'];
  return challenges[Math.floor(Math.random() * challenges.length)];
};

// Sharp brightness/luminance change detector
const detectSharpChanges = (prev, current, width, height) => {
  let diff = 0;
  const threshold = 50;
  for (let i = 0; i < prev.data.length; i += 4) {
    const delta = Math.abs(prev.data[i] - current.data[i]) +
                  Math.abs(prev.data[i + 1] - current.data[i + 1]) +
                  Math.abs(prev.data[i + 2] - current.data[i + 2]);
    if (delta > threshold) diff++;
  }
  return diff > width * height * 0.05;
};

// Spoofing detection (glare + sharp frame diff)
const isSpoofed = (landmarks, ctx, prevFrame, currentFrame, width, height) => {
  const eyePoints = landmarks.getLeftEye().concat(landmarks.getRightEye());
  let totalBrightness = 0;
  eyePoints.forEach(pt => {
    const px = ctx.getImageData(Math.floor(pt.x), Math.floor(pt.y), 1, 1).data;
    totalBrightness += (px[0] + px[1] + px[2]) / 3;
  });
  const avgBrightness = totalBrightness / eyePoints.length;
  const glareDetected = avgBrightness > 240;
  const sharpChange = detectSharpChanges(prevFrame, currentFrame, width, height);
  return glareDetected || sharpChange;
};

// Liveness check loop
const startLivenessCheck = async () => {
  loading.value = true;
  challenge.value = getRandomChallenge();
  message.value = `Please ${challenge.value}...`;
  messageColor.value = 'info';

  const timeout = 20000;
  const start = Date.now();
  let blinked = false, turned = false, prev = null;

  const loop = async () => {
    if (Date.now() - start > timeout) {
      message.value = "Liveness check failed. Try again.";
      messageColor.value = 'error';
      loading.value = false;
      return;
    }

    const detection = await faceapi.detectSingleFace(videoRef.value, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
      .withFaceLandmarks();
    if (!detection) {
      requestAnimationFrame(loop);
      return;
    }

    const landmarks = detection.landmarks;
    const leftEye = landmarks.getLeftEye();
    const rightEye = landmarks.getRightEye();
    const nose = landmarks.getNose();

    const canvas = document.createElement('canvas');
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(videoRef.value, 0, 0);
    const current = ctx.getImageData(0, 0, canvas.width, canvas.height);

    if (prev && isSpoofed(landmarks, ctx, prev, current, canvas.width, canvas.height)) {
      message.value = "Spoofing detected. Aborted.";
      messageColor.value = 'error';
      loading.value = false;
      return;
    }
    prev = current;

    const ear = (eye) => Math.abs(eye[1].y - eye[5].y) / Math.abs(eye[0].x - eye[3].x);
    const avgEAR = (ear(leftEye) + ear(rightEye)) / 2;
    if (avgEAR < 0.23) blinked = true;

    const noseX = nose[3].x;
    if (noseX < canvas.width * 0.4 || noseX > canvas.width * 0.6) turned = true;

    if ((challenge.value === 'blink' && blinked) ||
        (challenge.value === 'turn left' && noseX > canvas.width * 0.6) ||
        (challenge.value === 'turn right' && noseX < canvas.width * 0.4)) {
      livenessConfirmed.value = true;
      message.value = "Liveness confirmed. Now capture face.";
      messageColor.value = 'success';
      loading.value = false;
      return;
    }
    requestAnimationFrame(loop);
  };

  loop();
};

// Face capture after liveness confirmation
const captureFace = async () => {
  const result = await faceapi.detectSingleFace(videoRef.value, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
    .withFaceLandmarks().withFaceDescriptor();
  if (!result) {
    message.value = "Only one face must be visible.";
    messageColor.value = 'info';
    return;
  }

  faceDescriptor.value = Array.from(result.descriptor);
  const canvas = document.createElement('canvas');
  canvas.width = videoRef.value.videoWidth;
  canvas.height = videoRef.value.videoHeight;
  canvas.getContext('2d').drawImage(videoRef.value, 0, 0);
  const dataURL = canvas.toDataURL('image/jpeg');
  faceImage.value = dataURL;
  capturedImage.value = dataURL;

  message.value = "Face captured successfully.";
  messageColor.value = 'success';
};

// Fingerprint capture using WebAuthn
const captureFingerprint = async () => {
  loading.value = true;
  try {
    const publicKey = {
      challenge: new Uint8Array(32),
      rp: { name: "Biometric App" },
      user: {
        id: new TextEncoder().encode(email.value),
        name: email.value,
        displayName: fullName.value,
      },
      pubKeyCredParams: [{ alg: -7, type: "public-key" }],
      authenticatorSelection: { authenticatorAttachment: "platform", userVerification: "preferred" },
      timeout: 20000,
      attestation: "none",
    };

    const credential = await navigator.credentials.create({ publicKey });
    fingerprintCredential.value = credential.id;

    const canvas = document.createElement('canvas');
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    canvas.getContext('2d').drawImage(videoRef.value, 0, 0);
    const template = canvas.toDataURL('image/jpeg');

    fingerprintTemplate.value = template;
    capturedImage.value = template;
    message.value = "Fingerprint captured.";
    messageColor.value = 'success';
  } catch (err) {
    message.value = "Fingerprint capture failed.";
    messageColor.value = 'error';
  } finally {
    loading.value = false;
  }
};

// Handle capture button
const handleBiometricAction = async () => {
  if (biometricChoice.value === 'face') {
    if (!livenessConfirmed.value) await startLivenessCheck();
    else await captureFace();
  } else {
    await captureFingerprint();
  }
};

// Submit enrollment
const submitEnrollment = async () => {
  if (!fullName.value || !email.value) {
    message.value = "Enter name and email.";
    messageColor.value = 'error';
    return;
  }

  if (biometricChoice.value === 'face' && (!faceDescriptor.value || !faceImage.value)) {
    message.value = "Capture face before submitting.";
    messageColor.value = 'error';
    return;
  }

  if (biometricChoice.value === 'fingerprint' && (!fingerprintCredential.value || !fingerprintTemplate.value)) {
    message.value = "Capture fingerprint before submitting.";
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
      message.value = "User enrolled successfully! Redirecting...";
      messageColor.value = 'success';
      setTimeout(() => {
        fullName.value = '';
        email.value = '';
        livenessConfirmed.value = false;
        faceDescriptor.value = null;
        faceImage.value = null;
        fingerprintCredential.value = null;
        fingerprintTemplate.value = null;
        capturedImage.value = null;
        router.push('/auth/');
      }, 1500);
    } else {
      message.value = "Enrollment failed.";
      messageColor.value = 'error';
    }
  } catch (err) {
    message.value = "Network error. Try again.";
    messageColor.value = 'error';
  } finally {
    loading.value = false;
  }
};

// Load models and start webcam on mount
onMounted(async () => {
  await loadFaceModels();
  startVideo();
});
</script>

<style scoped>
.title{
  font-size: 20px;
  font-family: Arial, Helvetica, sans-serif;
}
.enrollment-form {
  max-width: 420px;
  margin: auto;
  padding: 30px;
  text-align: center;
}
.input {
  width: 80%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #2c7be5;
  color: #000;
}
.video-section {
  margin: 20px auto;
  border: 2px solid #ccc;
  border-radius: 8px;
  height: 150px;
  width: 150px;
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
  border-radius: 4px;
  overflow: hidden;
}
.progress {
  width: 100%;
  height: 100%;
  background-color: #2c7be5;
  animation: progressAnim 1.5s linear infinite;
}
@keyframes progressAnim {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}
.capture-btn, .submit-btn {
  margin-top: 10px;
  padding: 10px 20px;
  color: white;
  border: none;
  border-radius: 5px;
}
.capture-btn {
  background-color: #2c7be5;
}
.capture-btn:hover {
  background-color: #1a5cbf;
}
.submit-btn {
  background-color: #28a745;
}
.submit-btn:hover {
  background-color: #218838;
}
.info-text {
  margin-top: 10px;
  font-weight: bold;
}
.success { color: green; }
.error { color: red; }
.info { color: gray; }
.footer {
  margin-top: 30px;
  font-size: 12px;
  color: #aaa;
}
</style>
