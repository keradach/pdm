import { createRouter, createWebHistory } from "vue-router";
import Dashboard from "@/views/Dashboard.vue";
import EarlyWarning from "@/views/EarlyWarning.vue";
import Monitoring from "@/views/Monitoring.vue";
import KnowledgeMedia from "@/views/KnowledgeMedia.vue";
import OperationSupport from "@/views/OperationSupport.vue";
import Login from "@/views/Login.vue";
import Register from "@/views/Register.vue";
import { useAuthStore } from "@/stores/auth";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/",
      name: "dashboard",
      component: Dashboard,
    },
    {
      path: "/warning",
      name: "warning",
      component: EarlyWarning,
    },
    {
      path: "/monitoring",
      name: "monitoring",
      component: Monitoring,
    },
    {
      path: "/knowledge",
      name: "knowledge",
      component: KnowledgeMedia,
    },
    {
      path: "/support",
      name: "support",
      component: OperationSupport,
    },
    {
      path: "/login",
      name: "login",
      component: Login,
      meta: { guestOnly: true },
    },
    {
      path: "/register",
      name: "register",
      component: Register,
      meta: { guestOnly: true },
    },
  ],
});

// Global navigation guard
router.beforeEach((to) => {
  const auth = useAuthStore();

  // If logged in and trying to access login/register, redirect to dashboard
  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: "dashboard" };
  }
});

export default router;
