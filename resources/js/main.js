import App from '@/App.vue';
import { registerPlugins } from '@core/utils/plugins';
import { createApp } from 'vue';
import { Skeletor } from 'vue-skeletor';

// Styles
import '@core-scss/template/index.scss';
import '@styles/styles.scss';

// Create vue app
const app = createApp(App)

app.component(Skeletor.name, Skeletor);

// Register plugins
registerPlugins(app)

// Mount vue app
app.mount('#app')
