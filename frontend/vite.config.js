import { fileURLToPath, URL } from "node:url";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
  plugins: [vue(), tailwindcss()],
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./src", import.meta.url)),
    },
  },
  server: {
    host: true,
    port: 5173,
    allowedHosts: ["pdmc.doae.go.th"],
    proxy: {
      "/api": {
        target: process.env.VITE_BACKEND_PROXY_URL || "http://localhost:8000",
        changeOrigin: true,
      },
    },
  },
});
