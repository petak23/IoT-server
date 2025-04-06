/* 
 * Main Vue.js app file
 * Posledná zmena(last change): 02.11.2023
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2023 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.2
 */

import { createPinia } from 'pinia'
import { createApp } from 'vue'


import App from './App.vue'
import router from './router'

const pinia = createPinia()
const app = createApp(App)
app.use(pinia)
app.use(router)


app.mount('#app')
