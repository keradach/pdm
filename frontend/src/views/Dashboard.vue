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
  damWaterData,
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
  { icon: '🚚', label: 'สต็อกชีวภัณฑ์' },
  { icon: '🚚', label: 'สต็อกเมล็ดพันธุ์ ปัจจัยสำรอง' },
  // { icon: '📱', label: 'Mobile Report' },
  // { icon: '📶', label: 'รายงานผ่านมือถือแบบเรียลไทม์' },
]
</script>

<template>
  <div class="page">
    <HeaderBar />
    <!-- <AlertBanner :alert="alerts?.[0]" /> -->

    <div v-if="error" class="error-banner">{{ error }}</div>
    <div v-if="loading" class="loading-banner">กำลังโหลดข้อมูล...</div>

    <!-- <div class="top-row">
      <StatCards :summary="summary" />
      <div class="weather-slot">
        <WeatherCard />
      </div>
    </div> -->
    <!-- <StatCards :summary="summary" /> -->

    <div class="main-grid">
      <aside class="col-nav">
        <SidebarNav />
      </aside>

      <section class="col-map">
        <RiskMapCard :provinces="provinces" :selected-province="selectedProvince" :weather-data="weatherData"
          :rainfall-data="rainfallData" :map-view="mapView" :rainfall-period="rainfallPeriod"
          :dam-water-data="damWaterData" @select-province="store.selectProvince($event)"
          @set-map-view="store.setMapView($event)" @set-rainfall-period="store.setRainfallPeriod($event)" />
        <div class="charts-row">
          <TrendLineChart :trend="trend" />
          <DisasterPieChart :breakdown="breakdown" />
          <TopProvincesBarChart :provinces="topDamaged" />
        </div>
      </section>

      <section class="col-status">
        <!-- <DisasterGauges :gauges="gauges" /> -->
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
      </section>


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
  grid-template-columns: minmax(180px, 200px) minmax(0, 2fr) minmax(260px, 1fr);
  grid-template-rows: auto auto;
  gap: 16px;
  padding: 16px 22px;
}

.main-grid>*,
.col-map,
.col-status {
  min-width: 0;
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
  grid-row: 1 / 3;
}

/* col-panels is a PLAIN child of .col-status (not a grid child of .main-grid);
   it fills col-status width and arranges the 4 InfoPanels responsively. */
.col-panels {
  display: grid;
  /* 1-up in the narrow desktop sidebar column, auto-reflows 2-up where there is
     room (tablet). 340px min keeps it at most 2 columns -> big enough for the
     long Thai labels. */
  grid-template-columns: repeat(auto-fill, minmax(min(100%, 340px), 1fr));
  gap: 12px;
  align-content: start;
}

.col-panels>* {
  min-width: 0;
}

.charts-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 240px), 1fr));
  gap: 12px;
}

.charts-row>* {
  min-width: 0;
  width: 100%;
}

@media (max-width: 1280px) {
  .main-grid {
    grid-template-columns: 180px minmax(0, 1fr);
  }

  .col-nav {
    grid-row: 1 / 3;
  }

  .col-map {
    grid-column: 2;
    grid-row: 1;
  }

  /* place the status panels below the map so they no longer overlap it */
  .col-status {
    grid-column: 2;
    grid-row: 2;
  }
}

@media (max-width: 900px) {
  .main-grid {
    grid-template-columns: 1fr;
    gap: 12px;
    padding: 12px 14px;
  }

  .col-nav,
  .col-map,
  .col-status {
    grid-column: 1;
    grid-row: auto;
  }

  /* stack the 4 panels into a single column for readability on small screens */
  .col-panels {
    grid-template-columns: 1fr;
  }

  .charts-row {
    grid-template-columns: 1fr;
  }

  .error-banner,
  .loading-banner {
    margin-left: 14px;
    margin-right: 14px;
  }

  /* .top-row {
    grid-template-columns: 1fr;
    padding-right: 0;
  } */
}

@media (max-width: 480px) {
  .page {
    padding-bottom: 12px;
  }

  .stat-row {
    padding-left: 14px;
    padding-right: 14px;
  }
}

@media (max-width: 640px) {
  .main-grid {
    padding: 10px;
  }

  .error-banner,
  .loading-banner {
    margin-left: 10px;
    margin-right: 10px;
  }
}
</style>
