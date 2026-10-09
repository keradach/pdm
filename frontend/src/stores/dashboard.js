import { defineStore } from "pinia";
import api, { API_ENABLED, API_DISABLED_MESSAGE } from "@/services/api";

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
    rainfallData: null,
    damWaterData: [],
    temperatureData: [],
    mapView: "rain_avg", // 'risk' or 'rain' or 'dam' or 'rain_avg'
    rainfallPeriod: "today", // 'today', 'yesterday', 'last_3_days', 'last_7_days'

    // --- ใหม่: พื้นที่ยังไม่เก็บเกี่ยว (efarmer.doae.go.th) ---
    noneProduceData: [],
    noneProduceDate: new Date().toISOString().slice(0, 10), // YYYY-MM-DD

    // --- ใหม่: ปริมาณน้ำฝนเฉลี่ย 24 ชม. (riskmap.doae.go.th) ---
    rainAverageData: [],
  }),

  actions: {
    async fetchAll() {
      if (!API_ENABLED) {
        this.error = API_DISABLED_MESSAGE;
        return;
      }

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
          damWaterData,
          temperatureData,
          noneProduceResult,
          rainAverageResult,
        ] = await Promise.all([
          api.getSummary(),
          api.getGauges(),
          api.getProvinces(),
          api.getTopDamaged(10),
          api.getTrend(),
          api.getBreakdown(),
          api.getAlerts(),
          // Rainfall API calls are temporarily disabled; keep these for later:
          // api.getRainToday(),
          // api.getRainYesterday(),
          // api.getRain3d(),
          // api.getRain7d(),
          api.getDamWater(),
          api.getTemperatureStations().catch(() => []),
          api
            .getNoneProduce({
              level: "province",
              dateDisaster: this.noneProduceDate,
            })
            .catch(() => ({ data: [] })),
          api.getRainAverage("p").catch(() => ({ data: [] })),
        ]);
        this.summary = summary;
        this.gauges = gauges;
        this.provinces = provinces;
        this.topDamaged = topDamaged;
        this.trend = trend;
        this.breakdown = breakdown;
        this.alerts = alerts;
        // Temporarily disabled along with rainfall API requests:
        // this.rainfallData = {
        //   data: {
        //     today: rainToday.data,
        //     yesterday: rainYesterday.data,
        //     "3d": rain3d.data,
        //     "7d": rain7d.data,
        //   },
        // };
        this.damWaterData = damWaterData;
        this.temperatureData = Array.isArray(temperatureData)
          ? temperatureData
          : [];
        this.noneProduceData = noneProduceResult?.data || [];
        this.rainAverageData = rainAverageResult?.data || [];
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

    async setNoneProduceDate(date) {
      this.noneProduceDate = date;
      try {
        const result = await api.getNoneProduce({
          level: "province",
          dateDisaster: date,
        });
        this.noneProduceData = result?.data || [];
      } catch (e) {
        console.error("fetchNoneProduce error:", e);
      }
    },

    async refreshRainAverage(level = "p") {
      try {
        const result = await api.getRainAverage(level);
        this.rainAverageData = result?.data || [];
      } catch (e) {
        console.error("fetchRainAverage error:", e);
      }
    },
  },
});
