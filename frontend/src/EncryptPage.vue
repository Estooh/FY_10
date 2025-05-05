<!-- This page works to encrypt all data being transmited -->
<template>

</template>

<script>
import crypto-js from 'crypto-js'

const secretKey = 'OtEaTnTASXiB5ArcS9Vl9RvIMc20iytTc2faII=';
const encryptedPayload = {
full_name: fullName.value,
email: email.value,
face_descriptor: CryptoJS.AES.encrypt(JSON.stringify(faceDescriptor.value),
secretKey).toString(),
face_image: CryptoJS.AES.encrypt(faceImageBase64, secretKey).toString(),
fingerprint_credential: CryptoJS.AES.encrypt(fingerprintCredential.value, secretKey).toString(),
};
</script>
