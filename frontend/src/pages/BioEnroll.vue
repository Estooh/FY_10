<template>
  <div class="enrollment-form">
    <h2 class="title">Enroll New User</h2>

    <form @submit.prevent="submitEnrollment">
      <input type="text" v-model="fullName" placeholder="Full Name" required class="input" />
      <input type="email" v-model="email" placeholder="Email Address" required class="input" />

      <div class="input">
        <label><input type="radio" value="face" v-model="biometricChoice" /> Use Face</label>
        <label style="margin-left: 20px;"><input type="radio" value="fingerprint" v-model="biometricChoice" /> Use Fingerprint</label>
      </div>

      <center>
        <div class="video-section">
          <video v-show="!capturedImage" ref="videoRef" autoplay playsinline muted></video>
          <img v-show="capturedImage" :src="capturedImage" alt="Captured" class="captured-img" />
        </div>
      </center>

      <div v-if="loading" class="progress-bar">
        <div class="progress"></div>
      </div>

      <div class="buttons">
        <button type="button" @click="handleBiometricAction" :disabled="!biometricChoice || loading" class="capture-btn">
          {{ biometricChoice === 'face' ? (livenessConfirmed ? 'Capture Face' : 'Start Liveness Check') : 'Capture Fingerprint' }}
        </button>
      </div>

      <p class="info-text">{{ message }}</p>
      <button type="submit" class="submit-btn" :disabled="loading">Enroll User</button>
    </form>
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
const message = ref('');
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
    .then((stream) => {
      if (videoRef.value) videoRef.value.srcObject = stream;
    })
    .catch((err) => console.error('❌ Cannot access webcam!', err));
};

const getRandomChallenge = () => {
  const challenges = ['turn left', 'turn right', 'blink'];
  return challenges[Math.floor(Math.random() * challenges.length)];
};

const detectSharpChanges = (prevFrame, currentFrame, width, height) => {
  const threshold = 50;
  let diffPixels = 0;
  for (let i = 0; i < prevFrame.data.length; i += 4) {
    const diff = Math.abs(prevFrame.data[i] - currentFrame.data[i]) +
                 Math.abs(prevFrame.data[i + 1] - currentFrame.data[i + 1]) +
                 Math.abs(prevFrame.data[i + 2] - currentFrame.data[i + 2]);
    if (diff > threshold) diffPixels++;
  }
  return diffPixels > (width * height * 0.05);
};

const isSpoofed = (landmarks, canvasCtx, prevFrame, currentFrame, width, height) => {
  const glareRegion = landmarks.getLeftEye().concat(landmarks.getRightEye());
  let totalBrightness = 0;

  glareRegion.forEach(point => {
    const x = Math.floor(point.x);
    const y = Math.floor(point.y);
    const pixel = canvasCtx.getImageData(x, y, 1, 1).data;
    const brightness = (pixel[0] + pixel[1] + pixel[2]) / 3;
    totalBrightness += brightness;
  });

  const avgBrightness = totalBrightness / glareRegion.length;
  const screenGlareDetected = avgBrightness > 240;

  const sharpChangeDetected = detectSharpChanges(prevFrame, currentFrame, width, height);

  return screenGlareDetected || sharpChangeDetected;
};

