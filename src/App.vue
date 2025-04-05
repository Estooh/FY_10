<template>
  <div id="app">
    <header>
      <p>Secure Transimission of Biometric Data from Sensor to Authentication Engine</p>
    </header>
    <!-- <img alt="Vue logo" src="./assets/logo.png"> -->
    <AuthPage msg="Welcome to Your Vue.js App"/>
  </div>
</template>

<script>
import AuthPage from './components/AuthPage.vue'

export default {
  name: 'App',
  components: {
    AuthPage
  }
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}

header {
  background-color: #000;
  color: #fff;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: bold;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

</style>
