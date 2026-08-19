import axios from "axios";

const tmdApi = axios.create({ headers: { Accept: "application/json" } });

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api",
  headers: { Accept: "application/json" },
});

// Attach Bearer token to every request if present
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("pdm_token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default {
  // ---- Auth ----
  register: (payload) =>
    api.post("/auth/register", payload).then((r) => r.data),
  login: (payload) => api.post("/auth/login", payload).then((r) => r.data),
  getMe: () => api.get("/auth/me").then((r) => r.data),
  logout: () => api.post("/auth/logout").then((r) => r.data),

  // ---- Dashboard ----
  getSummary: () => api.get("/dashboard/summary").then((r) => r.data),
  getGauges: () => api.get("/dashboard/gauges").then((r) => r.data),
  getProvinces: () => api.get("/provinces").then((r) => r.data),
  getProvince: (id) => api.get(`/provinces/${id}`).then((r) => r.data),
  getTopDamaged: (limit = 10) =>
    api.get(`/provinces/top-damaged?limit=${limit}`).then((r) => r.data),
  getTrend: () => api.get("/reports/trend").then((r) => r.data),
  getBreakdown: () => api.get("/reports/breakdown").then((r) => r.data),
  getAlerts: () => api.get("/alerts").then((r) => r.data),

  // ---- TMD API ----
  getAwsNow: () =>
    tmdApi.get("https://wxmap.tmd.go.th/api/awsnow").then((r) => r.data),
  getAwsRainfall: () =>
    tmdApi.get("https://wxmap.tmd.go.th/api/awsrainfall").then((r) => r.data),
};
