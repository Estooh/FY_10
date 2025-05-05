<template>
  <q-layout view="hHh lpR fFf" class="bg-white">
    <q-page-container
      class="q-pa-md bg row items-center justify-center"
      style="width: 100%; height: 100vh"
    >
      <!-- <div class="column items-center justify-center">
        <p>
          <q-img src="~assets/conture-logo.png" style="width: 80px; height: auto" />
        </p>
        <p class="text-primary text-h5 text-center" style="letter-spacing: 5px">
          CONTURE
          <br>
          Jinoe
        </p>
      </div> -->
    </q-page-container>
  </q-layout>
</template>
<script setup>
import { useRouter } from "vue-router";
import { onMounted } from "vue";

const router = useRouter();

onMounted(() => {
  setTimeout(() => {
    router.push("/auth/");
  }, 2000);
});
</script>
<style>
.bg {
  background-image: url("/bg.png");
  background-size: cover;
  background-repeat: no-repeat;
}
.q-layout-footer {
  background-color: #1d1d1d;
}
</style>
