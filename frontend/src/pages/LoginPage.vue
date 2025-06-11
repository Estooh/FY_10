<template>
  <div class="biometric-auth">
    <h2 class="title">Biometric Authentication</h2>

    <div class="video-container">
      <video ref="videoRef" autoplay playsinline muted></video>
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
      <router-link to="/enroll-user" class="admin-link">Enroll Now!</router-link>
    </p>
    <p class="message" :class="{ success: faceDetected, error: !faceDetected && message }">{{ message }}</p>

    <footer class="footer">&copy; 2025 Final Year Project:10. All rights reserved.</footer>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import * as faceapi from 'face-api.js';
import { Fingerprint, Eye } from 'lucide-vue-next';
import HmacSHA256 from 'crypto-js/hmac-sha256';
import encHex from 'crypto-js/enc-hex';

export default defineComponent({
  components: { Fingerprint, Eye },
  setup() {
    const videoRef = ref(null);
    const message = ref('');
    const faceDetected = ref(false);
    const modelsLoaded = ref(false);
    const router = useRouter();
    let blinkCounter = 0;

    const loadFaceModels = async () => {
      const MODEL_URL = '/models';
      try {
        await Promise.all([
          faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
          faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
          faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);
        modelsLoaded.value = true;
      } catch (error) {
        console.error("Model loading error:", error);
        message.value = "❌ Failed to load face recognition models.";
      }
    };

    const startVideo = () => {
      navigator.mediaDevices
        .getUserMedia({ video: true })
        .then((stream) => {
          if (videoRef.value) {
            videoRef.value.srcObject = stream;
          }
        })
        .catch((err) => {
          console.error("Webcam error:", err);
          message.value = "❌ Unable to access webcam.";
        });
    };

    const generateNonce = () => crypto.getRandomValues(new Uint8Array(16)).join('');

    const signPayload = (payload) => {
      const secretKey = import.meta.env.VITE_SIGNATURE_SECRET;
      return HmacSHA256(JSON.stringify(payload), secretKey).toString(encHex);
    };

    const getEAR = (landmarks) => {
      const left = landmarks.getLeftEye();
      const right = landmarks.getRightEye();

      const calc = (eye) => {
        const A = faceapi.euclideanDistance(eye[1], eye[5]);
        const B = faceapi.euclideanDistance(eye[2], eye[4]);
        const C = faceapi.euclideanDistance(eye[0], eye[3]);
        return (A + B) / (2.0 * C);
      };

      return (calc(left) + calc(right)) / 2.0;
    };

    const checkLiveness = async () => {
      let detected = false;
      const options = new faceapi.TinyFaceDetectorOptions();

      for (let i = 0; i < 30; i++) {
        const result = await faceapi
          .detectSingleFace(videoRef.value, options)
          .withFaceLandmarks();

        if (result) {
          const ear = getEAR(result.landmarks);
          if (ear < 0.2) {
            blinkCounter++;
          }

          if (blinkCounter >= 2) {
            detected = true;
            break;
          }
        }
        await new Promise((r) => setTimeout(r, 200));
      }

      return detected;
    };

    const handleLivenessThenDetect = async () => {
      message.value = "🔎 Please blink to verify liveness...";
      blinkCounter = 0;

      const isLive = await checkLiveness();
      if (!isLive) {
        message.value = "❌ Liveness check failed. Please try again.";
        return;
      }
      message.value = "✅ Liveness confirmed. Authenticating...";
      setTimeout(() => detectFace(), 1000);
    };

    const detectFace = async () => {
      const options = new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 });

      const detections = await faceapi
        .detectAllFaces(videoRef.value, options)
        .withFaceLandmarks()
        .withFaceDescriptors();

      if (detections.length === 0) {
        faceDetected.value = false;
        message.value = "❌ No face detected!";
        return;
      }

      const descriptor = Array.from(detections[0].descriptor);
      const timestamp = new Date().toISOString();
      const nonce = generateNonce();
      const payload = { descriptor, timestamp, nonce };
      const signature = signPayload(payload);

      try {
        const response = await fetch('http://127.0.0.1:8000/api/auth/face', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Signature': signature,
          },
          body: JSON.stringify(payload),
        });

        const result = await response.json();

        if (response.ok && result.success) {
          faceDetected.value = true;
          message.value = `✅ ${result.message || 'Authentication successful!'}`;
          router.push('/dashboard');
        } else {
          faceDetected.value = false;
          message.value = `${result.message || 'Face not recognized!'}`;
        }
      } catch (error) {
        faceDetected.value = false;
        console.error("Auth error:", error);
        message.value = "❌ Server error during authentication.";
      }
    };

    const authenticateFingerprint = async () => {
      try {
        const challenge = new Uint8Array(32);
        const publicKey = {
          challenge,
          timeout: 60000,
          allowCredentials: [{ type: 'public-key', id: new Uint8Array(16), transports: ['internal'] }],
          userVerification: 'preferred',
        };

        await navigator.credentials.get({ publicKey });

        const timestamp = new Date().toISOString();
        const nonce = generateNonce();
        const payload = { userAgent: navigator.userAgent, timestamp, nonce };
        const signature = signPayload(payload);

        message.value = "✅ Fingerprint authenticated!";
        router.push('/dashboard');

        await fetch('http://127.0.0.1:8000/api/auth/fingerprint', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-Signature': signature },
          body: JSON.stringify(payload),
        });
      } catch (error) {
        console.error("Fingerprint error:", error);
        message.value = "❌ Fingerprint authentication failed!";
      }
    };

    onMounted(async () => {
      await loadFaceModels();
      startVideo();
    });

    return {
      videoRef,
      message,
      detectFace,
      handleLivenessThenDetect,
      authenticateFingerprint,
      faceDetected,
    };
  },
});
</script>

<style scoped>
.biometric-auth {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 30px;
  font-family: Arial, sans-serif;
  text-align: center;
}
.title {
  font-size: 1.8rem;
  margin-bottom: 15px;
  color: #333;
}
.video-container {
  border: 2px solid #ccc;
  border-radius: 10px;
  overflow: hidden;
  width: 150px;
  height: 150px;
  margin-bottom: 20px;
}
video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.buttons {
  display: flex;
  gap: 12px;
  margin-bottom: 10px;
}
.auth-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background-color: #007bff;
  color: #fff;
  border: none;
  padding: 10px 15px;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}
.auth-btn:hover {
  background-color: #0056b3;
}
.help-text {
  font-size: 0.95rem;
  color: #446ad4;
  margin-top: 10px;
}
.message {
  margin-top: 12px;
  font-weight: bold;
  font-size: 1rem;
}
.success {
  color: green;
}
.error {
  color: red;
}
.footer {
  margin-top: 30px;
  font-size: 0.85rem;
  color: #888;
}
.admin-link {
  color: #e63946;
  text-decoration: none;
  cursor: pointer;
  margin-left: 5px;
}
.admin-link:hover {
  color: #a61d2d;
}
</style>
