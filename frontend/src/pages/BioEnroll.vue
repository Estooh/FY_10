<template>
  <div class="enrollment-form">
    <h2 class="title">Enroll New User</h2>

    <form @submit.prevent="submitEnrollment">
      <input type="text" v-model="fullName" placeholder="Full Name" required class="input" />
      <input type="email" v-model="email" placeholder="Email Address" required class="input" />

      <!-- Biometric Choice -->
      <div class="input">
        <label><input type="radio" value="face" v-model="biometricChoice" /> Use Face</label>
        <label style="margin-left: 20px;"><input type="radio" value="fingerprint" v-model="biometricChoice" /> Use Fingerprint</label>
      </div>

      <center>
        <div class="video-section">
          <video ref="videoRef" autoplay playsinline muted></video>
        </div>
      </center>

      <div class="buttons">
        <button type="button" @click="captureFace" :disabled="biometricChoice !== 'face'" class="capture-btn">
          Capture Face
        </button>
        <button type="button" @click="captureFingerprint" :disabled="biometricChoice !== 'fingerprint'" class="capture-btn">
          Capture Fingerprint
        </button>
      </div>

      <p class="info-text">{{ message }}</p>

      <button type="submit" class="submit-btn">Enroll User</button>
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
const biometricChoice = ref('face'); // 'face' or 'fingerprint'
const faceDescriptor = ref(null);
const faceImage = ref(null);
const fingerprintCredential = ref(null);
const fingerprintTemplate = ref(null);
const message = ref('');

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
      if (videoRef.value) {
        videoRef.value.srcObject = stream;
      }
    })
    .catch((err) => console.error('Webcam error:', err));
};

const captureFace = async () => {
  const options = new faceapi.TinyFaceDetectorOptions();
  const detections = await faceapi
    .detectAllFaces(videoRef.value, options)
    .withFaceLandmarks()
    .withFaceDescriptors();

  if (detections.length > 0) {
    faceDescriptor.value = Array.from(detections[0].descriptor);

    const canvas = document.createElement('canvas');
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    canvas.getContext('2d').drawImage(videoRef.value, 0, 0);
    faceImage.value = canvas.toDataURL('image/jpeg');

    message.value = "✅ Face captured successfully.";
  } else {
    message.value = "❌ No face detected. Try again.";
  }
};

const captureFingerprint = async () => {
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
      timeout: 60000,
      attestation: "none"
    };

    const credential = await navigator.credentials.create({ publicKey });
    fingerprintCredential.value = credential.id;

    // Snapshot from video for visual-only template
    const canvas = document.createElement('canvas');
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    canvas.getContext('2d').drawImage(videoRef.value, 0, 0);
    fingerprintTemplate.value = canvas.toDataURL('image/jpeg');

    message.value = "✅ Fingerprint captured using WebAuthn.";
  } catch (error) {
    console.error('Fingerprint error:', error);
    message.value = "❌ Fingerprint capture failed.";
  }
};

const submitEnrollment = async () => {
  if (!fullName.value || !email.value) {
    message.value = "❗ Please enter full name and email.";
    return;
  }

  if (biometricChoice.value === 'face') {
    if (!faceDescriptor.value || !faceImage.value) {
      message.value = "❗ Please capture a face image.";
      return;
    }
  } else if (biometricChoice.value === 'fingerprint') {
    if (!fingerprintCredential.value || !fingerprintTemplate.value) {
      message.value = "❗ Please capture a fingerprint.";
      return;
    }
  } else {
    message.value = "❗ Invalid biometric choice.";
    return;
  }

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
      message.value = "🎉 User enrolled successfully! Redirecting to authentication page...";
      // Clear form fields
      fullName.value = '';
      email.value = '';
      faceDescriptor.value = null;
      faceImage.value = null;
      fingerprintCredential.value = null;
      fingerprintTemplate.value = null;

      // Delay navigation to authentication page
      setTimeout(() => {
        router.push('/auth/');
      }, 3000); // 3-second delay
    } else {
      message.value = "❌ Enrollment failed. Try again.";
    }
  } catch (error) {
    console.error('Submit error:', error);
    message.value = "❌ Enrollment failed.";
  }
};

onMounted(async () => {
  await loadFaceModels();
  startVideo();
});
</script>

<style scoped>
h2 {
  font-size: large;
  font-style: bold;
}
.enrollment-form {
  max-width: 400px;
  margin: 0 auto;
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
  overflow: hidden;
  height: 150px;
  width: 150px;
  justify-content: center;
}
video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.capture-btn {
  margin: 10px;
  padding: 10px 20px;
  background-color: #2c7be5;
  color: #fff;
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
