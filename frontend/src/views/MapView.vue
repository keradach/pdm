<template>
    <div class="h-screen w-full bg-white text-gray-800 flex flex-col font-sans overflow-hidden">

        <!-- HEADER WITH #1f5c3f GRADIENT -->
        <header
            class="shrink-0 bg-gradient-to-r from-[#1f5c3f] to-[#2d7d56] text-white shadow-md px-4 py-3 flex items-center justify-between z-30">
            <div class="flex items-center space-x-3">
                <!-- Logo / Icon -->
                <div class="bg-white/20 p-2 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-300" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h2.5M14 22c0-.517-.113-1.003-.316-1.442a4.433 4.433 0 00-1.442-1.442A4.43 4.43 0 0010.8 19c-.517 0-1.003.113-1.442.316a4.433 4.433 0 00-1.442 1.442A4.43 4.43 0 007.6 22.2" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg md:text-xl font-bold tracking-wide leading-tight">ระบบแผนที่วิเคราะห์ข้อมูล</h1>
                    <p class="text-[11px] text-emerald-100 hidden sm:block">ปริมาณฝนเฉลี่ยสะสม (RiskMap DOAE), พิกัดน้ำในเขื่อน (ThaiWater) และอุณหภูมิ (TMD)</p>
                </div>
            </div>

            <!-- Quick National Summary Badges on Header (Desktop) -->
            <div
                class="hidden xl:flex items-center space-x-4 text-xs bg-black/15 px-3 py-1.5 rounded-lg border border-white/10">
                <div class="flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                    <span class="text-emerald-100">น้ำในเขื่อนรวม:</span>
                    <span class="font-bold text-white">{{ nationalWaterSummary.percent ? nationalWaterSummary.percent +
                        '%' : 'กำลังโหลด...' }}</span>
                </div>
                <div class="h-3 w-px bg-white/20"></div>
                <div class="flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span class="text-emerald-100">อุณหภูมิเฉลี่ย:</span>
                    <span class="font-bold text-white">{{ nationalWeatherSummary.avgTemp ?
                        nationalWeatherSummary.avgTemp + '°C' : 'กำลังโหลด...' }}</span>
                </div>
                <div class="h-3 w-px bg-white/20"></div>
                <div class="flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-sky-300"></span>
                    <span class="text-emerald-100">ฝนเฉลี่ย ({{ currentPeriodShortLabel }}):</span>
                    <span class="font-bold text-white">{{ nationalRainSummary.avgRain ?
                        nationalRainSummary.avgRain + ' มม.' : '-' }}</span>
                </div>
            </div>

            <!-- User / Menu Actions -->
            <div class="flex items-center space-x-4">
                <button class="p-2 hover:bg-white/10 rounded-full transition-colors md:hidden"
                    @click="isSidebarOpen = !isSidebarOpen">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="hidden md:flex items-center space-x-2">
                    <a href="/"
                        class="text-white no-underline flex items-center space-x-2 bg-emerald-700/80 hover:bg-emerald-700 px-3 py-1.5 rounded-lg transition text-xs font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </a>
                </div>
            </div>
        </header>

        <!-- MAIN CONTAINER (RESPONSIVE SPLIT) -->
        <div class="flex-1 flex flex-col md:flex-row relative overflow-hidden min-h-0 w-full">

            <!-- INTERACTIVE MAP AREA (Full height, flexible width) -->
            <main class="flex-1 h-full w-full relative min-h-0 overflow-hidden bg-gray-100">
                <!-- ELEMENT สำหรับการเรนเดอร์แผนที่ Leaflet -->
                <div id="map-container" class="w-full h-full z-0" ref="mapContainer"></div>

                <!-- LOADING OVERLAY -->
                <div v-if="isLoading"
                    class="absolute inset-0 z-[1100] bg-white/60 backdrop-blur-xs flex items-center justify-center pointer-events-none">
                    <div
                        class="bg-white/95 px-4 py-3 rounded-xl shadow-lg border border-emerald-100 flex items-center space-x-3">
                        <div class="w-5 h-5 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin">
                        </div>
                        <span class="text-xs font-semibold text-gray-700">กำลังเชื่อมต่อ API เขื่อน, อุณหภูมิ และ RiskMap...</span>
                    </div>
                </div>

                <!-- FLOATING CONTROLS & LAYERS SELECTOR (วางทับบนแผนที่) -->
                <div
                    class="absolute top-2 left-12 right-3 sm:right-auto sm:left-11 z-[1000] flex flex-col gap-2 pointer-events-none">

                    <!-- LAYER FILTER BUTTONS -->
                    <div
                        class="bg-white/95 backdrop-blur shadow-lg rounded-xl p-1.5 flex flex-wrap items-center gap-1 border border-emerald-100 pointer-events-auto">
                        <!-- ทั้งหมด (Master toggle) -->
                        <label :class="[
                            'flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold cursor-pointer select-none transition border',
                            allLayersOn ? 'bg-[#1f5c3f] text-white border-[#1f5c3f]' : 'text-gray-600 hover:bg-gray-100 border-black/5'
                        ]">
                            <input type="checkbox" :checked="allLayersOn" @change="toggleAll"
                                class="h-3.5 w-3.5 rounded accent-white cursor-pointer" />
                            <span>🗺️ ทั้งหมด</span>
                        </label>

                        <!-- ฝนเฉลี่ย RiskMap -->
                        <label :class="[
                            'flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold cursor-pointer select-none transition border',
                            enabledLayers.riskmap ? 'bg-cyan-700 text-white border-cyan-700' : 'text-gray-600 hover:bg-gray-100 border-black/5'
                        ]">
                            <input type="checkbox" v-model="enabledLayers.riskmap" @change="syncMapLayers"
                                class="h-3.5 w-3.5 rounded accent-white cursor-pointer" />
                            <span>🌧️ ฝน RiskMap</span>
                            <span class="text-[10px] opacity-80">({{ provinceList.length }})</span>
                        </label>

                        <!-- เขื่อน -->
                        <label :class="[
                            'flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold cursor-pointer select-none transition border',
                            enabledLayers.dams ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-600 hover:bg-gray-100 border-black/5'
                        ]">
                            <input type="checkbox" v-model="enabledLayers.dams" @change="syncMapLayers"
                                class="h-3.5 w-3.5 rounded accent-white cursor-pointer" />
                            <span>💧 เขื่อน</span>
                            <span class="text-[10px] opacity-80">({{ waterReservoirData.length }})</span>
                        </label>

                        <!-- อุณหภูมิ -->
                        <label :class="[
                            'flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold cursor-pointer select-none transition border',
                            enabledLayers.temperature ? 'bg-amber-600 text-white border-amber-600' : 'text-gray-600 hover:bg-gray-100 border-black/5'
                        ]">
                            <input type="checkbox" v-model="enabledLayers.temperature" @change="syncMapLayers"
                                class="h-3.5 w-3.5 rounded accent-white cursor-pointer" />
                            <span>🌡️ อุณหภูมิ</span>
                            <span class="text-[10px] opacity-80">({{ rawStations.length }})</span>
                        </label>

                        <button @click="resetMapView" title="รีเซ็ตมุมมองประเทศไทย"
                            class="p-1 rounded-lg text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>

                    <!-- PERIOD SELECTOR (เมื่อเปิดเลเยอร์ RiskMap) -->
                    <div v-if="enabledLayers.riskmap"
                        class="bg-white/95 backdrop-blur shadow-lg rounded-xl p-1 flex items-center gap-1 border border-emerald-100 pointer-events-auto self-start text-[11px]">
                        <span class="text-gray-500 font-medium px-1.5 text-[10px]">ช่วงเวลาฝน:</span>
                        <button v-for="p in rainAveragePeriods" :key="p.key"
                            @click="setRainPeriod(p.key)"
                            :class="[
                                'px-2 py-0.5 rounded-lg transition font-medium',
                                selectedRainPeriod === p.key ? 'bg-cyan-700 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'
                            ]">
                            {{ p.shortLabel }}
                        </button>
                    </div>

                    <!-- SEARCH BOX WITH DROPDOWN -->
                    <div class="relative w-full sm:w-80 pointer-events-auto">
                        <div
                            class="bg-white/95 backdrop-blur shadow-lg rounded-xl p-2 flex items-center border border-emerald-100">
                            <span class="p-1.5 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input v-model="searchQuery" type="text"
                                placeholder="ค้นหาจังหวัด, เขื่อน หรือสถานี..."
                                class="w-full bg-transparent border-none text-xs focus:outline-none py-1 text-gray-700 placeholder-gray-400" />
                            <button v-if="searchQuery" @click="searchQuery = ''"
                                class="p-1 text-gray-400 hover:text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        <!-- SEARCH AUTO-COMPLETE RESULTS -->
                        <div v-if="searchQuery.trim().length > 1 && searchResults.length > 0"
                            class="absolute top-full left-0 right-0 mt-1 bg-white/95 backdrop-blur shadow-xl rounded-xl border border-gray-100 max-h-56 overflow-y-auto z-50 divide-y divide-gray-100">
                            <div v-for="(item, idx) in searchResults" :key="idx" @click="flyToItem(item)"
                                class="p-2 hover:bg-emerald-50 cursor-pointer flex items-center justify-between text-xs transition">
                                <div class="truncate mr-2">
                                    <p class="font-bold text-gray-800 truncate">
                                        {{ item.type === 'dam' ? '💧' : item.type === 'province' ? '🌧️' : '🌡️' }}
                                        {{ item.title }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 truncate">{{ item.subtitle }}</p>
                                </div>
                                <span
                                    :class="['px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0', item.badgeClass]">
                                    {{ item.valueText }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FLOATING MAP LEGEND (BOTTOM-LEFT) -->
                <div class="absolute bottom-4 left-4 z-[1000] hidden sm:block">
                    <div
                        class="bg-white/95 backdrop-blur shadow-xl rounded-xl p-3 border border-gray-100 text-xs max-w-xs space-y-2">
                        <div
                            class="flex items-center justify-between font-bold text-gray-700 pb-1 border-b border-gray-100">
                            <span>คำอธิบายสัญลักษณ์พิกัด</span>
                        </div>

                        <!-- คำอธิบาย RiskMap ฝนเฉลี่ยสะสม -->
                        <div v-if="enabledLayers.riskmap">
                            <p class="font-semibold text-gray-600 mb-1 text-[11px]">🌧️ ฝนเฉลี่ยสะสม ({{ currentPeriodShortLabel }}):</p>
                            <div class="grid grid-cols-2 gap-1 text-[10px]">
                                <div class="flex items-center space-x-1">
                                    <span class="w-2.5 h-2.5 rounded-sm bg-[#dc3545]"></span>
                                    <span>≥35 มม. (หนักมาก)</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="w-2.5 h-2.5 rounded-sm bg-[#fd7e14]"></span>
                                    <span>20-35 มม. (หนัก)</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="w-2.5 h-2.5 rounded-sm bg-[#ffc107]"></span>
                                    <span>10-20 มม. (ปานกลาง)</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="w-2.5 h-2.5 rounded-sm bg-[#198754]"></span>
                                    <span>0.1-10 มม. (เล็กน้อย)</span>
                                </div>
                                <div class="flex items-center space-x-1 col-span-2">
                                    <span class="w-2.5 h-2.5 rounded-sm bg-[#0dcaf0]"></span>
                                    <span>0 มม. (ไม่มีฝน)</span>
                                </div>
                            </div>
                        </div>

                        <!-- คำอธิบาย เขื่อน -->
                        <div v-if="enabledLayers.dams" :class="enabledLayers.riskmap ? 'pt-1 border-t border-gray-100' : ''">
                            <p class="font-semibold text-gray-600 mb-1 text-[11px]">💧 ปริมาณน้ำในเขื่อน (%):</p>
                            <div class="grid grid-cols-2 gap-1 text-[10px]">
                                <div class="flex items-center space-x-1"><span
                                        class="w-2.5 h-2.5 rounded-full bg-red-600"></span><span>>100% ล้นเขื่อน</span>
                                </div>
                                <div class="flex items-center space-x-1"><span
                                        class="w-2.5 h-2.5 rounded-full bg-orange-500"></span><span>80-100%
                                        น้ำมาก</span></div>
                                <div class="flex items-center space-x-1"><span
                                        class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span><span>50-80% ปกติ</span>
                                </div>
                                <div class="flex items-center space-x-1"><span
                                        class="w-2.5 h-2.5 rounded-full bg-cyan-600"></span><span>30-50% น้ำน้อย</span>
                                </div>
                                <div class="flex items-center space-x-1"><span
                                        class="w-2.5 h-2.5 rounded-full bg-blue-600"></span><span>&lt;30%
                                        วิกฤตน้อย</span></div>
                            </div>
                        </div>

                        <!-- คำอธิบาย อุณหภูมิ -->
                        <div v-if="enabledLayers.temperature" class="pt-1 border-t border-gray-100">
                            <p class="font-semibold text-gray-600 mb-1 text-[11px]">🌡️ อุณหภูมิสถานีตรวจวัด (°C):</p>
                            <div class="flex items-center justify-between text-[10px] gap-1">
                                <span class="px-1 py-0.5 rounded bg-blue-500 text-white font-bold">&lt;24°</span>
                                <span class="px-1 py-0.5 rounded bg-emerald-500 text-white font-bold">24-28°</span>
                                <span class="px-1 py-0.5 rounded bg-yellow-500 text-white font-bold">28-32°</span>
                                <span class="px-1 py-0.5 rounded bg-orange-500 text-white font-bold">32-35°</span>
                                <span class="px-1 py-0.5 rounded bg-red-600 text-white font-bold">>35°</span>
                            </div>
                        </div>
                    </div>
                </div>

            </main>

            <!-- Mobile Backdrop Overlay -->
            <div v-if="isSidebarOpen" @click="isSidebarOpen = false"
                class="fixed inset-0 bg-black/40 backdrop-blur-xs z-30 md:hidden transition-opacity"></div>

            <!-- SIDEBAR DATA PANEL (Scrollable sidebar on desktop, Bottom Sheet on mobile) -->
            <aside :class="[
                'bg-white z-40 transition-transform duration-300 flex flex-col overflow-y-auto overscroll-contain scrollbar-thin border-emerald-100',
                'md:relative md:translate-y-0 md:w-80 lg:w-96 md:h-full md:shrink-0 md:border-l md:border-t-0 md:rounded-none md:shadow-none',
                'fixed inset-x-0 bottom-0 h-[70vh] rounded-t-2xl shadow-2xl border-t',
                isSidebarOpen ? 'translate-y-0' : 'translate-y-full md:translate-y-0'
            ]">
                <!-- Mobile Handle drag bar -->
                <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto my-3 md:hidden cursor-pointer"
                    @click="isSidebarOpen = false">
                </div>

                <div class="p-3 space-y-5 flex-1">
                    <a href="/">
                        <button class="hover:bg-white/10 rounded-full transition-colors md:hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </button>
                    </a>

                    <!-- SECTION 1: RISKMAP RAINFALL DATA (DOAE API REALTIME) -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold text-gray-700 text-sm flex items-center space-x-1">
                                <span>🌧️ ฝนเฉลี่ยสะสม (RiskMap)</span>
                            </h3>
                            <span
                                class="text-[10px] text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded-full font-medium">riskmap.doae.go.th</span>
                        </div>

                        <!-- Period buttons in Sidebar -->
                        <div class="grid grid-cols-4 gap-1 mb-2.5">
                            <button v-for="period in rainAveragePeriods" :key="period.key" type="button"
                                @click="setRainPeriod(period.key)"
                                :class="[
                                    'py-1 text-[11px] rounded-lg transition font-medium text-center border',
                                    selectedRainPeriod === period.key
                                        ? 'bg-[#1f5c3f] text-white border-[#1f5c3f] shadow-xs'
                                        : 'bg-gray-50 hover:bg-gray-100 text-gray-600 border-gray-200'
                                ]">
                                {{ period.shortLabel }}
                            </button>
                        </div>

                        <!-- RiskMap Summary Stats -->
                        <div class="grid grid-cols-2 gap-2 text-center mb-2.5">
                            <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                <p class="text-[11px] text-gray-500">ฝนเฉลี่ยทั้งประเทศ</p>
                                <p class="text-base font-bold text-cyan-700 mt-0.5">
                                    {{ nationalRainSummary.avgRain }} มม.
                                </p>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                <p class="text-[11px] text-gray-500">จังหวัดมีฝนตก</p>
                                <p class="text-base font-bold text-emerald-600 mt-0.5">
                                    {{ nationalRainSummary.rainingProvincesCount }} / {{ provinceList.length }}
                                </p>
                            </div>
                        </div>

                        <!-- Top 5 Provinces by Rain -->
                        <div v-if="nationalRainSummary.topProvinces && nationalRainSummary.topProvinces.length" class="space-y-1.5">
                            <div class="flex justify-between items-center text-[11px] font-semibold text-gray-600">
                                <span>5 อันดับจังหวัดฝนตกสูงสุด (คลิกซูม):</span>
                                <span class="text-[10px] text-gray-400">ฝนสะสม</span>
                            </div>
                            <div class="space-y-1">
                                <div v-for="(prov, idx) in nationalRainSummary.topProvinces" :key="prov.code"
                                    @click="flyToProvince(prov)"
                                    class="bg-gray-50 hover:bg-cyan-50/70 p-2 rounded-lg border border-gray-100 hover:border-cyan-200 cursor-pointer transition text-xs">
                                    <div class="flex justify-between items-center mb-1">
                                        <div class="flex items-center space-x-1.5 truncate">
                                            <span class="w-4 h-4 rounded-full bg-cyan-100 text-cyan-800 text-[10px] font-bold flex items-center justify-center shrink-0">
                                                {{ idx + 1 }}
                                            </span>
                                            <span class="font-medium text-gray-700 truncate">จ.{{ prov.name }}</span>
                                        </div>
                                        <span class="font-bold shrink-0" :style="{ color: getRainAvgColor(prov[selectedRainPeriod]) }">
                                            {{ prov[selectedRainPeriod] != null ? Number(prov[selectedRainPeriod]).toFixed(1) : '0.0' }} มม.
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full transition-all duration-500"
                                            :style="{
                                                width: Math.min((Number(prov[selectedRainPeriod]) || 0) / Math.max(Number(nationalRainSummary.maxRain) || 1, 1) * 100, 100) + '%',
                                                backgroundColor: getRainAvgColor(prov[selectedRainPeriod])
                                            }">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    <!-- SECTION 2: WEATHER DATA (TMD API REALTIME) -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold text-gray-700 text-sm flex items-center space-x-1">
                                <span>🌤️ สภาพอากาศและอุณหภูมิ</span>
                            </h3>
                            <span
                                class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-medium">TMD
                                API</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-center mb-2">
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <p class="text-xs text-gray-500">อุณหภูมิเฉลี่ยทั้งประเทศ</p>
                                <p class="text-lg font-bold text-gray-700 mt-0.5">
                                    {{ nationalWeatherSummary.avgTemp ? nationalWeatherSummary.avgTemp + '°C' : '32°C'
                                    }}
                                </p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <p class="text-xs text-gray-500">สถานีตรวจวัด</p>
                                <p class="text-lg font-bold text-emerald-600 mt-0.5">
                                    {{ nationalWeatherSummary.count || rawStations.length }} แห่ง
                                </p>
                            </div>
                        </div>

                        <!-- Regional Temperature Breakdown -->
                        <div v-if="nationalWeatherSummary.regions && nationalWeatherSummary.regions.length"
                            class="space-y-1 text-xs">
                            <p class="text-[11px] font-semibold text-gray-600 mb-1">อุณหภูมิเฉลี่ยรายภาค (คลิกเพื่อซูม):
                            </p>
                            <div class="grid grid-cols-2 gap-1.5">
                                <div v-for="reg in nationalWeatherSummary.regions" :key="reg.name"
                                    @click="zoomToRegion(reg.name)"
                                    class="bg-gray-50 hover:bg-emerald-50 p-1.5 rounded-lg border border-gray-100 flex justify-between items-center cursor-pointer transition">
                                    <span class="text-gray-600 text-[10px]">{{ reg.name }}</span>
                                    <span class="font-bold text-amber-700 text-[11px]">{{ reg.avg }}°C</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    <!-- SECTION 3: WATER DATA (THAIWATER API REALTIME) -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold text-gray-700 text-sm flex items-center space-x-1">
                                <span>💧 ข้อมูลปริมาณน้ำในเขื่อน</span>
                            </h3>
                            <span class="text-[10px] text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full font-medium">สสน.
                                ({{ waterReservoirData.length }} เขื่อน)</span>
                        </div>
                        <div class="space-y-3">
                            <!-- Water Reservoir Storage Total -->
                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-600">ปริมาณน้ำในเขื่อนรวมทั้งประเทศ</span>
                                    <span class="font-semibold text-emerald-600">{{ nationalWaterSummary.percent
                                        }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden mb-1.5">
                                    <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2 rounded-full transition-all duration-700"
                                        :style="{ width: Math.min(nationalWaterSummary.percent || 75, 100) + '%' }">
                                    </div>
                                </div>
                                <div class="flex justify-between text-[10px] text-gray-500">
                                    <span>น้ำใช้การได้: {{ nationalWaterSummary.totalStorage ?
                                        (nationalWaterSummary.totalStorage).toLocaleString() : '0' }} ล้าน ลบ.ม.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- รายการเขื่อนทั้งประเทศที่ดึงจาก API (คลิกที่เขื่อนเพื่อซูมพิกัดบนแผนที่) -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-700">รายชื่อเขื่อน ({{ waterReservoirData.length
                                }})</span>
                            <span class="text-[10px] text-gray-400">คลิกเขื่อนเพื่อดูพิกัด</span>
                        </div>
                        <div class="space-y-2 max-h-[350px] overflow-y-auto scrollbar-thin pr-1.5 overscroll-contain">
                            <div v-for="dam in waterReservoirData" :key="dam.id || dam.name" @click="flyToDam(dam)"
                                class="bg-gray-50 hover:bg-emerald-50/80 p-3 rounded-xl border border-gray-100 hover:border-emerald-200 cursor-pointer transition">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-700 font-medium truncate mr-1">{{ dam.name }} (จ.{{
                                        dam.province }})</span>
                                    <span :class="[
                                        'font-semibold shrink-0',
                                        dam.status === 'critical' ? 'text-red-600' : dam.status === 'warning' ? 'text-amber-600' : 'text-emerald-600'
                                    ]">{{ dam.percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden mb-1">
                                    <div :class="[
                                        'h-2 rounded-full transition-all duration-500',
                                        dam.status === 'critical' ? 'bg-red-500' : dam.status === 'warning' ? 'bg-amber-500' : 'bg-emerald-500'
                                    ]" :style="{ width: Math.min(dam.percentage, 100) + '%' }"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-gray-400">
                                    <span>{{ dam.currentVolume.toLocaleString() }} / {{ dam.maxCapacity.toLocaleString()
                                        }} ล้าน ลบ.ม.</span>
                                    <span>{{ dam.statusText }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </aside>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import api from '@/services/api';
import provinceBoundaries from '@/assets/th_adm1.json';

// ควบคุมการ เปิด/ปิด แผงข้อมูลด้านล่างในโหมดมือถือ (Mobile Drawer Toggle)
const isSidebarOpen = ref(false);

// สถานะการเปิดแสดงของแต่ละเลเยอร์ (checkbox) - แสดงหลายเลเยอร์พร้อมกันได้
const enabledLayers = reactive({
    riskmap: true,
    dams: true,
    temperature: true,
});

const searchQuery = ref('');
const isLoading = ref(false);

const mapContainer = ref(null);
let map = null;

// Leaflet Layers
let riskmapLayer = null;
let damsLayer = new L.LayerGroup();
let tempLayer = new L.LayerGroup();
let disasterLayer = new L.LayerGroup();

// Marker / Layer references for fly-to and open popup
const damMarkersMap = new Map();
const stationMarkersMap = new Map();
const provinceLayersMap = new Map();
const selectedProvinceCode = ref(null);

// ข้อมูลดิบจาก API
const rawDams = ref([]);
const rawStations = ref([]);
const rawRainAverage = ref([]);

// ช่วงเวลาสำหรับ RiskMap
const rainAveragePeriods = [
    { key: 'avg_rain_24h', label: '24 ชั่วโมง', shortLabel: '24 ชม.' },
    { key: 'avg_yesterday', label: 'เมื่อวาน', shortLabel: 'เมื่อวาน' },
    { key: 'avg_3d', label: 'สะสม 3 วัน', shortLabel: '3 วัน' },
    { key: 'avg_7d', label: 'สะสม 7 วัน', shortLabel: '7 วัน' },
];
const selectedRainPeriod = ref('avg_rain_24h');

const currentPeriodLabel = computed(() => {
    return rainAveragePeriods.find(p => p.key === selectedRainPeriod.value)?.label || '24 ชั่วโมง';
});
const currentPeriodShortLabel = computed(() => {
    return rainAveragePeriods.find(p => p.key === selectedRainPeriod.value)?.shortLabel || '24 ชม.';
});

const setRainPeriod = (periodKey) => {
    selectedRainPeriod.value = periodKey;
    if (riskmapLayer) {
        riskmapLayer.setStyle(getBoundaryStyle);
    }
};

// ระดับและสีของปริมาณน้ำฝน RiskMap
const rainAvgLevels = [
    { min: 35, color: '#dc3545', label: '≥ 35 มม. (ตกหนักมาก)' },
    { min: 20, color: '#fd7e14', label: '20-35 มม. (ตกหนัก)' },
    { min: 10, color: '#ffc107', label: '10-20 มม. (ปานกลาง)' },
    { min: 0.1, color: '#198754', label: '0.1-10 มม. (เล็กน้อย)' },
    { min: 0, color: '#0dcaf0', label: '0 มม. (ไม่มีฝน)' },
];

const getRainAvgColor = (val) => {
    const num = Number(val);
    if (val == null || isNaN(num)) return '#9ca3af';
    for (const l of rainAvgLevels) {
        if (num >= l.min) return l.color;
    }
    return '#0dcaf0';
};

const normalizeAdminCode = (code) => String(code ?? '').replace(/^TH/i, '').padStart(2, '0');

// จัดเก็บข้อมูลฝนเฉลี่ยเป็น Map โดยใช้รหัสจังหวัดเป็น key
const rainAverageMap = computed(() => {
    const mapObj = new Map();
    rawRainAverage.value.forEach(record => {
        if (record && record.admin_code != null) {
            mapObj.set(normalizeAdminCode(record.admin_code), record);
        }
    });
    return mapObj;
});

// รายชื่อจังหวัดพร้อมสถิติฝนจาก GeoJSON
const provinceList = computed(() => {
    if (!provinceBoundaries || !provinceBoundaries.features) return [];
    return provinceBoundaries.features.map(f => {
        const pcode = normalizeAdminCode(f.properties.ADM1_PCODE);
        const name = f.properties.ADM1_TH || f.properties.ADM1_EN || '';
        const rain = rainAverageMap.value.get(pcode) || {};

        // คำนวณจุดกึ่งกลางคร่าวๆ สำหรับบินไปหาเมื่อคลิก
        let center = [13.7367, 100.5231];
        if (f.geometry && f.geometry.coordinates) {
            let coords = [];
            if (f.geometry.type === 'Polygon') {
                coords = f.geometry.coordinates[0];
            } else if (f.geometry.type === 'MultiPolygon' && f.geometry.coordinates[0]) {
                coords = f.geometry.coordinates[0][0];
            }
            if (coords && coords.length > 0) {
                let sumLat = 0, sumLon = 0;
                coords.forEach(pt => {
                    sumLon += pt[0];
                    sumLat += pt[1];
                });
                center = [sumLat / coords.length, sumLon / coords.length];
            }
        }

        return {
            code: pcode,
            pcodeRaw: f.properties.ADM1_PCODE,
            name,
            nameEn: f.properties.ADM1_EN,
            center,
            station_count: rain.station_count ?? 0,
            avg_rain_24h: rain.avg_rain_24h != null ? Number(rain.avg_rain_24h) : null,
            avg_yesterday: rain.avg_yesterday != null ? Number(rain.avg_yesterday) : null,
            avg_3d: rain.avg_3d != null ? Number(rain.avg_3d) : null,
            avg_7d: rain.avg_7d != null ? Number(rain.avg_7d) : null,
        };
    });
});

// สรุปข้อมูล RiskMap ภาพรวมระดับประเทศ
const nationalRainSummary = computed(() => {
    const list = provinceList.value;
    if (!list.length) {
        return {
            avgRain: '0.0',
            maxRain: '0.0',
            maxProvince: '-',
            rainingProvincesCount: 0,
            totalStations: 0,
            topProvinces: []
        };
    }

    const currentKey = selectedRainPeriod.value;
    const withData = list.filter(p => p[currentKey] != null);
    const values = withData.map(p => Number(p[currentKey]) || 0);
    const avg = values.length ? (values.reduce((a, b) => a + b, 0) / values.length).toFixed(1) : '0.0';

    const sorted = [...list].sort((a, b) => (Number(b[currentKey]) || 0) - (Number(a[currentKey]) || 0));
    const maxItem = sorted[0];
    const rainingCount = list.filter(p => (Number(p[currentKey]) || 0) > 0).length;
    const totalStations = list.reduce((sum, p) => sum + (p.station_count || 0), 0);

    return {
        avgRain: avg,
        maxRain: maxItem && maxItem[currentKey] != null ? Number(maxItem[currentKey]).toFixed(1) : '0.0',
        maxProvince: maxItem?.name || '-',
        rainingProvincesCount: rainingCount,
        totalStations,
        topProvinces: sorted.slice(0, 5)
    };
});

// ข้อมูลปริมาณน้ำในอ่างเก็บน้ำ/เขื่อนหลัก (Reservoir Water Data)
const waterReservoirData = computed(() => {
    if (!rawDams.value.length) {
        return [
            { id: 1, name: 'เขื่อนภูมิพล', province: 'ตาก', currentVolume: 9850, maxCapacity: 13462, percentage: 73, status: 'normal', statusText: 'ปกติ', lat: 17.2436, lon: 98.9722 },
            { id: 2, name: 'เขื่อนสิริกิติ์', province: 'อุตรดิตถ์', currentVolume: 8210, maxCapacity: 9510, percentage: 86, status: 'warning', statusText: 'เฝ้าระวัง', lat: 17.7680, lon: 100.5549 },
            { id: 3, name: 'เขื่อนป่าสักชลสิทธิ์', province: 'ลพบุรี', currentVolume: 912, maxCapacity: 960, percentage: 95, status: 'critical', statusText: 'วิกฤต', lat: 14.8622, lon: 101.0772 }
        ];
    }

    return rawDams.value.map(item => {
        const d = item.dam || {};
        const geocode = item.geocode || {};
        const agency = item.agency || {};
        const currentVol = Number(item.dam_storage) || 0;
        const capacity = Number(d.normal_storage || d.max_storage) || 0;
        const pct = Number(item.dam_storage_percent) || (capacity ? Math.round((currentVol / capacity) * 100) : 0);

        let status = 'normal';
        let statusText = 'ปกติ';
        if (pct > 100) {
            status = 'critical';
            statusText = 'วิกฤต (ล้นเขื่อน)';
        } else if (pct >= 80) {
            status = 'warning';
            statusText = 'เฝ้าระวัง (น้ำมาก)';
        } else if (pct >= 50) {
            status = 'normal';
            statusText = 'ปกติ';
        } else if (pct >= 30) {
            status = 'low';
            statusText = 'น้ำน้อย';
        } else {
            status = 'critical';
            statusText = 'วิกฤต (น้ำน้อย)';
        }

        const rawName = d.dam_name?.th || d.dam_name?.en || 'เขื่อน';
        const formattedName = rawName.startsWith('เขื่อน') ? rawName : `เขื่อน${rawName}`;

        return {
            id: item.id || d.id,
            name: formattedName,
            province: geocode.province_name?.th || 'ไม่ระบุ',
            agency: agency.agency_shortname?.th || agency.agency_name?.th || 'RID',
            currentVolume: currentVol,
            maxCapacity: capacity,
            percentage: pct,
            inflow: item.dam_inflow,
            released: item.dam_released,
            date: item.dam_date,
            status,
            statusText,
            lat: d.dam_lat,
            lon: d.dam_long
        };
    }).sort((a, b) => b.percentage - a.percentage);
});

// สรุปปริมาณน้ำในเขื่อนรวมทั้งประเทศ
const nationalWaterSummary = computed(() => {
    const list = waterReservoirData.value;
    if (!list.length) return { totalStorage: 0, totalCapacity: 0, percent: 75, count: 0 };

    const totalStorage = list.reduce((sum, d) => sum + d.currentVolume, 0);
    const totalCapacity = list.reduce((sum, d) => sum + d.maxCapacity, 0);
    const percent = totalCapacity > 0 ? Math.round((totalStorage / totalCapacity) * 100) : 0;

    return {
        totalStorage,
        totalCapacity,
        percent,
        count: list.length
    };
});

// ข้อมูลสภาพอากาศและอุณหภูมิเฉลี่ยทั้งประเทศ
const nationalWeatherSummary = computed(() => {
    const stations = rawStations.value.filter(s => s.temperature != null && !isNaN(s.temperature));
    if (!stations.length) {
        return {
            avgTemp: '32',
            count: 0,
            regions: [
                { name: 'ภาคเหนือ', avg: '29.6' },
                { name: 'ภาคกลาง', avg: '32.5' },
                { name: 'ภาคตะวันออกเฉียงเหนือ', avg: '31.1' },
                { name: 'ภาคตะวันออก', avg: '31.1' },
                { name: 'ภาคใต้', avg: '30.9' }
            ]
        };
    }

    const temps = stations.map(s => Number(s.temperature));
    const avgTemp = (temps.reduce((a, b) => a + b, 0) / temps.length).toFixed(1);

    const regionMap = {};
    stations.forEach(s => {
        const reg = s.region_name_th || 'ไม่ระบุ';
        if (!regionMap[reg]) regionMap[reg] = [];
        regionMap[reg].push(Number(s.temperature));
    });

    const regions = Object.entries(regionMap).map(([name, tList]) => ({
        name,
        avg: (tList.reduce((a, b) => a + b, 0) / tList.length).toFixed(1),
        count: tList.length
    })).sort((a, b) => Number(b.avg) - Number(a.avg));

    return {
        avgTemp,
        count: stations.length,
        regions
    };
});

// การค้นหาอัตโนมัติ (Search Autocomplete)
const searchResults = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (q.length < 2) return [];

    const results = [];

    // ค้นหาจังหวัด (RiskMap)
    provinceList.value.forEach(p => {
        if (p.name.toLowerCase().includes(q) || (p.nameEn && p.nameEn.toLowerCase().includes(q))) {
            const rainVal = p[selectedRainPeriod.value];
            results.push({
                type: 'province',
                id: `prov-${p.code}`,
                title: `จ.${p.name}`,
                province: p.name,
                subtitle: `ฝนเฉลี่ยสะสม (${currentPeriodShortLabel.value}): ${rainVal != null ? Number(rainVal).toFixed(1) + ' มม.' : 'N/A'}`,
                lat: p.center[0],
                lon: p.center[1],
                code: p.code,
                valueText: `${rainVal != null ? Number(rainVal).toFixed(1) : 0} มม.`,
                badgeClass: 'bg-cyan-100 text-cyan-800'
            });
        }
    });

    // ค้นหาเขื่อน
    waterReservoirData.value.forEach(dam => {
        if (dam.name.toLowerCase().includes(q) || dam.province.toLowerCase().includes(q)) {
            results.push({
                type: 'dam',
                id: `dam-${dam.id}`,
                title: dam.name,
                province: dam.province,
                subtitle: `จ.${dam.province} (${dam.agency || 'RID'})`,
                lat: dam.lat,
                lon: dam.lon,
                valueText: `${dam.percentage}%`,
                badgeClass: dam.status === 'critical' ? 'bg-red-100 text-red-700' : dam.status === 'warning' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'
            });
        }
    });

    // ค้นหาสถานีตรวจวัดอุณหภูมิ
    rawStations.value.forEach(st => {
        const name = (st.station_name_th || '').toLowerCase();
        const prov = (st.province_name_th || '').toLowerCase();
        if (name.includes(q) || prov.includes(q)) {
            results.push({
                type: 'station',
                id: `st-${st.station_id}`,
                title: st.station_name_th,
                province: st.province_name_th,
                subtitle: `จ.${st.province_name_th || ''} (${st.region_name_th || ''})`,
                lat: st.station_lat,
                lon: st.station_lon,
                valueText: `${st.temperature}°C`,
                badgeClass: 'bg-amber-100 text-amber-800'
            });
        }
    });

    return results.slice(0, 8);
});

