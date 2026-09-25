<script setup>
const theme = {
  icon: '🧑‍💼',
  title: 'ระบบสนับสนุนเจ้าหน้าที่',
  subtitle: 'Operation Support',
  color: '#6f4fa3',
}

// Mockup data (ตัวอย่างข้อมูลจำลอง)
const stock = [
  { item: 'ข้าวพันธุ์ กข.7', unit: 'กระสอบ', inStock: 320, target: 500, rate: 64 },
  { item: 'ข้าวโพดเลี้ยงสัตว์', unit: 'กระสอบ', inStock: 180, target: 300, rate: 60 },
  { item: 'ถั่วเขียว', unit: 'กระสอบ', inStock: 95, target: 150, rate: 63 },
  { item: 'ปุ๋ยชีวภาพ', unit: 'ถัง', inStock: 210, target: 250, rate: 84 },
]

const bioproducts = [
  { item: 'ไตรโคเดอร์มา', unit: 'ขวด', inStock: 1400, target: 2000, rate: 70 },
  { item: 'บิวเวอร์เรีย', unit: 'ขวด', inStock: 860, target: 1500, rate: 57 },
  { item: 'เมธาไรเซียม', unit: 'ขวด', inStock: 520, target: 1000, rate: 52 },
  { item: 'บีที (Bacillus)', unit: 'ขวด', inStock: 1100, target: 1600, rate: 69 },
]

const operations = [
  { activity: 'ลงพื้นที่สำรวจความเสียหาย', officer: 'นายสมชาย แก้วใส', date: '21 ก.ย. 2569', district: 'อ.ปักธงชัย', status: 'เสร็จสิ้น', statusClass: 'ok' },
  { activity: 'ติดตามการช่วยเหลือ', officer: 'น.ส.วิภาดา เรืองศรี', date: '22 ก.ย. 2569', district: 'อ.ศีขรภูมิ', status: 'ดำเนินการ', statusClass: 'watch' },
  { activity: 'แจกจ่ายปัจจัยการผลิต', officer: 'นายอนุชา ทองคำ', date: '23 ก.ย. 2569', district: 'อ.ประโคนชัย', status: 'รออนุมัติ', statusClass: 'critical' },
]

const allocations = [
  { item: 'ข้าวพันธุ์ กข.7', from: 'คลังกลาง จ.นครราชสีมา', to: 'จุดจ่าย อ.ศีขรภูมิ', qty: 120, status: 'ขนส่งแล้ว', statusClass: 'ok' },
  { item: 'ปุ๋ยชีวภาพ', from: 'คลังกลาง จ.นครราชสีมา', to: 'จุดจ่าย อ.ประโคนชัย', qty: 60, status: 'รอขนส่ง', statusClass: 'watch' },
  { item: 'ไตรโคเดอร์มา', from: 'คลัง จ.บุรีรัมย์', to: 'จุดจ่าย อ.ปักธงชัย', qty: 400, status: 'เตรียมเอกสาร', statusClass: 'watch' },
]

// helper for progress bar color
function barClass(rate) {
  if (rate >= 70) return 'bg-ok'
  if (rate >= 55) return 'bg-watch'
  return 'bg-critical'
}
</script>

<template>
  <section class="hero flex items-center gap-[14px] text-white rounded-[16px] p-[18px_20px] shadow-card" :style="{ background: theme.color }">
          <span class="text-3xl">{{ theme.icon }}</span>
          <div>
            <h2 class="text-xl">{{ theme.title }}</h2>
            <p class="mt-0.5 opacity-[0.85] text-[13px]">{{ theme.subtitle }}</p>
          </div>
        </section>

        <div class="grid grid-cols-2 gap-[14px] max-[1280px]:grid-cols-1">
          <section class="card pb-1.5">
            <div class="font-bold text-[14px] p-[14px_16px_8px]" :style="{ color: theme.color }">🚚 ปัจจัยพันธุ์พืชสำรอง</div>
            <!-- <table class="w-full border-collapse text-[13px]">
              <thead>
                <tr>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">รายการ</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">คงคลัง</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">เป้าหมาย</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">ความพร้อม</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="s in stock" :key="s.item">
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ s.item }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ s.inStock }} {{ s.unit }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ s.target }} {{ s.unit }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">
                    <div class="progress flex items-center gap-2 min-w-[110px]"><span class="h-2 rounded-full" :class="barClass(s.rate)"
                        :style="{ width: s.rate + '%' }"></span><em class="font-normal text-[11.5px] text-muted">{{ s.rate }}%</em></div>
                  </td>
                </tr>
              </tbody>
            </table> -->
          </section>

          <section class="card pb-1.5">
            <div class="font-bold text-[14px] p-[14px_16px_8px]" :style="{ color: theme.color }">🚚 สต็อกชีวภัณฑ์</div>
            <!-- <table class="w-full border-collapse text-[13px]">
              <thead>
                <tr>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">รายการ</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">คงคลัง</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">เป้าหมาย</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">ความพร้อม</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="b in bioproducts" :key="b.item">
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ b.item }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ b.inStock.toLocaleString() }} {{ b.unit }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ b.target.toLocaleString() }} {{ b.unit }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">
                    <div class="progress flex items-center gap-2 min-w-[110px]"><span class="h-2 rounded-full" :class="barClass(b.rate)"
                        :style="{ width: b.rate + '%' }"></span><em class="font-normal text-[11.5px] text-muted">{{ b.rate }}%</em></div>
                  </td>
                </tr>
              </tbody>
            </table> -->
          </section>
        </div>

        <div class="grid grid-cols-2 gap-[14px] max-[1280px]:grid-cols-1">
          <section class="card pb-1.5">
            <div class="font-bold text-[14px] p-[14px_16px_8px]" :style="{ color: theme.color }">🧰 การรายงานเหตุเบื้องต้นจาก อกม.</div>
            <!-- <table class="w-full border-collapse text-[13px]">
              <thead>
                <tr>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">รายการ</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">จาก</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">ไปยัง</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">จำนวน</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">สถานะ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in allocations" :key="p.item + p.to">
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ p.item }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ p.from }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ p.to }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ p.qty }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge"><span class="text-white rounded-full px-[10px] py-0.5 text-[11.5px] whitespace-nowrap" :class="'bg-' + p.statusClass">{{ p.status }}</span></td>
                </tr>
              </tbody>
            </table> -->
          </section>

          <section class="card pb-1.5">
            <div class="font-bold text-[14px] p-[14px_16px_8px]" :style="{ color: theme.color }">📋 AI chatbot</div>
            <!-- <table class="w-full border-collapse text-[13px]">
              <thead>
                <tr>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">กิจกรรม</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">เจ้าหน้าที่</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">วันที่</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">อำเภอ</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">สถานะ</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="o in operations" :key="o.activity + o.officer">
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ o.activity }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ o.officer }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ o.date }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ o.district }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge"><span class="text-white rounded-full px-[10px] py-0.5 text-[11.5px] whitespace-nowrap" :class="'bg-' + o.statusClass">{{ o.status }}</span></td>
                </tr>
              </tbody>
            </table> -->
          </section>
        </div>
</template>
