import axios from "axios";

export const API_ENABLED =
  import.meta.env.VITE_API_ENABLED?.trim().toLowerCase() !== "false";
export const API_DISABLED_MESSAGE =
  "API calls are disabled by VITE_API_ENABLED=false";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api",
  headers: { Accept: "application/json" },
});

// Attach Bearer token to every request if present
api.interceptors.request.use((config) => {
  if (!API_ENABLED) {
    throw new Error(API_DISABLED_MESSAGE);
  }

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
  getBreakdown: () =>
    api.get("/external/disaster/breakdown").then((r) => r.data),
  getAlerts: () => api.get("/alerts").then((r) => r.data),

  // ---- Third-party APIs (proxied via the Laravel backend, cached in DB) ----
  getTemperatureStations: () =>
    api.get("/external/temperature-stations").then((r) => r.data?.data || []),
  getAwsRainfall: () => api.get("/external/rainfall").then((r) => r.data),
  getRain24h: () => api.get("/external/rain/24h").then((r) => r.data),
  getRainToday: () => api.get("/external/rain/today").then((r) => r.data),
  getRainYesterday: () => api.get("/external/rain/yesterday").then((r) => r.data),
  getRain3d: () => api.get("/external/rain/3d").then((r) => r.data),
  getRain7d: () => api.get("/external/rain/7d").then((r) => r.data),
  getDamWater: () =>
    api.get("/external/dam-water").then((r) => r.data?.data?.dam_daily || []),
  getWeatherForecast: (latitude, longitude) =>
    api
      .get(`/external/weather/forecast?lat=${latitude}&lng=${longitude}`)
      .then((r) => r.data),
  getReverseGeocode: (latitude, longitude) =>
    api
      .get(`/external/weather/reverse-geocode?lat=${latitude}&lng=${longitude}`)
      .then((r) => r.data),

  // ---- efarmer.doae.go.th — พื้นที่ยังไม่เก็บเกี่ยว ----
  // level: 'province' | 'amphur'
  // areaCode: รหัสจังหวัด 2 หลัก หรือ รหัสอำเภอ 4 หลัก (optional)
  // dateDisaster: 'YYYY-MM-DD'
  getNoneProduce: (params = {}) =>
    api.post("/external/none-produce", params).then((r) => r.data),

  // ---- riskmap.doae.go.th — ปริมาณน้ำฝนเฉลี่ย 24 ชม. ----
  // level: 'p' (province) | 'a' (amphur)
  // admin_code: optional
  getRainAverage: (level = "p", adminCode = null) => {
    const params = new URLSearchParams({ level });
    if (adminCode) params.set("admin_code", adminCode);
    return api
      .get(`/external/rain-average?${params.toString()}`)
      .then((r) => r.data);
  },
};