// สีตามระดับเปอร์เซ็นต์น้ำในเขื่อน
const getDamColor = (pct) => {
    if (pct > 100) return '#dc2626'; // แดง ล้นเขื่อน
    if (pct >= 80) return '#ea580c'; // ส้ม น้ำมาก
    if (pct >= 50) return '#16a34a'; // เขียว ปกติ
    if (pct >= 30) return '#0284c7'; // ฟ้า น้ำน้อย
    return '#2563eb'; // น้ำเงิน วิกฤตน้อย
};

// สีตามระดับอุณหภูมิ
const getTempColor = (temp) => {
    const t = Number(temp);
    if (isNaN(t)) return '#6b7280';
    if (t < 24) return '#3b82f6'; // เย็น
    if (t < 28) return '#10b981'; // สบาย
    if (t < 32) return '#eab308'; // ค่อนข้างร้อน
    if (t < 35) return '#f97316'; // ร้อน
    return '#dc2626'; // ร้อนจัด
};

// สไตล์ของขอบเขตจังหวัด RiskMap
const getBoundaryStyle = (feature) => {
    const provinceCode = normalizeAdminCode(feature.properties.ADM1_PCODE);
    const rainRecord = rainAverageMap.value.get(provinceCode);
    const val = rainRecord && rainRecord[selectedRainPeriod.value] != null ? Number(rainRecord[selectedRainPeriod.value]) : null;
    const isSelected = selectedProvinceCode.value === provinceCode;

    return {
        pane: 'riskmapPane',
        color: isSelected ? '#0f172a' : '#ffffff',
        weight: isSelected ? 2.5 : 1,
        fillColor: val == null ? '#9ca3af' : getRainAvgColor(val),
        fillOpacity: isSelected ? 0.85 : (val == null ? 0.25 : 0.65),
    };
};

