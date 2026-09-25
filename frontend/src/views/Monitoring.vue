<script setup>
const theme = {
  icon: '📊',
  title: 'แดชบอร์ดติดตามสถานการณ์และการช่วยเหลือ',
  subtitle: 'Monitoring & Assistance',
  color: '#2e7d4f',
}

// Mockup data (ตัวอย่างข้อมูลจำลอง)
const assistance = [
  { province: 'นครราชสีมา', requested: 1280, serviced: 1120, rate: 88, status: 'ดำเนินการ', statusClass: 'ok' },
  { province: 'สุรินทร์', requested: 954, serviced: 760, rate: 80, status: 'ดำเนินการ', statusClass: 'ok' },
  { province: 'บุรีรัมย์', requested: 810, serviced: 540, rate: 67, status: 'ระหว่างช่วยเหลือ', statusClass: 'watch' },
  { province: 'อุบลราชธานี', requested: 1503, serviced: 620, rate: 41, status: 'รอลงพื้นที่', statusClass: 'watch' },
  { province: 'ศรีสะเกษ', requested: 605, serviced: 190, rate: 31, status: 'เร่งดำเนินการ', statusClass: 'critical' },
]

const progress = [
  { activity: 'แจกจ่ายเมล็ดพันธุ์', target: 1200, done: 965, rate: 80 },
  { activity: 'แจกจ่ายปัจจัยการผลิต', target: 850, done: 620, rate: 73 },
  { activity: 'ฟื้นฟูพื้นที่เพาะปลูก', target: 2000, done: 1450, rate: 73 },
  { activity: 'ช่วยเหลือเงินชดเชย', target: 500, done: 310, rate: 62 },
]

const farmers = { total: 38420, registered: 29105, pending: 9315, areas: 6 }

const villageAssist = [
  { village: 'ต.หนองบัว', district: 'อ.ปักธงชัย', incident: 'อุทกภัย', households: 180, status: 'ช่วยเหลือแล้ว', statusClass: 'ok' },
  { village: 'ต.คูเมือง', district: 'อ.ศีขรภูมิ', incident: 'พายุฝน', households: 240, status: 'รอลงพื้นที่', statusClass: 'watch' },
  { village: 'ต.ไพรขลา', district: 'อ.เฉลิมพระเกียรติ', incident: 'น้ำท่วมฉับพลัน', households: 96, status: 'ขอรับการสนับสนุน', statusClass: 'critical' },
]
</script>
<template>
  <section class="hero flex items-center gap-[14px] text-white rounded-[16px] p-[18px_20px] shadow-card" :style="{ background: theme.color }">
          <span class="text-3xl">{{ theme.icon }}</span>
          <div>
            <h2 class="text-xl">{{ theme.title }}</h2>
            <p class="mt-0.5 opacity-[0.85] text-[13px]">{{ theme.subtitle }}</p>
          </div>
        </section>

        <div class="grid grid-cols-4 gap-[14px] max-[900px]:grid-cols-2">
          <div class="card p-4 flex flex-col gap-1">
            <span class="font-display text-[26px] font-bold text-pdm-green">{{ farmers.total.toLocaleString() }}</span>
            <span class="text-xs text-muted">เกษตรกรทั้งหมด (ทะเบียน)</span>
          </div>
          <div class="card p-4 flex flex-col gap-1">
            <span class="font-display text-[26px] font-bold text-pdm-green">{{ farmers.registered.toLocaleString() }}</span>
            <span class="text-xs text-muted">ขึ้นทะเบียนแล้ว</span>
          </div>
          <div class="card p-4 flex flex-col gap-1">
            <span class="font-display text-[26px] font-bold text-pdm-green">{{ farmers.pending.toLocaleString() }}</span>
            <span class="text-xs text-muted">รอขึ้นทะเบียน</span>
          </div>
          <div class="card p-4 flex flex-col gap-1">
            <span class="font-display text-[26px] font-bold text-pdm-green">{{ farmers.areas }}</span>
            <span class="text-xs text-muted">จังหวัดเป้าหมาย</span>
          </div>
        </div>

        <section class="card pb-1.5">
          <div class="font-bold text-[14px] p-[14px_16px_8px]" :style="{ color: theme.color }">ติดตามการช่วยเหลือรายจังหวัด</div>
          <table class="w-full border-collapse text-[13px]">
            <thead><tr><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">จังหวัด</th><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">คำขอ (ราย)</th><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">ช่วยเหลือแล้ว</th><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">ความคืบหน้า</th><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">สถานะ</th></tr></thead>
            <tbody>
              <tr v-for="a in assistance" :key="a.province">
                <td class="text-left p-[9px_12px] border-b border-edge">{{ a.province }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge">{{ a.requested.toLocaleString() }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge">{{ a.serviced.toLocaleString() }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge">
                  <div class="progress flex items-center gap-2 min-w-[120px]"><span class="h-2 rounded-full" :class="'bg-' + a.statusClass" :style="{ width: a.rate + '%' }"></span><em class="font-normal text-[11.5px] text-muted">{{ a.rate }}%</em></div>
                </td>
                <td class="text-left p-[9px_12px] border-b border-edge"><span class="text-white rounded-full px-[10px] py-0.5 text-[11.5px] whitespace-nowrap" :class="'bg-' + a.statusClass">{{ a.status }}</span></td>
              </tr>
            </tbody>
          </table>
        </section>

        <section class="card pb-1.5">
          <div class="font-bold text-[14px] p-[14px_16px_8px]" :style="{ color: theme.color }">ความคืบหน้าการช่วยเหลือ (รายกิจกรรม)</div>
          <table class="w-full border-collapse text-[13px]">
            <thead><tr><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">กิจกรรม</th><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">เป้าหมาย</th><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">ดำเนินการแล้ว</th><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">ความคืบหน้า</th></tr></thead>
            <tbody>
              <tr v-for="p in progress" :key="p.activity">
                <td class="text-left p-[9px_12px] border-b border-edge">{{ p.activity }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge">{{ p.target.toLocaleString() }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge">{{ p.done.toLocaleString() }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge">
                  <div class="progress flex items-center gap-2 min-w-[120px]"><span class="h-2 rounded-full bg-ok" :style="{ width: p.rate + '%' }"></span><em class="font-normal text-[11.5px] text-muted">{{ p.rate }}%</em></div>
                </td>
              </tr>
            </tbody>
          </table>
        </section>

        <section class="card pb-1.5">
          <div class="font-bold text-[14px] p-[14px_16px_8px]" :style="{ color: theme.color }">รายงานเหตุเบื้องต้นและการลงพื้นที่</div>
          <table class="w-full border-collapse text-[13px]">
            <thead><tr><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">ตำบล</th><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">อำเภอ</th><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">เหตุการณ์</th><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">ครัวเรือน</th><th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">สถานะ</th></tr></thead>
            <tbody>
              <tr v-for="v in villageAssist" :key="v.village">
                <td class="text-left p-[9px_12px] border-b border-edge">{{ v.village }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge">{{ v.district }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge">{{ v.incident }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge">{{ v.households }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge"><span class="text-white rounded-full px-[10px] py-0.5 text-[11.5px] whitespace-nowrap" :class="'bg-' + v.statusClass">{{ v.status }}</span></td>
              </tr>
            </tbody>
          </table>
        </section>
</template>