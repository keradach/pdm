<script setup>
const theme = {
  icon: '📚',
  title: 'คลังความรู้สู้ภัยพิบัติด้านพืช',
  subtitle: 'Knowledge & Media',
  color: '#e8770f',
}

// Mockup data (ตัวอย่างข้อมูลจำลอง)
const flood = [
  { type: 'ข่าวประชาสัมพันธ์', title: 'แถลงสถานการณ์ภัยน้ำท่วม ประจำสัปดาห์', date: '22 ก.ย. 2569', views: '1.2k', icon: '📰' },
  { type: 'ข่าวประชาสัมพันธ์', title: 'มาตรการช่วยเหลือเกษตรกรผู้ประสบภัย', date: '20 ก.ย. 2569', views: '3.4k', icon: '📰' },
  { type: 'ข่าวประชาสัมพันธ์', title: 'เปิดจุดจ่ายน้ำเคลื่อนที่ จ.สุรินทร์', date: '18 ก.ย. 2569', views: '860', icon: '📰' },
]

const drought = [
  { title: 'แถลงสถานการณ์ภัยแล้ง ประจำสัปดาห์', tag: 'Infographic', icon: '🎞' },
  { title: 'VDO : ขั้นตอนการขอรับเงินชดเชย', tag: 'VDO', icon: '📹' },
  { title: 'สื่อความรู้การจัดการน้ำในแปลงนา', tag: 'สื่อความรู้', icon: '📖' },
]

const alternativeCrops = [
  { crop: 'ข้าวโพดเลี้ยงสัตว์', water: 'ต่ำ', period: '90 วัน', income: '12,000 บาท/ไร่', suitable: 'สูง', levelClass: 'ok' },
  { crop: 'ถั่วเขียว', water: 'ต่ำ', period: '70 วัน', income: '8,500 บาท/ไร่', suitable: 'สูง', levelClass: 'ok' },
  { crop: 'งา', water: 'ต่ำมาก', period: '80 วัน', income: '9,000 บาท/ไร่', suitable: 'ปานกลาง', levelClass: 'watch' },
  { crop: 'ข้าวโพดหวาน', water: 'ปานกลาง', period: '75 วัน', income: '15,000 บาท/ไร่', suitable: 'ปานกลาง', levelClass: 'watch' },
]

const waterSaving = [
  { method: 'การปลูกหลังนาเขตชลประทาน', saving: '30–40%', note: 'ใช้น้ำร่วมกับแปลงนาเดิม' },
  { method: 'ระบบน้ำหยดในแปลงผัก', saving: '50%', note: 'ร่วมกับแปลงผักสวนครัว' },
  { method: 'การคลุมดินด้วยฟาง / วัสดุเหลือใช้', saving: '20–25%', note: 'ลดการระเหยของน้ำผิวดิน' },
  { method: 'การปรับพื้นที่แบบแลนด์เลเวลลิ่ง', saving: '35%', note: 'กระจายน้ำให้ทั่วแปลง' },
]