// เนื้อหา Tooltip สำหรับจังหวัด
const getProvinceTooltipContent = (provinceCode, provinceName, feature) => {
    const rainRecord = rainAverageMap.value.get(provinceCode);
    const periodLabel = currentPeriodLabel.value;
    const val = rainRecord && rainRecord[selectedRainPeriod.value] != null 
        ? Number(rainRecord[selectedRainPeriod.value]).toFixed(1) 
        : null;
    const stations = rainRecord?.station_count ?? '-';
    const color = val != null ? getRainAvgColor(Number(val)) : '#9ca3af';

    return `
        <div style="font-family: sans-serif; min-width: 170px; font-size: 12px; line-height: 1.5; padding: 2px;">
            <div style="font-weight: 700; font-size: 13px; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; margin-bottom: 4px;">
                จ.${provinceName} <span style="font-size: 10px; color: #6b7280; font-weight: normal;">(${feature.properties.ADM1_PCODE || ''})</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 3px;">
                <span style="color: #4b5563;">ฝนเฉลี่ยสะสม (${periodLabel}):</span>
                <strong style="color: ${color}; font-size: 14px;">${val != null ? val + ' มม.' : 'ไม่มีข้อมูล'}</strong>
            </div>
            <div style="font-size: 11px; color: #6b7280;">
                จำนวนสถานีตรวจวัด: <b>${stations}</b> แห่ง
            </div>
            <div style="font-size: 10px; color: #9ca3af; margin-top: 4px; border-top: 1px solid #f3f4f6; padding-top: 3px;">
                แหล่งข้อมูล: riskmap.doae.go.th
            </div>
        </div>
    `;
};

