<template>
    <div class="min-h-screen bg-white text-gray-800 flex flex-col font-sans">

        <!-- HEADER WITH #1f5c3f GRADIENT -->
        <header
            class="bg-gradient-to-r from-[#1f5c3f] to-[#2d7d56] text-white shadow-md px-4 py-3 flex items-center justify-between sticky top-0 z-50">
            <div class="flex items-center space-x-3">
                <!-- Logo / Icon -->
                <div class="bg-white/20 p-2 rounded-lg">
                    <svg xmlns="http://w3.org" class="h-6 w-6 text-emerald-300" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h2.5M14 22c0-.517-.113-1.003-.316-1.442a4.433 4.433 0 00-1.442-1.442A4.43 4.43 0 0010.8 19c-.517 0-1.003.113-1.442.316a4.433 4.433 0 00-1.442 1.442A4.43 4.43 0 007.6 22.2" />
                    </svg>
                </div>
                <h1 class="text-lg md:text-xl font-bold tracking-wide">ระบบเฝ้าระวังน้ำและภัยพิบัติแห่งชาติ</h1>
            </div>

            <!-- User / Menu Actions -->
            <div class="flex items-center space-x-4">
                <button class="p-2 hover:bg-white/10 rounded-full transition-colors md:hidden"
                    @click="isSidebarOpen = !isSidebarOpen">
                    <svg xmlns="http://w3.org" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="hidden md:flex items-center space-x-2">
                    <a href="/" class="text-white no-underline hover:underline">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-700 flex items-center justify-center font-semibold text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </div>
                    </a>
                </div>
            </div>
        </header>

        <!-- MAIN CONTAINER (RESPONSIVE SPLIT) -->
        <div class="flex-1 flex flex-col md:flex-row relative overflow-hidden">

            <!-- INTERACTIVE MAP AREA (75% on Desktop, Full on Mobile) -->
            <main class="flex-1 h-[50vh] md:h-[calc(100vh-60px)] bg-gray-100 relative">
                <!-- ELEMENT สำหรับการเรนเดอร์แผนที่ Leaflet -->
                <div id="map-container" class="w-full h-full z-0" ref="mapContainer"></div>

                <!-- FLOATING CONTROLS & LAYERS SELECTOR (ยังคงวางทับบนแผนที่ได้ตามเดิม) -->
                <div class="absolute top-4 left-15 z-[1000] w-72 max-w-[calc(100%-32px)]">
                    <div
                        class="bg-white/95 backdrop-blur shadow-lg rounded-xl p-2 flex items-center border border-emerald-100">
                        <span class="p-2 text-gray-400">
                            <svg xmlns="http://w3.org" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="text" placeholder="ค้นหาพื้นที่ / จังหวัด..."
                            class="w-full bg-transparent border-none text-sm focus:outline-none py-1 text-gray-700" />
                    </div>
                </div>
            </main>

            <!-- SIDEBAR DATA PANEL (25% on Desktop, Bottom Sheet / Drawer on Mobile) -->
            <aside :class="[
                'w-full md:w-80 bg-white md:h-[calc(100vh-60px)] shadow-2xl md:shadow-none z-40 transition-transform duration-300 overflow-y-auto flex flex-col border-t md:border-t-0 md:border-l border-emerald-50',
                isSidebarOpen ? 'translate-y-0' : 'translate-y-full md:translate-y-0 fixed bottom-0 left-0 right-0 h-[60vh] md:relative md:h-auto'
            ]">
                <!-- Mobile Handle drag bar -->
                <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto my-3 md:hidden" @click="isSidebarOpen = false">
                </div>

                <div class="p-4 space-y-5 flex-1">
                    <a href="/">
                        <button class="p-2 hover:bg-white/10 rounded-full transition-colors md:hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </button>
                    </a>

                    <!-- SECTION 1: ALERT STATUS -->
                    <div class="bg-[#fcfdfc] p-4 rounded-xl border border-emerald-100">
                        <h3 class="font-bold text-gray-700 text-sm mb-3 flex items-center">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>
                            สรุปสถานการณ์ภัยพิบัติ
                        </h3>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between p-2 bg-red-50 rounded-lg text-red-700">
                                <span class="font-medium">🔴 Critical (วิกฤต)</span>
                                <span class="font-bold bg-red-200 px-2 py-0.5 rounded-full">2 พื้นที่</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-amber-50 rounded-lg text-amber-700">
                                <span class="font-medium">🟡 Warning (เฝ้าระวัง)</span>
                                <span class="font-bold bg-amber-200 px-2 py-0.5 rounded-full">5 พื้นที่</span>
                            </div>
                            <div
                                class="flex items-center justify-between p-2 bg-emerald-50 rounded-lg text-emerald-700">
                                <span class="font-medium">🟢 Normal (ปกติ)</span>
                                <span class="font-bold bg-emerald-200 px-2 py-0.5 rounded-full">ระดับดี</span>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: WEATHER DATA -->
                    <div>
                        <h3 class="font-bold text-gray-700 text-sm mb-3">🌤️ สภาพอากาศวันนี้</h3>
                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <p class="text-xs text-gray-500">อุณหภูมิเฉลี่ย</p>
                                <p class="text-lg font-bold text-gray-700 mt-0.5">32°C</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <p class="text-xs text-gray-500">โอกาสเกิดฝน</p>
                                <p class="text-lg font-bold text-emerald-600 mt-0.5">65%</p>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: WATER DATA -->
                    <div>
                        <h3 class="font-bold text-gray-700 text-sm mb-3">💧 ข้อมูลปริมาณน้ำ</h3>
                        <div class="space-y-3">
                            <!-- Water Reservoir Storage -->
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600">ปริมาณน้ำในเขื่อนรวม</span>
                                    <span class="font-semibold text-emerald-600">75%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2 rounded-full"
                                        style="width: 75%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ตัวอย่างการนำข้อมูลเขื่อนไปแสดงผลในหน้าเว็บ -->
                    <div v-for="dam in waterReservoirData" :key="dam.name"
                        class="bg-gray-50 p-3 rounded-xl border border-gray-100 mb-2">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-700 font-medium">{{ dam.name }} (จ.{{ dam.province }})</span>
                            <span :class="[
                                'font-semibold',
                                dam.status === 'critical' ? 'text-red-600' : dam.status === 'warning' ? 'text-amber-600' : 'text-emerald-600'
                            ]">{{ dam.percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div :class="[
                                'h-2 rounded-full transition-all duration-500',
                                dam.status === 'critical' ? 'bg-red-500' : dam.status === 'warning' ? 'bg-amber-500' : 'bg-emerald-500'
                            ]" :style="{ width: dam.percentage + '%' }"></div>
                        </div>
                    </div>


                </div>

                <!-- EMERGENCY BUTTON -->
                <div class="p-4 border-t border-gray-100 bg-white">
                    <button
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm py-2.5 px-4 rounded-xl shadow-md shadow-emerald-100 transition flex items-center justify-center space-x-2">
                        <svg xmlns="http://w3.org" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" stroke-width="1" />
                        </svg>
                        <span>แจ้งเหตุฉุกเฉิน / รายงานภัย</span>
                    </button>
                </div>
            </aside>
        </div>
    </div>