const guides = [
  { title: 'คู่มือการบริหารจัดการน้ำในภาวะขาดแคลน', format: 'PDF • 32 หน้า' },
  { title: 'เอกสารวิชาการการใช้น้ำอย่างประหยัด', format: 'PDF • 18 หน้า' },
  { title: 'คู่มือการกักเก็บน้ำในพื้นที่เกษตร', format: 'PDF • 24 หน้า' },
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

        <div class="grid grid-cols-2 gap-[14px] max-[1280px]:grid-cols-1">
          <section class="card pb-1.5">
            <div class="font-bold text-[14px] p-[14px_16px_8px]" :style="{ color: theme.color }">📰 คลังความรู้สู้ภัยน้ำท่วม</div>
            <!-- <ul class="media-list grid gap-[10px] p-[4px_16px_14px] list-none m-0">
              <li v-for="m in flood" :key="m.title">
                <span class="media-icon w-[34px] h-[34px] shrink-0 flex items-center justify-center text-[17px] bg-[#f4f6f5] rounded-[8px]">{{ m.icon }}</span>
                <span class="media-body flex flex-col text-[13px]">
                  <strong>{{ m.title }}</strong>
                  <small>{{ m.type }} · {{ m.date }} · {{ m.views }} ครั้ง</small>
                </span>
              </li>
            </ul> -->
          </section>

          <section class="card pb-1.5">
            <div class="font-bold text-[14px] p-[14px_16px_8px]" :style="{ color: theme.color }">🎞 คลังความรู้สู้ภัยแล้ง</div>
            <!-- <ul class="media-list grid gap-[10px] p-[4px_16px_14px] list-none m-0">
              <li v-for="g in drought" :key="g.title">
                <span class="media-icon w-[34px] h-[34px] shrink-0 flex items-center justify-center text-[17px] bg-[#f4f6f5] rounded-[8px]">{{ g.icon }}</span>
                <span class="media-body flex flex-col text-[13px]">
                  <strong>{{ g.title }}</strong>
                  <small>{{ g.tag }}</small>
                </span>
              </li>
            </ul> -->
          </section>
        </div>

        <div class="grid grid-cols-2 gap-[14px] max-[1280px]:grid-cols-1">
          <section class="card pb-1.5">
            <div class="font-bold text-[14px] p-[14px_16px_8px]" :style="{ color: theme.color }">🌱 เมนูพืชทางเลือก</div>
            <!-- <table class="w-full border-collapse text-[13px]">
            <thead>
              <tr>
                <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">พืช</th>
                <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">การใช้น้ำ</th>
                <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">ระยะเวลา</th>
                <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">รายได้โดยประมาณ</th>
                <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">ความเหมาะสม</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in alternativeCrops" :key="c.crop">
                <td class="text-left p-[9px_12px] border-b border-edge">{{ c.crop }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge">{{ c.water }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge">{{ c.period }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge">{{ c.income }}</td>
                <td class="text-left p-[9px_12px] border-b border-edge"><span class="text-white rounded-full px-[10px] py-0.5 text-[11.5px] whitespace-nowrap" :class="'bg-' + c.levelClass">{{ c.suitable }}</span></td>
              </tr>
            </tbody>
          </table> -->
          </section>
          <!-- 
        <div class="grid grid-cols-2 gap-[14px] max-[1280px]:grid-cols-1">
          <section class="card pb-1.5">
            <div class="font-bold text-[14px] p-[14px_16px_8px]" :style="{ color: theme.color }">💧 ทางเลือกการปลูกพืชใช้น้ำน้อย</div>
            <table class="w-full border-collapse text-[13px]">
              <thead>
                <tr>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">วิธีการ</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">ประหยัดน้ำ</th>
                  <th class="text-left p-[9px_12px] border-b border-edge bg-[#f6f8f7] text-xs font-semibold text-muted">รายละเอียด</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="w in waterSaving" :key="w.method">
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ w.method }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ w.saving }}</td>
                  <td class="text-left p-[9px_12px] border-b border-edge">{{ w.note }}</td>
                </tr>
              </tbody>
            </table>
          </section>

          <section class="card pb-1.5">
            <div class="font-bold text-[14px] p-[14px_16px_8px]" :style="{ color: theme.color }">📕 คำแนะนำวิชาการ / คู่มือ</div>
            <ul class="media-list grid gap-[10px] p-[4px_16px_14px] list-none m-0">
              <li v-for="d in guides" :key="d.title">
                <span class="media-icon w-[34px] h-[34px] shrink-0 flex items-center justify-center text-[17px] bg-[#f4f6f5] rounded-[8px]">📑</span>
                <span class="media-body flex flex-col text-[13px]">
                  <strong>{{ d.title }}</strong>
                  <small>{{ d.format }}</small>
                </span>
              </li>
            </ul>
          </section> 
    </div> -->
        </div>
</template>