// ผูกเหตุการณ์กับแต่ละขอบเขตจังหวัด
const bindBoundaryEvents = (feature, layer) => {
    const provinceCode = normalizeAdminCode(feature.properties.ADM1_PCODE);
    const provinceName = feature.properties.ADM1_TH || feature.properties.ADM1_EN || 'ไม่ระบุ';

    provinceLayersMap.set(provinceCode, layer);

    layer.bindTooltip(() => getProvinceTooltipContent(provinceCode, provinceName, feature), {
        sticky: true,
        direction: 'auto',
        className: 'riskmap-province-tooltip'
    });

    layer.on({
        mouseover: (e) => {
            const l = e.target;
            l.setStyle({
                weight: 2.5,
                color: '#1f5c3f',
                fillOpacity: 0.85
            });
            l.bringToFront();
        },
        mouseout: (e) => {
            if (riskmapLayer) {
                riskmapLayer.resetStyle(e.target);
            }
        },
        click: (e) => {
            selectedProvinceCode.value = provinceCode;
            if (riskmapLayer) {
                riskmapLayer.setStyle(getBoundaryStyle);
            }
            if (map && e.target.getBounds().isValid()) {
                map.fitBounds(e.target.getBounds(), { maxZoom: 9, padding: [25, 25] });
            }
        }
    });
};

