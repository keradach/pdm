<script setup>
import { onMounted, ref } from "vue";
import api from "@/services/api";

const forecast = ref([]);
const locationName = ref("");
const loading = ref(true);
const errorMessage = ref("");
const updatedAt = ref("");

// ฟังก์ชันแปลง Weather Code ของ Open-Meteo ให้เป็นข้อความภาษาไทย
function getWeatherDescription(code) {
  const weatherCodes = {
    0: "ท้องฟ้าแจ่มใส",
    1: "ท้องฟ้าโปร่ง", 2: "มีเมฆบางส่วน", 3: "เมฆครึ้ม",
    45: "หมอกหนา", 48: "หมอกน้ำค้างแข็ง",
    51: "ฝนละอองเบาบาง", 53: "ฝนละอองปานกลาง", 55: "ฝนละอองหนาแน่น",
    61: "ฝนตกปรอยๆ", 63: "ฝนตกปานกลาง", 65: "ฝนตกหนัก",
    71: "หิมะตกเล็กน้อย", 73: "หิมะตกปานกลาง", 75: "หิมะตกหนัก",
    80: "ฝนซู่เบาบาง", 81: "ฝนซู่ปานกลาง", 82: "ฝนซู่รุนแรง",
    95: "ฝนฟ้าคะนอง", 96: "ฝนฟ้าคะนองพร้อมลูกเห็บตกเล็กน้อย", 99: "ฝนฟ้าคะนองพร้อมลูกเห็บตกหนัก"
  };
  return weatherCodes[code] || "ไม่ทราบสภาพอากาศ";
}

function getDevicePosition() {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error("เบราว์เซอร์นี้ไม่รองรับการระบุตำแหน่ง"));
      return;
    }

    navigator.geolocation.getCurrentPosition(
      ({ coords }) => resolve({ latitude: coords.latitude, longitude: coords.longitude }),
      (error) => reject(new Error(error.code === 1
        ? "กรุณาอนุญาตการเข้าถึงตำแหน่ง เพื่อดูพยากรณ์อากาศในพื้นที่ของคุณ"
        : "ไม่สามารถระบุตำแหน่งอุปกรณ์ได้ กรุณาลองใหม่อีกครั้ง")),
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 300000 },
    );
  });
}

async function loadWeather() {
  loading.value = true;
  errorMessage.value = "";

  try {
    const { latitude, longitude } = await getDevicePosition();
    const [weatherResult, locationResult] = await Promise.allSettled([
      api.getWeatherForecast(latitude, longitude),
      api.getReverseGeocode(latitude, longitude),
    ]);

    if (weatherResult.status === "rejected") throw weatherResult.reason;
    const weatherData = weatherResult.value;
    if (!weatherData || !weatherData.daily) {
      throw new Error("ไม่สามารถโหลดข้อมูลพยากรณ์อากาศได้");
    }

    const daily = weatherData.daily;
    forecast.value = daily.time.map((date, index) => ({
      date,
      weather: getWeatherDescription(daily.weather_code[index]),
      maxTemp: daily.temperature_2m_max[index],
      minTemp: daily.temperature_2m_min[index],
      rainChance: daily.precipitation_probability_max[index],
    }));

    if (locationResult.status === "fulfilled" && locationResult.value) {
      const { address = {} } = locationResult.value;
      const subdistrict = address.suburb;
      const district = address.quarter;
      const province = address.city;
      locationName.value = [
        province && `จ.${province.replace(/^จังหวัด/, "")}`,
        district && `อ.${district.replace(/^(อำเภอ|เขต)/, "")}`,
        subdistrict && `ต.${subdistrict.replace(/^(ตำบล|แขวง)/, "")}`
      ].filter(Boolean).join(" ") || "ไม่พบข้อมูลพื้นที่";
    } else {
      locationName.value = "ไม่สามารถระบุชื่อพื้นที่ได้";
    }

    updatedAt.value = new Intl.DateTimeFormat("th-TH", {
      hour: "2-digit",
      minute: "2-digit",
      timeZone: "Asia/Bangkok",
    }).format(new Date());
  } catch (error) {
    errorMessage.value = error.message || "เกิดข้อผิดพลาดในการโหลดข้อมูลอากาศ";
    forecast.value = [];
    locationName.value = "";
  } finally {
    loading.value = false;
  }
}

function formatDate(date) {
  return new Intl.DateTimeFormat("th-TH", {
    weekday: "short",
    day: "numeric",
    month: "short",
    timeZone: "Asia/Bangkok",
  }).format(new Date(`${date}T00:00:00+07:00`));
}

onMounted(loadWeather);
</script>

<template>
  <section class="card min-w-0 p-2" aria-labelledby="weather-heading">
    <div class="flex flex-wrap items-start justify-between gap-2 px-2">
      <div>
        <h2 id="weather-heading" class="text-[15px] font-semibold text-ink">พยากรณ์อากาศ 7 วัน</h2>
      </div>
      <span class="text-[12px] text-muted">{{ locationName || (loading ? "กำลังระบุตำแหน่ง..." : "") }}</span>
    </div>

    <p v-if="loading" class="py-4 text-center text-sm text-muted" role="status">กำลังโหลดพยากรณ์อากาศ...</p>
    <div v-else-if="errorMessage" class="flex flex-wrap items-center justify-between gap-3 py-4">
      <p class="text-sm text-pdm-red" role="alert">{{ errorMessage }}</p>
      <button class="rounded border border-edge px-3 py-1.5 text-sm text-ink hover:bg-page" @click="loadWeather">
        ลองอีกครั้ง
      </button>
    </div>

    <div v-else class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7 sm:px-1 xl:px-10">
      <article v-for="day in forecast" :key="day.date"
        class="flex min-w-0 flex-col gap-2 rounded-md border border-edge bg-white p-2 shadow-sm">
        <h3 class="rounded-sm bg-pdm-green-deep px-1.5 py-1.5 text-center text-[12px] text-white">
          {{ formatDate(day.date) }}
        </h3>
        <div class="flex min-h-[36px] flex-col justify-center rounded-sm bg-page px-2 py-2 text-center">
          <p class="mt-1 text-sm font-semibold leading-5 text-ink">{{ day.weather }}</p>
          <p class="mt-1 flex justify-between gap-1 items-center text-center text-pdm-blue">
            <span class="px-1 text-[10px] font-semibold leading-4">โอกาสฝน</span>
            <span class="px-1 text-base font-extrabold">{{ day.rainChance ?? "-" }}%</span>
          </p>

        </div>
        <p class="text-center text-sm font-bold text-ink">
          {{ Math.round(day.maxTemp) }}°C
          <span class="text-muted">/ {{ Math.round(day.minTemp) }}°C</span>
        </p>
      </article>
    </div>

    <div v-if="!loading"
      class="mt-2 flex flex-wrap justify-between gap-2 border-t border-edge pt-2 px-2 text-[11px] text-muted">
      <p v-if="updatedAt">อัปเดต {{ updatedAt }} น.</p>
      <a href="https://Open-Meteo.com" target="_blank" rel="noreferrer" class="hover:underline">
        © Open-Meteo
      </a>
    </div>
  </section>
</template>
