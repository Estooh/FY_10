<template>
  <div class="biometric-auth">
    <h2 class="title">Biometric Authentication</h2>

    <div class="video-container">
      <video ref="videoRef" autoplay playsinline muted></video>
    </div>

    <div class="buttons">
      <button @click="detectFace" class="auth-btn">
        <Eye size="20" /> Authenticate Face
      </button>
      <button @click="authenticateFingerprint" class="auth-btn">
        <Fingerprint size="20" /> Authenticate Fingerprint
      </button>
    </div>

    <p class="help-text">
      Unable to authenticate?
      <router-link to="/enroll-user" class="admin-link">Contact the administrator.</router-link>
    </p>
    <p class="message" :class="{ success: faceDetected, error: !faceDetected && message }">{{ message }}</p>

    <footer class="footer">&copy; 2025 Final Year Project:10. All rights reserved.</footer>
  </div>
</template>

<script>
import { defineComponent, ref, onMounted } from 'vue';
import * as faceapi from 'face-api.js';
import { Fingerprint, Eye } from 'lucide-vue-next';

export default defineComponent({
  components: {
    Fingerprint,
    Eye,
  },
  setup() {
    const videoRef = ref(null);
    const message = ref('');
    const faceDetected = ref(false);
    const modelsLoaded = ref(false);

    // Load face-api.js models
    const loadFaceModels = async () => {
      const MODEL_URL = '/models';
      try {
        await Promise.all([
          faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
          faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
          faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);
        modelsLoaded.value = true;
        console.log("Face models loaded successfully.");
      } catch (error) {
        console.error("Error loading face-api models:", error);
        message.value = "❌ Failed to load face recognition models.";
      }
    };

    // Start webcam stream
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

    // Detect face and authenticate
    const detectFace = async () => {
      if (!modelsLoaded.value) {
        message.value = "⏳ Loading models, please wait...";
        return;
      }

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
      message.value = "⏳ Authenticating...";

      try {
        const response = await fetch('http://127.0.0.1:8000/api/auth/face', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ descriptor }),
        });

        const result = await response.json();

        if (response.ok && result.success) {
          faceDetected.value = true;
          message.value = `✅ ${result.message || 'Authentication successful!'}`;
        } else {
          faceDetected.value = false;
          message.value = `${result.message || 'Face not recognized!'}`;
        }
      } catch (error) {
        faceDetected.value = false;
        console.error("Authentication error:", error);
        message.value = "❌ Server error during authentication.";
      }
    };

    // Simulated fingerprint authentication
    const authenticateFingerprint = async () => {
      try {
        const publicKey = {
          challenge: new Uint8Array(32),
          timeout: 60000,
          allowCredentials: [{ type: "public-key", id: new Uint8Array(16), transports: ["internal"] }],
          userVerification: "preferred",
        };

        await navigator.credentials.get({ publicKey });
        message.value = "✅ Fingerprint authenticated!";

        await fetch('http://127.0.0.1:8000/api/auth/fingerprint', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            userAgent: navigator.userAgent,
            timestamp: new Date().toISOString(),
          }),
        });
      } catch (error) {
        console.error("Fingerprint auth failed:", error);
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
  width: 180px;
  height: 140px;
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