</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// 1. ข้อมูลจุดพิกัดภัยพิบัติสำหรับวาดหมุดบนแผนที่ (Disaster Incidents Markers)
const disasterMarkers = ref([
    {
        id: 'DIS-001',
        type: 'flood', // ประเภทภัย: น้ำท่วม
        title: 'น้ำท่วมขังผิวจราจร',
        location: 'อ.เมือง, จ.เชียงใหม่',
        coordinates: [18.7883, 98.9853], // [Latitude, Longitude]
        severity: 'critical', // ระดับความรุนแรง: วิกฤต (สีแดง)
        reportedAt: '2026-09-24 14:30',
        details: 'น้ำท่วมสูง 30-50 ซม. รถเล็กไม่สามารถสัญจรผ่านได้ คาดว่าจะคลี่คลายใน 3 ชม.'
    },
    {
        id: 'DIS-002',
        type: 'landslide', // ประเภทภัย: ดินสไลด์
        title: 'เฝ้าระวังดินสไลด์และน้ำป่าไหลหลาก',
        location: 'อ.แม่สอด, จ.ตาก',
        coordinates: [16.7161, 98.5674],
        severity: 'warning', // ระดับความรุนแรง: เฝ้าระวัง (สีส้ม)
        reportedAt: '2026-09-24 11:15',
        details: 'ตรวจพบปริมาณน้ำฝนสะสมเกิน 150 มม. ให้ประชาชนในพื้นที่ลาดเชิงเขาเตรียมพร้อมอพยพ'
    },
    {
        id: 'DIS-003',
        type: 'storm', // ประเภทภัย: พายุ/ลมกระโชกแรง
        title: 'ต้นไม้ล้มทับเสาไฟฟ้าจากลมกระโชกแรง',
        location: 'เขตจตุจักร, กรุงเทพมหานคร',
        coordinates: [13.8234, 100.5624],
        severity: 'warning', // ระดับความรุนแรง: เฝ้าระวัง (สีเหลือง)
        reportedAt: '2026-09-24 15:45',
        details: 'เจ้าหน้าที่กำลังเข้าดำเนินการตัดสิ่งกีดขวาง การจราจรติดขัดชะลอตัว'
    }
])