// วาดหมุดเขื่อนลงบนแผนที่
const renderDamMarkers = () => {
    damsLayer.clearLayers();
    damMarkersMap.clear();

    waterReservoirData.value.forEach(dam => {
        if (dam.lat == null || dam.lon == null) return;
        const color = getDamColor(dam.percentage);
        const marker = L.circleMarker([dam.lat, dam.lon], {
            radius: 8,
            fillColor: color,
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        });

        const popupContent = `
            <div style="font-family: sans-serif; min-width: 190px; font-size: 12px; line-height: 1.5;">
                <div style="border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-bottom: 6px;">
                    <strong style="font-size: 13px; color: #111827;">${dam.name}</strong>
                    <div style="font-size: 11px; color: #6b7280;">จ.${dam.province} (${dam.agency || 'RID'})</div>
                </div>
                <div style="margin-bottom: 6px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                        <span>ปริมาณน้ำกักเก็บ:</span>
                        <strong style="color: ${color};">${dam.percentage}%</strong>
                    </div>
                    <div style="width: 100%; background: #e5e7eb; height: 6px; border-radius: 9999px; overflow: hidden;">
                        <div style="width: ${Math.min(dam.percentage, 100)}%; height: 100%; background: ${color};"></div>
                    </div>
                </div>
                <div style="font-size: 11px; color: #4b5563;">
                    <div>ปริมาณน้ำ: <b>${dam.currentVolume.toLocaleString()}</b> ล้าน ลบ.ม.</div>
                    <div>ความจุปกติ: ${dam.maxCapacity.toLocaleString()} ล้าน ลบ.ม.</div>
                    ${dam.inflow != null ? `<div>น้ำไหลเข้า: ${dam.inflow} ล้าน ลบ.ม./วัน</div>` : ''}
                    ${dam.released != null ? `<div>น้ำระบาย: ${dam.released} ล้าน ลบ.ม./วัน</div>` : ''}
                </div>
                <div style="font-size: 10px; color: #9ca3af; margin-top: 6px; border-top: 1px solid #f3f4f6; padding-top: 4px;">
                    วันที่ข้อมูล: ${dam.date || 'ล่าสุด'}
                </div>
            </div>
        `;
        marker.bindPopup(popupContent);
        marker.addTo(damsLayer);
        damMarkersMap.set(dam.id, marker);
    });
};

