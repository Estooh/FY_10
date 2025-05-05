import { boot } from 'quasar/wrappers'
import axios from 'axios'


// Be careful when using SSR for cross-request state pollution
// due to creating a Singleton instance here;
// If any client changes this (global) instance, it might be a
// good idea to move this instance creation inside of the
// "export default () => {}" function below (which runs individually
// for each client)
let user = sessionStorage.getItem('user');
let token = user!=null?JSON.parse(user).token:'';

axios.defaults.withCredentials=true;
axios.get(`${process.env.API_URL}sanctum/csrf-cookie`)
.then((response)=>{
});

const api = axios.create({
  baseURL: process.env.API_URL,
  headers: {
    Authorization: `Bearer ${token}`,
    Accept: 'application/json',
 },
  withCredentials: true,
})

export default boot(({ app }) => {
  // for use inside Vue files (Options API) through this.$axios and this.$api

  app.config.globalProperties.$axios = axios
  // ^ ^ ^ this will allow you to use this.$axios (for Vue Options API form)
  //       so you won't necessarily have to import axios in each vue file

  app.config.globalProperties.$api = api
  // ^ ^ ^ this will allow you to use this.$api (for Vue Options API form)
  //       so you can easily perform requests against your app's API
})

export { api }
