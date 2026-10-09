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
    rainAveragePeriod: "avg_rain_24h",

    // --- ใหม่: พื้นที่ยังไม่เก็บเกี่ยว (efarmer.doae.go.th) ---
    noneProduceData: [],
    noneProduceSumAll: null,
    noneProduceDate: "2026-09-25",
    noneProduceLevel: "province",
    noneProduceAreaCode: null,
    noneProduceParentAreaCode: null,
    noneProduceSelectionPath: [],
    noneProduceSelectedAreaCode: null,
    noneProduceLoading: false,
    noneProduceError: null,
    noneProduceRequestId: 0,

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
        this.rainAverageData = rainAverageResult?.data || [];
        this.selectedProvince =
          provinces.find((p) => p.risk_level === "critical") ||
          provinces[0] ||
          null;
        this.fetchNoneProduce();
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

    setRainAveragePeriod(period) {
      this.rainAveragePeriod = period;
    },

    async fetchNoneProduce() {
      const requestId = ++this.noneProduceRequestId;
      this.noneProduceLoading = true;
      this.noneProduceError = null;
      try {
        const result = await api.getNoneProduce({
          level: this.noneProduceLevel,
          areaCode: this.noneProduceAreaCode,
          dateDisaster: this.noneProduceDate,
        });
        if (requestId !== this.noneProduceRequestId) return;
        this.noneProduceData = Array.isArray(result?.data)
          ? result.data
          : Array.isArray(result?.data?.data)
            ? result.data.data
          : Array.isArray(result)
            ? result
            : [];
        this.noneProduceSumAll =
          result?.sumAll ?? result?.data?.sumAll ?? result?.totals ?? null;
      } catch (e) {
        if (requestId !== this.noneProduceRequestId) return;
        this.noneProduceError =
          "ไม่สามารถโหลดข้อมูลพื้นที่ยังไม่เก็บเกี่ยวได้ กรุณาลองใหม่อีกครั้ง";
        console.error("fetchNoneProduce error:", e);
      } finally {
        if (requestId === this.noneProduceRequestId) {
          this.noneProduceLoading = false;
        }
      }
    },

    async selectNoneProduceArea({ code, name, level }) {
      if (level !== this.noneProduceLevel || !code) return;

      if (level === "province") {
        this.noneProduceSelectionPath = [{ code, name, level }];
      } else if (level === "district") {
        this.noneProduceSelectionPath = [
          ...this.noneProduceSelectionPath.slice(0, 1),
          { code, name, level },
        ];
      } else if (level === "subdistrict") {
        this.noneProduceSelectionPath = [
          ...this.noneProduceSelectionPath.slice(0, 2),
          { code, name, level },
        ];
        this.noneProduceSelectedAreaCode = code;
        return;
      } else {
        return;
      }

      this.syncNoneProduceSelection();
      await this.fetchNoneProduce();
    },

    async goBackNoneProduceArea() {
      if (this.noneProduceSelectionPath.length === 0) return;
      this.noneProduceSelectionPath = this.noneProduceSelectionPath.slice(0, -1);
      this.syncNoneProduceSelection();
      await this.fetchNoneProduce();
    },

    async clearNoneProduceArea() {
      this.noneProduceSelectionPath = [];
      this.syncNoneProduceSelection();
      await this.fetchNoneProduce();
    },

    syncNoneProduceSelection() {
      const path = this.noneProduceSelectionPath;
      const province = path[0];
      const district = path[1];
      const leaf = path[2];
      this.noneProduceSelectedAreaCode = leaf?.code ?? null;

      if (!province) {
        this.noneProduceLevel = "province";
        this.noneProduceAreaCode = null;
        this.noneProduceParentAreaCode = null;
      } else if (!district) {
        this.noneProduceLevel = "district";
        this.noneProduceAreaCode = province.code;
        this.noneProduceParentAreaCode = province.code;
      } else {
        this.noneProduceLevel = "subdistrict";
        this.noneProduceAreaCode = district.code;
        this.noneProduceParentAreaCode = district.code;
      }
    },

    async setNoneProduceDate(date) {
      this.noneProduceDate = date;
      await this.fetchNoneProduce();
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