// วาดหมุดสถานีตรวจวัดอุณหภูมิลงบนแผนที่
const renderTemperatureMarkers = () => {
    tempLayer.clearLayers();
    stationMarkersMap.clear();

    rawStations.value.forEach(st => {
        if (st.station_lat == null || st.station_lon == null || st.temperature == null) return;
        const color = getTempColor(st.temperature);

        const tempBadge = L.divIcon({
            className: 'tmd-temp-badge',
            html: `
                <div style="
                    background: ${color};
                    color: #fff;
                    font-size: 10px;
                    font-weight: 700;
                    padding: 2px 5px;
                    border-radius: 9999px;
                    border: 1.5px solid #fff;
                    box-shadow: 0 1px 4px rgba(0,0,0,0.3);
                    white-space: nowrap;
                    transform: translate(-50%, -50%);
                    cursor: pointer;
                ">
                    ${st.temperature}°
                </div>
            `,
            iconSize: [0, 0]
        });

        const marker = L.marker([st.station_lat, st.station_lon], { icon: tempBadge });

        const minT = st.temperature_min_today != null ? `${st.temperature_min_today}°C` : '-';
        const maxT = st.temperature_max_today != null ? `${st.temperature_max_today}°C` : '-';
        const humidity = st.humidity != null ? `${st.humidity}%` : '-';
        const wind = st.windspeed != null ? `${st.windspeed} กม./ชม.` : '-';

        const popupContent = `
            <div style="font-family: sans-serif; min-width: 200px; font-size: 12px; line-height: 1.5;">
                <div style="border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-bottom: 6px;">
                    <strong style="font-size: 13px; color: #111827;">${st.station_name_th || 'สถานีตรวจวัด'}</strong>
                    <div style="font-size: 11px; color: #6b7280;">จ.${st.province_name_th || '-'} (${st.region_name_th || '-'})</div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 6px;">
                    <span>อุณหภูมิปัจจุบัน:</span>
                    <strong style="font-size: 16px; color: ${color};">${st.temperature}°C</strong>
                </div>
                <div style="font-size: 11px; color: #4b5563; background: #f9fafb; padding: 6px; border-radius: 6px; margin-bottom: 4px;">
                    <div>ต่ำสุด - สูงสุด วันนี้: <b>${minT} - ${maxT}</b></div>
                    <div>ความชื้นสัมพัทธ์: <b>${humidity}</b></div>
                    <div>ความเร็วลม: ${wind}</div>
                </div>
                <div style="font-size: 10px; color: #9ca3af;">
                    เวลาตรวจวัด: ${st.datetime_utc7 || '-'}
                </div>
            </div>
        `;
        marker.bindPopup(popupContent);
        marker.addTo(tempLayer);
        stationMarkersMap.set(st.station_id, marker);
    });
};

