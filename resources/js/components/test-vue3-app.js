import { createApp } from 'vue';
import TestVue3 from './test-vue3.vue';

// Create a simple Vue 3 app just for testing
const app = createApp({
    setup() {
        const productionUrl = '/metag';
        
        return {
            productionUrl
        };
    }
});

// Register the test component
app.component('test-vue3', TestVue3);

// Mount the app when the DOM is ready
window.addEventListener('DOMContentLoaded', () => {
    try {
        window.testApp = app.mount("#app");
        console.log('Vue 3 test app mounted successfully');
    } catch (error) {
        console.error('Failed to mount Vue 3 app:', error);
        document.getElementById('error-output').innerHTML = `<p>Error mounting app: ${error.message}</p>`;
        document.getElementById('error-output').style.display = 'block';
    }
});