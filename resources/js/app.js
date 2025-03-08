import "./bootstrap";
import "../css/app.css";

import { createApp } from "vue";
import { createPinia } from "pinia";
import Toast, { POSITION } from "vue-toastification";
import "vue-toastification/dist/index.css";
import App from "./App.vue";
import router from "./router";
import { useAuthStore } from "./stores/auth";
import VueDOMPurifyHTML from "vue-dompurify-html";

const app = createApp(App);
const pinia = createPinia();
app.use(pinia);
app.use(VueDOMPurifyHTML);

const authStore = useAuthStore();
authStore.initializeAuth();

const toastOptions = {
    position: POSITION.BOTTOM_RIGHT,
    timeout: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: true,
    rtl: false,
};

app.use(Toast, toastOptions);
app.use(router);
app.mount("#app");
