import './styles/tokens.css'

import { createPinia } from 'pinia'
import { createApp } from 'vue'

import App from './App.vue'
import vuetify from './plugins/vuetify'
import router from './router'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(vuetify)

// Vue Router's initial navigation is async — mounting before it resolves
// leaves route.params empty, which throws in any `:to` binding needing a
// required param (e.g. AppHeader's cart link) and crashes the whole render.
void router.isReady().then(() => app.mount('#app'))
