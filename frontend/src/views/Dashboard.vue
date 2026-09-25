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
  <!-- <AlertBanner :alert="alerts?.[0]" /> -->

  <div v-if="error"
    class="error-banner bg-[#fdecea] text-pdm-red border border-[#f5c6c2] rounded-[10px] p-[10px_16px] text-[13px]">{{
      error }}</div>
  <div v-if="loading" class="loading-banner bg-[#eef4ff] text-pdm-blue rounded-[10px] p-[10px_16px] text-[13px]">
    กำลังโหลดข้อมูล...</div>

  <!-- <div class="top-row">
      <StatCards :summary="summary" />
      <div class="weather-slot">
        <WeatherCard />
      </div>
    </div> -->
  <!-- <StatCards :summary="summary" /> -->

  <section class="flex flex-col gap-4">
    <div class="grid grid-cols-[4fr_2fr] gap-3 items-stretch max-[900px]:grid-cols-1">
      <div class="flex min-w-0">
        <RiskMapCard class="flex-1 w-full min-w-0" :provinces="provinces" :selected-province="selectedProvince"
          :rainfall-data="rainfallData" :map-view="mapView" :rainfall-period="rainfallPeriod"
          :dam-water-data="damWaterData" @select-province="store.selectProvince($event)"
          @set-map-view="store.setMapView($event)" @set-rainfall-period="store.setRainfallPeriod($event)" />
      </div>
      <div
        class="flex flex-col gap-3 min-w-0 max-[900px]:grid max-[900px]:grid-cols-2 max-[640px]:flex max-[640px]:flex-col">
        <TrendLineChart class="w-full min-w-0" :trend="trend" />
        <DisasterPieChart class="w-full min-w-0" :breakdown="breakdown" />
      </div>
    </div>
    <div class="min-w-0">
      <DisasterTypeCard class="w-full" :provinces="topDamaged" />
    </div>
  </section>

  <PartnerLogos />
</template>
