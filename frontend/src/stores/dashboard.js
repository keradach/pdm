import { defineStore } from "pinia";
import api from "@/services/api";

export const useDashboardStore = defineStore("dashboard", {
  state: () => ({
    loading: false,
    error: null,
    summary: null,
    gauges: [],
    provinces: [],
    topDamaged: [],
    trend: [],
    breakdown: [],
    alerts: [],
    selectedProvince: null,
    weatherData: null,
    rainfallData: null,
    mapView: "risk", // 'risk' or 'weather'
    rainfallPeriod: "today", // 'today', 'yesterday', 'last_3_days', 'last_7_days'
  }),

  actions: {
    async fetchAll() {
      this.loading = true;
      this.error = null;
      try {
        const [
          summary,
          gauges,
          provinces,
          topDamaged,
          trend,
          breakdown,
          alerts,
          weatherData,
          rainfallData,
        ] = await Promise.all([
          api.getSummary(),
          api.getGauges(),
          api.getProvinces(),
          api.getTopDamaged(10),
          api.getTrend(),
          api.getBreakdown(),
          api.getAlerts(),
          api.getAwsNow(),
          api.getAwsRainfall(),
        ]);
        this.summary = summary;
        this.gauges = gauges;
        this.provinces = provinces;
        this.topDamaged = topDamaged;
        this.trend = trend;
        this.breakdown = breakdown;
        this.alerts = alerts;
        this.weatherData = weatherData;
        this.rainfallData = rainfallData;
        this.selectedProvince =
          provinces.find((p) => p.risk_level === "critical") ||
          provinces[0] ||
          null;
      } catch (e) {
        // API not reachable yet (e.g. backend still migrating) - keep the
        // page usable with an inline error instead of a blank screen.
        this.error =
          "ไม่สามารถโหลดข้อมูลจากเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง";
        console.error(e);
      } finally {
        this.loading = false;
      }
    },

    selectProvince(province) {
      this.selectedProvince = province;
    },

    setMapView(view) {
      this.mapView = view;
    },

    setRainfallPeriod(period) {
      this.rainfallPeriod = period;
    },
  },
});
