import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";
import DefaultLayout from "../layouts/DefaultLayout.vue";

const routes = [
    {
        path: "/",
        component: DefaultLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: "",
                redirect: "/dashboard",
            },
            {
                path: "/dashboard",
                name: "Dashboard",
                component: () => import("../views/DashboardView.vue"),
            },
            {
                path: "/bots",
                name: "Bots",
                component: () => import("../views/BotsView.vue"),
            },
            {
                path: "/team",
                name: "Team",
                component: () => import("../views/TeamView.vue"),
            },
            {
                path: "/account",
                name: "Account",
                component: () => import("../views/AccountView.vue"),
            },
            {
                path: "/integrations",
                name: "API/Integrations",
                component: () => import("../views/IntegrationsView.vue"),
            },
        ],
    },
    {
        path: "/login",
        name: "login",
        component: () => import("../views/auth/LoginView.vue"), // Updated path
        meta: { guest: true },
    },
    {
        path: "/register",
        name: "register",
        component: () => import("../views/auth/RegisterView.vue"), // Updated path
        meta: { guest: true },
    },
];

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore();

    // Initialize auth on first navigation
    auth.initializeAuth();

    console.log("Route guard - Auth status:", auth.isAuthenticated);

    if (to.meta.requiresAuth) {
        if (!auth.isAuthenticated) {
            console.log("Not authenticated, redirecting to login");
            return next("/login");
        }
        // Only check with server if we think we're authenticated
        const verified = await auth.checkAuth();
        if (!verified) {
            console.log("Server check failed, redirecting to login");
            return next("/login");
        }
    } else if (to.meta.guest && auth.isAuthenticated) {
        console.log(
            "Authenticated user trying to access guest route, redirecting to home"
        );
        return next("/");
    }

    next();
});

export default router;