// 2. ข้อมูลปริมาณน้ำในอ่างเก็บน้ำ/เขื่อนหลัก (Reservoir Water Data)
const waterReservoirData = ref([
    {
        name: 'เขื่อนภูมิพล',
        province: 'ตาก',
        currentVolume: 9850, // ล้านลูกบาศก์เมตร
        maxCapacity: 13462, // ล้านลูกบาศก์เมตร
        percentage: 73, // เปอร์เซ็นต์ความจุ
        status: 'normal' // สถานการณ์ปกติ
    },
    {
        name: 'เขื่อนสิริกิติ์',
        province: 'อุตรดิตถ์',
        currentVolume: 8210,
        maxCapacity: 9510,
        percentage: 86,
        status: 'warning' // ปริมาณน้ำค่อนข้างสูง ต้องเฝ้าระวังการระบายน้ำ
    },
    {
        name: 'เขื่อนป่าสักชลสิทธิ์',
        province: 'ลพบุรี',
        currentVolume: 912,
        maxCapacity: 960,
        percentage: 95,
        status: 'critical' // ปริมาณน้ำวิกฤต ใกล้ล้นตลิ่ง
    }
])

// 3. ข้อมูลสภาพอากาศแยกตามภูมิภาค (Regional Weather Summary)
const weatherSummaryData = ref({
    updatedAt: '2026-09-24 16:00',
    overallCondition: 'มีฝนฟ้าคะนองกระจายตัวเป็นวงกว้าง',
    regions: [
        { name: 'ภาคเหนือ', temp: '28-32°C', rainChance: '70%', status: 'rain' },
        { name: 'ภาคกลาง', temp: '30-34°C', rainChance: '60%', status: 'cloudy' },
        { name: 'ภาคใต้', temp: '27-31°C', rainChance: '40%', status: 'sunny' }
    ]
})

// 4. สถิติสรุปภาพรวมความรุนแรง (Severity Dashboard Counter)
const disasterSummaryCounters = ref({
    criticalCount: 2,
    warningCount: 5,
    normalCount: 68
})


// ควบคุมการ เปิด/ปิด แผงข้อมูลด้านล่างในโหมดมือถือ (Mobile Drawer Toggle)
const isSidebarOpen = ref(false)

const mapContainer = ref(null);
let map = null;

// ฟังก์ชันสร้างไอคอนหมุดแบบแต่งสีตามระดับความรุนแรงภัย (Custom Marker Icons)
const createCustomIcon = (severity) => {
    let color = '#10B981' // สีเขียวปกติ
    if (severity === 'critical') color = '#EF4444' // สีแดงวิกฤต
    if (severity === 'warning') color = '#F59E0B' // สีส้ม/เหลืองเตือนภัย

    // สร้างไอคอนหมุดด้วย HTML/CSS สไตล์มินิมอลให้เข้ากับธีมขาว-เขียว
    return L.divIcon({
        className: 'custom-leaflet-icon',
        html: `
      <div style="
        background-color: ${color}; 
        width: 24px; 
        height: 24px; 
        border-radius: 50%; 
        border: 3px solid white; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        animation: pulse 2s infinite;
      "></div>
    `,
        iconSize: 5,
        iconAnchor: [12, 12]
    })
}
// เริ่มต้นวาดแผนที่เมื่อคอมโพเนนต์ถูกโหลดเข้าสู่หน้าจอ (onMounted)
onMounted(() => {
    // 2. สร้างตัวแผนที่และตั้งค่าพิกัดเริ่มต้นไปที่ใจกลางประเทศไทย [Lat, Lng], ระดับการซูมเริ่มต้น 6
    map = L.map(mapContainer.value).setView([13.7367, 100.5231], 6)

    // 3. ใช้แผนที่ฐาน (TileLayer) โทนสีขาวสว่าง สะอาดตา (CartoDB Positron) เพื่อให้เข้ากับธีมหลัก
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 20
    }).addTo(map)

    // 4. วนลูปนำข้อมูลจาก disasterMarkers ไปสร้างเป็นหมุดลงบนแผนที่
    // วนลูปสร้างหมุดปัก (ชุดคำสั่งเดิมของคุณ...)
    disasterMarkers.value.forEach(incident => {
        const markerIcon = createCustomIcon(incident.severity)
        const popupContent = `<strong>${incident.title}</strong>`
        L.marker(incident.coordinates, { icon: markerIcon }).bindPopup(popupContent).addTo(map)
    })

});

// ล้างหน่วยความจำแผนที่เมื่อผู้ใช้ออกจากหน้าเพจ (ป้องกัน Memory Leak)
onUnmounted(() => {
    if (map) {
        map.remove()
    }
})

</script>

<style scoped>
/* คุณสามารถเขียนสไตล์เพิ่มเติม หรือทำการผูกแผนที่ (Map Library Styles) ที่นี่ได้ */
#map-container {
    width: 100% !important;
    height: 100% !important;
    min-height: 400px;
    /* ป้องกันหน้าจอมือถือหดตัว */
}

/* เอฟเฟกต์หมุดกระพริบสำหรับภัยพิบัติ */
@keyframes pulse {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
    }

    70% {
        transform: scale(1);
        box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
    }

    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
    }
}
</style>