// ซิงค์เลเยอร์กับแผนที่ตามสถานะ checkbox (เปิด/ปิด พร้อมกันได้หลายเลเยอร์)
const syncMapLayers = () => {
    if (!map) return;
    const layerMap = {
        riskmap: riskmapLayer,
        dams: damsLayer,
        temperature: tempLayer,
        disaster: disasterLayer
    };
    for (const [name, layer] of Object.entries(layerMap)) {
        if (!layer) continue;
        const enabled = enabledLayers[name];
        if (enabled && !map.hasLayer(layer)) map.addLayer(layer);
        if (!enabled && map.hasLayer(layer)) map.removeLayer(layer);
    }
};

// เปิด/ปิด ทุกเลเยอร์พร้อมกัน (master toggle 'ทั้งหมด')
const allLayersOn = computed(() => Object.values(enabledLayers).every(v => v));
const toggleAll = () => {
    const turnOn = !allLayersOn.value;
    for (const key in enabledLayers) enabledLayers[key] = turnOn;
    syncMapLayers();
};

// รีเซ็ตมุมมองแผนที่
const resetMapView = () => {
    if (!map) return;
    selectedProvinceCode.value = null;
    if (riskmapLayer) {
        riskmapLayer.setStyle(getBoundaryStyle);
    }
    map.flyTo([13.7367, 100.5231], 6, { duration: 1 });
};

