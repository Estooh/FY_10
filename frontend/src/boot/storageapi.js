import { boot } from 'quasar/wrappers'

const storageApi = process.env.STORAGE_URL

export default boot(({ app }) => {
  app.config.globalProperties.$storageApi = storageApi
})
export { storageApi}