const startLivenessCheck = async () => {
  loading.value = true;
  challenge.value = getRandomChallenge();
  message.value = `👁️ Please ${challenge.value}...`;

  const timeout = 15000;
  const startTime = Date.now();
  let blinked = false;
  let turned = false;
  let prevFrame = null;

  const detectActions = async () => {
    const elapsed = Date.now() - startTime;
    if (elapsed > timeout) {
      message.value = "❌ Liveness check failed. Try again.";
      loading.value = false;
      return;
    }

    const detections = await faceapi
      .detectSingleFace(videoRef.value, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
      .withFaceLandmarks();

    if (!detections) {
      requestAnimationFrame(detectActions);
      return;
    }

    const landmarks = detections.landmarks;
    const leftEye = landmarks.getLeftEye();
    const rightEye = landmarks.getRightEye();
    const nose = landmarks.getNose();
    const canvas = document.createElement('canvas');
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(videoRef.value, 0, 0);
    const currentFrame = ctx.getImageData(0, 0, canvas.width, canvas.height);

    if (prevFrame && isSpoofed(landmarks, ctx, prevFrame, currentFrame, canvas.width, canvas.height)) {
      message.value = "❌ Spoofing attempt detected (glare or abnormal frame change).";
      loading.value = false;
      return;
    }
    prevFrame = currentFrame;

    const eyeOpenRatio = (eye) => Math.abs(eye[1].y - eye[5].y) / Math.abs(eye[0].x - eye[3].x);
    const leftRatio = eyeOpenRatio(leftEye);
    const rightRatio = eyeOpenRatio(rightEye);
    const avgEAR = (leftRatio + rightRatio) / 2;
    const CLOSED_THRESHOLD = 0.23;
    if (avgEAR < CLOSED_THRESHOLD) blinked = true;

    const noseX = nose[3].x;
    if (noseX < videoRef.value.videoWidth * 0.4 || noseX > videoRef.value.videoWidth * 0.6) turned = true;

    if ((challenge.value === 'blink' && blinked) ||
        (challenge.value === 'turn right' && noseX < videoRef.value.videoWidth * 0.4) ||
        (challenge.value === 'turn left' && noseX > videoRef.value.videoWidth * 0.6)) {
      livenessConfirmed.value = true;
      message.value = "✅ Liveness confirmed. Now capture face.";
      loading.value = false;
      return;
    }

    requestAnimationFrame(detectActions);
  };

  detectActions();
};

const captureFace = async () => {
  const detections = await faceapi
    .detectSingleFace(videoRef.value, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
    .withFaceLandmarks()
    .withFaceDescriptor();

  if (!detections) {
    message.value = "❌ Only one face must be visible.";
    return;
  }

  faceDescriptor.value = Array.from(detections.descriptor);
  const canvas = document.createElement('canvas');
  canvas.width = videoRef.value.videoWidth;
  canvas.height = videoRef.value.videoHeight;
  canvas.getContext('2d').drawImage(videoRef.value, 0, 0);
  const imageData = canvas.toDataURL('image/jpeg');
  faceImage.value = imageData;
  capturedImage.value = imageData;
  message.value = "✅ Face captured successfully.";
};

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
      authenticatorSelection: {
        authenticatorAttachment: "platform",
        userVerification: "preferred",
      },
      timeout: 10000,
      attestation: "none",
    };

    const credential = await navigator.credentials.create({ publicKey });
    fingerprintCredential.value = credential.id;

    const canvas = document.createElement('canvas');
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    canvas.getContext('2d').drawImage(videoRef.value, 0, 0);
    const imageData = canvas.toDataURL('image/jpeg');
    fingerprintTemplate.value = imageData;
    capturedImage.value = imageData;

    message.value = "✅ Fingerprint captured using WebAuthn.";
  } catch (error) {
    console.error('Fingerprint error:', error);
    message.value = "❌ Fingerprint capture failed.";
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
    message.value = "❗ Please enter full name and email.";
    return;
  }

  if (biometricChoice.value === 'face' && (!faceDescriptor.value || !faceImage.value)) {
    message.value = "❗ Please capture a face image.";
    return;
  }

  if (biometricChoice.value === 'fingerprint' && (!fingerprintCredential.value || !fingerprintTemplate.value)) {
    message.value = "❗ Please capture a fingerprint.";
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
    const response = await fetch('http://localhost:8000/api/enroll-user', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    if (response.ok) {
      message.value = "🎉 User enrolled successfully! Redirecting...";
      setTimeout(() => {
        fullName.value = '';
        email.value = '';
        faceDescriptor.value = null;
        faceImage.value = null;
        fingerprintCredential.value = null;
        fingerprintTemplate.value = null;
        livenessConfirmed.value = false;
        capturedImage.value = null;
        router.push('/auth/');
      }, 1500);
    } else {
      message.value = "❌ Enrollment failed. Try again.";
    }
  } catch (error) {
    console.error('Submit error:', error);
    message.value = "❌ Enrollment failed.";
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
.enrollment-form {
  max-width: 420px;
  margin: auto;
  padding: 30px;
  text-align: center;
}
.input {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
}
.video-section {
  margin: 20px 0;
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
.capture-btn {
  margin: 10px;
  padding: 10px 20px;
  background-color: #2c7be5;
  color: white;
  border: none;
  border-radius: 5px;
}
.capture-btn:hover {
  background-color: #1a5cbf;
}
.submit-btn {
  margin-top: 20px;
  padding: 12px 24px;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 5px;
}
.submit-btn:hover {
  background-color: #218838;
}
.info-text {
  margin-top: 10px;
  color: #555;
}
</style>