// บินไปยังเขื่อน
const flyToDam = (dam) => {
    if (!map || dam.lat == null || dam.lon == null) return;
    map.flyTo([dam.lat, dam.lon], 10, { duration: 1.2 });
    setTimeout(() => {
        const marker = damMarkersMap.get(dam.id);
        if (marker) marker.openPopup();
    }, 1300);
};

// บินไปยังจังหวัด (RiskMap)
const flyToProvince = (prov) => {
    if (!map || !prov) return;
    selectedProvinceCode.value = prov.code;
    if (riskmapLayer) {
        riskmapLayer.setStyle(getBoundaryStyle);
    }

    const layer = provinceLayersMap.get(prov.code);
    if (layer && layer.getBounds && layer.getBounds().isValid()) {
        map.fitBounds(layer.getBounds(), { maxZoom: 9, padding: [30, 30] });
        setTimeout(() => {
            layer.openTooltip();
        }, 600);
    } else if (prov.center) {
        map.flyTo(prov.center, 8, { duration: 1.2 });
    }
};

// บินไปยังผลการค้นหา
const flyToItem = (item) => {
    if (!map || item.lat == null || item.lon == null) return;
    searchQuery.value = '';

    if (item.type === 'province') {
        const prov = provinceList.value.find(p => p.code === item.code);
        if (prov) {
            flyToProvince(prov);
            return;
        }
    }

    map.flyTo([item.lat, item.lon], 10, { duration: 1.2 });

    setTimeout(() => {
        if (item.type === 'dam') {
            const rawId = item.id.replace('dam-', '');
            const marker = damMarkersMap.get(Number(rawId)) || damMarkersMap.get(rawId);
            if (marker) marker.openPopup();
        } else if (item.type === 'station') {
            const rawId = item.id.replace('st-', '');
            const marker = stationMarkersMap.get(Number(rawId)) || stationMarkersMap.get(rawId);
            if (marker) marker.openPopup();
        }
    }, 1300);
};

// ซูมไปยังภาค
const regionCoordinates = {
    'ภาคเหนือ': { center: [18.7883, 98.9853], zoom: 7 },
    'ภาคตะวันออกเฉียงเหนือ': { center: [16.0, 103.2], zoom: 7 },
    'ภาคกลาง': { center: [14.8, 100.5], zoom: 7 },
    'ภาคตะวันออก': { center: [13.2, 101.5], zoom: 7 },
    'ภาคใต้ฝั่งตะวันออก': { center: [8.5, 99.5], zoom: 7 },
    'ภาคใต้ฝั่งตะวันตก': { center: [8.5, 99.5], zoom: 7 },
};

const zoomToRegion = (regionName) => {
    const target = regionCoordinates[regionName];
    if (target && map) {
        map.flyTo(target.center, target.zoom, { duration: 1.2 });
    }
};

// ดึงข้อมูล API สำหรับเขื่อน, อุณหภูมิ และ RiskMap
const loadApiData = async () => {
    isLoading.value = true;
    try {
        const [damsRes, stationsRes, rainAvgRes] = await Promise.allSettled([
            api.getDamWater(),
            api.getTemperatureStations(),
            api.getRainAverage('p')
        ]);

        if (damsRes.status === 'fulfilled' && Array.isArray(damsRes.value)) {
            rawDams.value = damsRes.value;
            renderDamMarkers();
        }

        if (stationsRes.status === 'fulfilled' && Array.isArray(stationsRes.value)) {
            rawStations.value = stationsRes.value;
            renderTemperatureMarkers();
        }

        if (rainAvgRes.status === 'fulfilled') {
            const val = rainAvgRes.value;
            const data = Array.isArray(val) ? val : (val?.data || []);
            rawRainAverage.value = Array.isArray(data) ? data : [];
            if (riskmapLayer) {
                riskmapLayer.setStyle(getBoundaryStyle);
            }
        }
    } catch (e) {
        console.error('Failed to load map API data:', e);
    } finally {
        isLoading.value = false;
    }
};

const handleResize = () => {
    if (map) {
        map.invalidateSize();
    }
};

// เริ่มต้นวาดแผนที่เมื่อคอมโพเนนต์ถูกโหลดเข้าสู่หน้าจอ (onMounted)
onMounted(() => {
    // 1. แก้ไขปัญหา default icon ของ Leaflet
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    });

    // 2. สร้างตัวแผนที่และตั้งค่าพิกัดเริ่มต้นไปที่ใจกลางประเทศไทย
    map = L.map(mapContainer.value).setView([13.7367, 100.5231], 6);

    // 3. ใช้แผนที่ฐาน (TileLayer) โทนสีขาวสว่าง สะอาดตา
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 20
    }).addTo(map);

    // 4. สร้าง Pane สำหรับ RiskMap Polygons (z-index 350 ต่ำกว่า Marker ป้องกันการบังหมุด)
    if (!map.getPane('riskmapPane')) {
        map.createPane('riskmapPane');
        map.getPane('riskmapPane').style.zIndex = 350;
    }

    // 5. สร้าง Layer ขอบเขตจังหวัด RiskMap
    riskmapLayer = L.geoJSON(provinceBoundaries, {
        pane: 'riskmapPane',
        style: getBoundaryStyle,
        onEachFeature: bindBoundaryEvents
    });

    // เพิ่มเลเยอร์เข้าสู่แผนที่
    riskmapLayer.addTo(map);
    damsLayer.addTo(map);
    tempLayer.addTo(map);
    disasterLayer.addTo(map);

    // ตรวจสอบและรีเฟรชขนาดแผนที่ให้เต็มพื้นที่
    setTimeout(() => {
        if (map) map.invalidateSize();
    }, 250);

    window.addEventListener('resize', handleResize);

    // โหลดข้อมูล API แบบเรียลไทม์
    loadApiData();
});

// ล้างหน่วยความจำแผนที่เมื่อผู้ใช้ออกจากหน้าเพจ (ป้องกัน Memory Leak)
onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    if (map) {
        map.remove();
        map = null;
    }
    damMarkersMap.clear();
    stationMarkersMap.clear();
    provinceLayersMap.clear();
    riskmapLayer = null;
});
</script>

<style scoped>
#map-container {
    width: 100% !important;
    height: 100% !important;
}

:deep(.riskmap-province-tooltip) {
    background-color: rgba(255, 255, 255, 0.95);
    border: 1px solid #10b981;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 6px 10px;
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
