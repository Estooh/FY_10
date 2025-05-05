<template>
  <q-layout view="hHh lpR fFf" class="bg-white">
    <q-page-container
      class="q-pa-md bg row items-center justify-center"
      style="width: 100%; height: 100vh"
    >
      <router-view>
        
      </router-view>
    </q-page-container>
  </q-layout>
</template>
<script setup>
import { useRouter } from "vue-router";
import { onMounted } from "vue";

const router = useRouter();
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
