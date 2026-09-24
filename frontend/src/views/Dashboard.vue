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
import DisasterTypeCard from '@/components/DisasterTypeCard.vue'
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
  rainfallData,
  damWaterData,
  mapView,
  rainfallPeriod,
} = storeToRefs(store)

onMounted(() => store.fetchAll())
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
        <div class="map-layout">
          <div class="map-area">
            <RiskMapCard :provinces="provinces" :selected-province="selectedProvince" :rainfall-data="rainfallData"
              :map-view="mapView" :rainfall-period="rainfallPeriod" :dam-water-data="damWaterData"
              @select-province="store.selectProvince($event)" @set-map-view="store.setMapView($event)"
              @set-rainfall-period="store.setRainfallPeriod($event)" />
          </div>
          <div class="side-charts">
            <TrendLineChart :trend="trend" />
            <DisasterPieChart :breakdown="breakdown" />
          </div>
        </div>
        <div class="bottom-chart">
          <DisasterTypeCard :provinces="topDamaged" />
        </div>
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
  grid-template-columns: minmax(180px, 200px) minmax(0, 1fr);
  grid-template-rows: auto auto;
  gap: 16px;
  padding: 16px 22px;
}

.main-grid>*,
.col-map {
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

.map-layout {
  display: grid;
  grid-template-columns: 3fr 2fr;
  /* RiskMap 60% / side-charts 40% */
  gap: 12px;
  align-items: stretch;
}

.map-area {
  display: flex;
  min-width: 0;
}

.map-area>* {
  flex: 1;
  width: 100%;
}

.side-charts {
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-width: 0;
}

.side-charts>* {
  min-width: 0;
  width: 100%;
}

.bottom-chart {
  min-width: 0;
}

.bottom-chart>* {
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
}

@media (max-width: 900px) {
  .main-grid {
    grid-template-columns: 1fr;
    gap: 12px;
    padding: 12px 14px;
  }

  .col-nav,
  .col-map {
    grid-column: 1;
    grid-row: auto;
  }

  /* Stack map full-width, side charts beside each other below the map */
  .map-layout {
    grid-template-columns: 1fr;
  }

  .side-charts {
    display: grid;
    grid-template-columns: 1fr 1fr;
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

  /* On small screens, stack the side charts into a single column */
  .side-charts {
    display: flex;
    flex-direction: column;
  }

  .error-banner,
  .loading-banner {
    margin-left: 10px;
    margin-right: 10px;
  }
}
</style>
