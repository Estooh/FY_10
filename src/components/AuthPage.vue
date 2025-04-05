<template>
  <div class="biometric-auth">
    <h2 class="title">Biometric Authentication Here</h2>

    <div class="video-container">
      <video ref="videoRef" autoplay playsinline></video>
    </div>

    <div class="buttons">
      <button @click="detectFace" class="auth-btn">
        <Eye size="20" /> Authenticate Face
      </button>
      <button @click="authenticateFingerprint" class="auth-btn">
        <Fingerprint size="20" /> Authenticate Fingerprint
      </button>
    </div>

    <p class="help-text">Unable to authenticate? Contact administrator!</p>

    <p class="message" :class="{ success: faceDetected, error: !faceDetected }">
      {{ message }}
    </p>

    <!-- Footer -->
    <footer class="footer">
      &copy; 2025 Final Year Project:10. All rights reserved.
    </footer>
  </div>
</template>


<script>
import { defineComponent, onMounted, ref } from "vue";
import * as faceapi from "face-api.js";
import { Fingerprint, Eye } from "lucide-vue-next";

export default defineComponent({
  components: {
    Fingerprint,
    Eye,
  },
  setup() {
    const videoRef = ref(null);
    const message = ref("");
    const faceDetected = ref(false);

    // Load face-api.js models
    const loadFaceModels = async () => {
      const MODEL_URL = "/models";
      await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL),
      ]);
    };

    // Start webcam feed
    const startVideo = () => {
      navigator.mediaDevices
        .getUserMedia({ video: true })
        .then((stream) => {
          if (videoRef.value) videoRef.value.srcObject = stream;
        })
        .catch((err) => {
          console.error("Error accessing webcam:", err);
        });
    };

    // Detect face in the video stream
    const detectFace = async () => {
      const options = new faceapi.TinyFaceDetectorOptions();
      const detections = await faceapi.detectAllFaces(videoRef.value, options);

      if (detections.length > 0) {
        message.value = "✅ Face recognized!";
        faceDetected.value = true;
      } else {
        message.value = "❌ No face detected!";
        faceDetected.value = false;
      }
    };

    // Fingerprint authentication via WebAuthn
    const authenticateFingerprint = async () => {
      try {
        const publicKey = {
          challenge: new Uint8Array(32),
          allowCredentials: [{ type: "public-key", id: new Uint8Array(16) }],
          timeout: 60000,
        };
        await navigator.credentials.get({ publicKey });
        message.value = "✅ Fingerprint authenticated!";
      } catch (error) {
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
  justify-content: center;
  padding: 20px;
  text-align: center;
}

.title {
  font-size: 1.5rem;
  margin-bottom: 10px;
}

.video-container {
  border: 2px solid #ccc;
  border-radius: 10px;
  overflow: hidden;
  width: 300px;
  height: 200px;
  margin-bottom: 10px;
}

video {
  width: 100%;
  height: 100%;
}

.buttons {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.auth-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  background-color: #007bff;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s;
}

.auth-btn:hover {
  background-color: #0056b3;
}

.message {
  margin-top: 15px;
  font-weight: bold;
}

.success {
  color: green;
}

.error {
  color: red;
}
.help-text {
  margin-top: 10px;
  font-size: 0.9rem;
  color: #446ad4;
}
.footer {
  margin-top: 30px;
  font-size: 0.85rem;
  color: #888;
}

</style>
