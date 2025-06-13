

<template>
    <div class="enrollment-form">
      <h2 class="title">Enroll New User</h2>

      <form @submit.prevent="submitEnrollment">
        <input type="text" v-model="fullName" placeholder="Full Name" required class="input" />
        <input type="email" v-model="email" placeholder="Email Address" required class="input" />

        <div class="input method-select">
          <label><input type="radio" value="face" v-model="biometricChoice" /> Use Face</label>
          <label><input type="radio" value="fingerprint" v-model="biometricChoice" /> Use Fingerprint</label>
        </div>

        <div class="video-wrapper">
          <video v-show="!capturedImage" ref="videoRef" autoplay playsinline muted></video>
          <img v-show="capturedImage" :src="capturedImage" alt="Captured" class="captured-img" />
        </div>

        <div v-if="loading" class="progress-bar">
          <div class="progress"></div>
        </div>

        <button type="button" @click="handleBiometricAction" :disabled="!biometricChoice || loading" class="capture-btn">
          {{ biometricChoice === 'face' ? (livenessConfirmed ? '📸 Capture Face' : '🧠 Start Liveness Check') : '🖐️ Capture Fingerprint' }}
        </button>

        <p :class="['status-message', messageType]">{{ message }}</p>

        <button type="submit" class="submit-btn" :disabled="loading">✅ Enroll User</button>
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

  const message = ref('');
  const messageType = ref('info'); // success, error, warning, info
  const livenessConfirmed = ref(false);
  const loading = ref(false);
  const capturedImage = ref(null);
  const challenge = ref('');

  const setMessage = (text, type = 'info') => {
    message.value = text;
    messageType.value = type;
  };

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
      .catch(() => setMessage("❌ Cannot access webcam.", "error"));
  };

  const getRandomChallenge = () => {
    return ['turn left', 'turn right', 'blink'][Math.floor(Math.random() * 3)];
  };

  const detectSharpChanges = (prev, current, w, h) => {
    let diffPixels = 0;
    const threshold = 50;
    for (let i = 0; i < prev.data.length; i += 4) {
      const diff = Math.abs(prev.data[i] - current.data[i]) +
                   Math.abs(prev.data[i + 1] - current.data[i + 1]) +
                   Math.abs(prev.data[i + 2] - current.data[i + 2]);
      if (diff > threshold) diffPixels++;
    }
    return diffPixels > (w * h * 0.05);
  };

  const isSpoofed = (landmarks, ctx, prevFrame, currentFrame, w, h) => {
    const glareRegion = landmarks.getLeftEye().concat(landmarks.getRightEye());
    let totalBrightness = 0;

    glareRegion.forEach(p => {
      const pixel = ctx.getImageData(p.x, p.y, 1, 1).data;
      totalBrightness += (pixel[0] + pixel[1] + pixel[2]) / 3;
    });

    const glare = (totalBrightness / glareRegion.length) > 240;
    const sharpChange = detectSharpChanges(prevFrame, currentFrame, w, h);
    return glare || sharpChange;
  };

  const startLivenessCheck = async () => {
    loading.value = true;
    challenge.value = getRandomChallenge();
    setMessage(`👁️ Please ${challenge.value}...`, "info");

    const timeout = 15000;
    const startTime = Date.now();
    let blinked = false;
    let turned = false;
    let prevFrame = null;

    const detect = async () => {
      if (Date.now() - startTime > timeout) {
        setMessage("❌ Liveness check failed. Try again.", "error");
        loading.value = false;
        return;
      }

      const detection = await faceapi
        .detectSingleFace(videoRef.value, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks();

      if (!detection) {
        requestAnimationFrame(detect);
        return;
      }

      const landmarks = detection.landmarks;
      const canvas = document.createElement('canvas');
      canvas.width = videoRef.value.videoWidth;
      canvas.height = videoRef.value.videoHeight;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(videoRef.value, 0, 0);
      const currentFrame = ctx.getImageData(0, 0, canvas.width, canvas.height);

      if (prevFrame && isSpoofed(landmarks, ctx, prevFrame, currentFrame, canvas.width, canvas.height)) {
        setMessage("❌ Spoofing detected (glare or fake feed).", "error");
        loading.value = false;
        return;
      }
      prevFrame = currentFrame;

      const leftEye = landmarks.getLeftEye();
      const rightEye = landmarks.getRightEye();
      const nose = landmarks.getNose();
      const ear = (eye) => Math.abs(eye[1].y - eye[5].y) / Math.abs(eye[0].x - eye[3].x);
      const EAR = (ear(leftEye) + ear(rightEye)) / 2;

      if (EAR < 0.23) blinked = true;
      const noseX = nose[3].x;

      if ((challenge.value === 'blink' && blinked) ||
          (challenge.value === 'turn left' && noseX > canvas.width * 0.6) ||
          (challenge.value === 'turn right' && noseX < canvas.width * 0.4)) {
        livenessConfirmed.value = true;
        setMessage("✅ Liveness confirmed. You can now capture your face.", "success");
        loading.value = false;
        return;
      }

      requestAnimationFrame(detect);
    };

    detect();
  };

  const captureFace = async () => {
    const detection = await faceapi
      .detectSingleFace(videoRef.value, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptor();

    if (!detection) return setMessage("❌ No face detected. Please try again.", "error");

    faceDescriptor.value = Array.from(detection.descriptor);
    const canvas = document.createElement('canvas');
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    canvas.getContext('2d').drawImage(videoRef.value, 0, 0);
    const image = canvas.toDataURL('image/jpeg');

    faceImage.value = image;
    capturedImage.value = image;
    setMessage("✅ Face captured successfully.", "success");
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
        authenticatorSelection: { authenticatorAttachment: "platform", userVerification: "preferred" },
        timeout: 10000,
        attestation: "none",
      };

      const cred = await navigator.credentials.create({ publicKey });
      fingerprintCredential.value = cred.id;

      const canvas = document.createElement('canvas');
      canvas.width = videoRef.value.videoWidth;
      canvas.height = videoRef.value.videoHeight;
      canvas.getContext('2d').drawImage(videoRef.value, 0, 0);
      const image = canvas.toDataURL('image/jpeg');

      fingerprintTemplate.value = image;
      capturedImage.value = image;
      setMessage("✅ Fingerprint captured successfully.", "success");
    } catch (err) {
      console.error(err);
      setMessage("❌ Fingerprint capture failed.", "error");
    } finally {
      loading.value = false;
    }
  };

  const handleBiometricAction = async () => {
    if (biometricChoice.value === 'face') {
      !livenessConfirmed.value ? await startLivenessCheck() : await captureFace();
    } else {
      await captureFingerprint();
    }
  };

  const submitEnrollment = async () => {
    if (!fullName.value || !email.value) return setMessage("❗ Please enter full name and email.", "warning");

    if (biometricChoice.value === 'face' && (!faceDescriptor.value || !faceImage.value))
      return setMessage("❗ Please complete face capture.", "warning");

    if (biometricChoice.value === 'fingerprint' && (!fingerprintCredential.value || !fingerprintTemplate.value))
      return setMessage("❗ Please complete fingerprint capture.", "warning");

    loading.value = true;

    try {
      const res = await fetch('http://localhost:8000/api/enroll-user', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          full_name: fullName.value,
          email: email.value,
          biometric_method: biometricChoice.value,
          face_descriptor: biometricChoice.value === 'face' ? faceDescriptor.value : null,
          face_image: biometricChoice.value === 'face' ? faceImage.value : null,
          fingerprint_template: biometricChoice.value === 'fingerprint' ? fingerprintTemplate.value : null,
          fingerprint_credential: biometricChoice.value === 'fingerprint' ? fingerprintCredential.value : null,
        }),
      });

      if (res.ok) {
        setMessage("🎉 User enrolled successfully! Redirecting...", "success");
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
        setMessage("❌ Enrollment failed. Try again.", "error");
      }
    } catch (err) {
      setMessage("❌ Server error. Please try later.", "error");
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
    font-family: Arial, sans-serif;
  }
  .title {
    font-size: 32px;
    margin-bottom: 20px;
  }
  .input {
    width: 80%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #2c7be5;
    border-radius: 5px;
  }
  .method-select {
    display: flex;
    justify-content: center;
    gap: 20px;
  }
  .video-wrapper {
    margin: 20px auto;
    width: 150px;
    height: 150px;
    border: 2px solid #ccc;
    border-radius: 8px;
  }
  video, .captured-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .progress-bar {
    height: 6px;
    background: #eee;
    border-radius: 4px;
    margin: 10px auto;
    overflow: hidden;
  }
  .progress {
    height: 100%;
    width: 100%;
    background: #2c7be5;
    animation: progressAnim 1.5s linear infinite;
  }
  @keyframes progressAnim {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
  }
  .capture-btn, .submit-btn {
    margin-top: 10px;
    padding: 10px 20px;
    border: none;
    color: white;
    border-radius: 5px;
    cursor: pointer;
  }
  .capture-btn {
    background: #2c7be5;
  }
  .capture-btn:hover {
    background: #1a5cbf;
  }
  .submit-btn {
    background: #28a745;
  }
  .submit-btn:hover {
    background: #218838;
  }
  .status-message {
    margin-top: 10px;
    font-weight: bold;
  }
  .status-message.success { color: green; }
  .status-message.error { color: red; }
  .status-message.warning { color: orange; }
  .status-message.info { color: #555; }
  .footer {
    margin-top: 40px;
    font-size: 13px;
    color: #aaa;
  }
  </style>
