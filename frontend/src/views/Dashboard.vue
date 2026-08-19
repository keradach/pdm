<script setup>
import { onMounted } from 'vue'
import { useDashboardStore } from '@/stores/dashboard'
import { storeToRefs } from 'pinia'

import HeaderBar from '@/components/HeaderBar.vue'
import AlertBanner from '@/components/AlertBanner.vue'
import StatCards from '@/components/StatCards.vue'
import SidebarNav from '@/components/SidebarNav.vue'
import RiskMapCard from '@/components/RiskMapCard.vue'
import DisasterGauges from '@/components/DisasterGauges.vue'
import TrendLineChart from '@/components/TrendLineChart.vue'
import DisasterPieChart from '@/components/DisasterPieChart.vue'
import TopProvincesBarChart from '@/components/TopProvincesBarChart.vue'
import InfoPanel from '@/components/InfoPanel.vue'
import PartnerLogos from '@/components/PartnerLogos.vue'

const store = useDashboardStore()
const {
  summary,
  gauges,
  provinces,
  topDamaged,
  trend,
  breakdown,
  alerts,
  selectedProvince,
  loading,
  error,
  weatherData,
  rainfallData,
  mapView,
  rainfallPeriod,
} = storeToRefs(store)

onMounted(() => store.fetchAll())

const earlyWarningItems = [
  { icon: '🗺', label: 'Risk Map Dashboard' },
  { icon: '📈', label: 'คาดการณ์ล่วงหน้า 15 วัน' },
  { icon: '💧', label: 'Water Balance' },
  { icon: '🛰', label: 'ข้อมูลดาวเทียม/เรดาร์' },
  { icon: '☀', label: 'Drought Risk Index (DRI)' },
  { icon: '📓', label: 'สมุดน้ำรายตำบล' },
]
const monitoringItems = [
  { icon: '📊', label: 'ติดตามการช่วยเหลือ' },
  { icon: '📈', label: 'ความคืบหน้าการช่วยเหลือ' },
  { icon: '📋', label: 'ทะเบียนเกษตรกร (ทบก.)' },
  { icon: '🧑\u200d🌾', label: 'ข้อมูลเกษตรกรและขึ้นทะเบียน' },
  { icon: '📝', label: 'รายงานเหตุเบื้องต้น' },
  { icon: '🚑', label: 'การลงพื้นที่และช่วยเหลือฉุกเฉิน' },
]
const knowledgeItems = [
  { icon: '📰', label: 'คลังสื่อประชาสัมพันธ์' },
  { icon: '🎞', label: 'Infographic / VDO / สื่อความรู้' },
  { icon: '🌱', label: 'พืชทางเลือก' },
  { icon: '💧', label: 'ทางเลือกการปลูกพืชใช้น้ำน้อย' },
  { icon: '📖', label: 'คำแนะนำวิชาการ' },
  { icon: '📕', label: 'คู่มือบริหารจัดการน้ำและพืช' },
]
const supportItems = [
  { icon: '🧰', label: 'จัดการปัจจัยสำรอง' },
  { icon: '🚚', label: 'สต็อกเมล็ดพันธุ์ ปัจจัยสำรอง' },
  { icon: '📱', label: 'Mobile Report' },
  { icon: '📶', label: 'รายงานผ่านมือถือแบบเรียลไทม์' },
]
</script>

<template>
  <div class="page">
    <HeaderBar />
    <AlertBanner :alert="alerts?.[0]" />

    <div v-if="error" class="error-banner">{{ error }}</div>
    <div v-if="loading" class="loading-banner">กำลังโหลดข้อมูล...</div>

    <!-- <div class="top-row">
      <StatCards :summary="summary" />
      <div class="weather-slot">
        <WeatherCard />
      </div>
    </div> -->
    <StatCards :summary="summary" />

    <div class="main-grid">
      <aside class="col-nav">
        <SidebarNav />
      </aside>

      <section class="col-map">
        <RiskMapCard :provinces="provinces" :selected-province="selectedProvince" :weather-data="weatherData"
          :rainfall-data="rainfallData" :map-view="mapView" :rainfall-period="rainfallPeriod"
          @select-province="store.selectProvince($event)" @set-map-view="store.setMapView($event)"
          @set-rainfall-period="store.setRainfallPeriod($event)" />

        <div class="charts-row">
          <TrendLineChart :trend="trend" />
          <DisasterPieChart :breakdown="breakdown" />
          <TopProvincesBarChart :provinces="topDamaged" />
        </div>
      </section>

      <section class="col-status">
        <DisasterGauges :gauges="gauges" />
      </section>

      <aside class="col-panels">
        <InfoPanel title="ระบบแจ้งเตือนภัยและชี้เป้าหมาย (Early Warning & Risk Maps)" header-icon="🔔"
          color="var(--pdm-blue)" :items="earlyWarningItems" />
        <InfoPanel title="แดชบอร์ดติดตามสถานการณ์และการช่วยเหลือ (Monitoring & Assistance)" header-icon="📊"
          color="var(--pdm-green)" :items="monitoringItems" />
        <InfoPanel title="คลังสื่อความรู้และทางเลือกการเกษตร (Knowledge & Media)" header-icon="📚"
          color="var(--pdm-orange)" :items="knowledgeItems" />
        <InfoPanel title="ระบบสนับสนุนเจ้าหน้าที่ (Operation Support)" header-icon="🧑" color="var(--pdm-purple)"
          :items="supportItems" />
      </aside>
    </div>

    <PartnerLogos />
  </div>
</template>

<style scoped>
.page {
  min-height: 100%;
  padding-bottom: 20px;
}

/* .top-row {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 16px;
  align-items: start;
  padding-right: 22px;
}

.weather-slot {
  padding-top: 16px;
} */

.error-banner,
.loading-banner {
  margin: 12px 22px 0;
  padding: 10px 16px;
  border-radius: var(--radius-md);
  font-size: 13px;
}

.error-banner {
  background: #fdecea;
  color: var(--pdm-red);
}

.loading-banner {
  background: #eef4ff;
  color: var(--pdm-blue);
}

.main-grid {
  display: grid;
  grid-template-columns: 200px 2fr 1fr;
  grid-template-rows: auto auto;
  gap: 16px;
  padding: 16px 22px;
}

.col-nav {
  grid-row: 1 / 3;
}

.col-map {
  grid-column: 2;
  grid-row: 1 / 3;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.col-status {
  grid-column: 3;
  grid-row: 1;
}

.col-panels {
  grid-column: 3;
  grid-row: 2;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.charts-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 12px;
}

@media (max-width: 1100px) {
  .main-grid {
    grid-template-columns: 1fr;
  }

  .col-nav,
  .col-map,
  .col-status,
  .col-panels {
    grid-column: 1;
    grid-row: auto;
  }

  .charts-row {
    grid-template-columns: 1fr;
  }

  /* .top-row {
    grid-template-columns: 1fr;
    padding-right: 0;
  } */
}
</style>
