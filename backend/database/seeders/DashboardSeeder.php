<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\DisasterReport;
use App\Models\DisasterType;
use App\Models\Farmer;
use App\Models\Province;
use Illuminate\Database\Seeder;

class DashboardSeeder extends Seeder
{
    /**
     * Demo dataset that mirrors the numbers shown on the PDM dashboard
     * mockup. Replace with a real data-import job / ETL from GISTDA,
     * กรมอุตุนิยมวิทยา, กรมชลประทาน, ฯลฯ when wiring up production data.
     */
    public function run(): void
    {
        // ---- Disaster types (gauges: ภัยแล้ง / น้ำท่วม / ศัตรูพืช / ภัยหนาว) ----
        $types = [
            ['code' => 'drought', 'name_th' => 'ภัยแล้ง',  'icon' => 'sun',   'severity_percent' => 65, 'severity_label' => 'high'],
            ['code' => 'flood',   'name_th' => 'น้ำท่วม',  'icon' => 'flood', 'severity_percent' => 82, 'severity_label' => 'critical'],
            ['code' => 'pest',    'name_th' => 'ศัตรูพืช', 'icon' => 'bug',   'severity_percent' => 31, 'severity_label' => 'watch'],
            ['code' => 'cold',    'name_th' => 'ภัยหนาว',  'icon' => 'snow',  'severity_percent' => 12, 'severity_label' => 'normal'],
        ];
        foreach ($types as $t) {
            DisasterType::updateOrCreate(['code' => $t['code']], $t);
        }

        // ---- Provinces (แผนที่ความเสี่ยงภัยด้านพืช + 10 อันดับพื้นที่เสียหายสูงสุด) ----
        $provinces = [
            ['name_th' => 'เชียงราย',     'risk_level' => 'critical', 'lat' => 19.9105, 'lng' => 99.8406, 'amphoe_affected' => 18, 'tambon_affected' => 124, 'farmers_affected' => 48532, 'damaged_area_rai' => 128450],
            ['name_th' => 'น่าน',         'risk_level' => 'high',     'lat' => 18.7757, 'lng' => 100.7730,'amphoe_affected' => 12, 'tambon_affected' => 88,  'farmers_affected' => 30210, 'damaged_area_rai' => 114320],
            ['name_th' => 'แพร่',         'risk_level' => 'high',     'lat' => 18.1445, 'lng' => 100.1405,'amphoe_affected' => 8,  'tambon_affected' => 60,  'farmers_affected' => 21870, 'damaged_area_rai' => 98230],
            ['name_th' => 'สุโขทัย',      'risk_level' => 'high',     'lat' => 17.0068, 'lng' => 99.8265, 'amphoe_affected' => 9,  'tambon_affected' => 65,  'farmers_affected' => 19540, 'damaged_area_rai' => 92110],
            ['name_th' => 'พิษณุโลก',     'risk_level' => 'watch',    'lat' => 16.8211, 'lng' => 100.2659,'amphoe_affected' => 9,  'tambon_affected' => 70,  'farmers_affected' => 18230, 'damaged_area_rai' => 84360],
            ['name_th' => 'อุบลราชธานี',  'risk_level' => 'high',     'lat' => 15.2287, 'lng' => 104.8564,'amphoe_affected' => 14, 'tambon_affected' => 95,  'farmers_affected' => 25010, 'damaged_area_rai' => 78450],
            ['name_th' => 'นครสวรรค์',    'risk_level' => 'watch',    'lat' => 15.7030, 'lng' => 100.1367,'amphoe_affected' => 7,  'tambon_affected' => 50,  'farmers_affected' => 15020, 'damaged_area_rai' => 73220],
            ['name_th' => 'กาญจนบุรี',    'risk_level' => 'watch',    'lat' => 14.0227, 'lng' => 99.5328, 'amphoe_affected' => 6,  'tambon_affected' => 40,  'farmers_affected' => 12980, 'damaged_area_rai' => 69180],
            ['name_th' => 'ตาก',          'risk_level' => 'high',     'lat' => 16.8839, 'lng' => 99.1258, 'amphoe_affected' => 6,  'tambon_affected' => 42,  'farmers_affected' => 11210, 'damaged_area_rai' => 64530],
            ['name_th' => 'เลย',          'risk_level' => 'watch',    'lat' => 17.4860, 'lng' => 101.7223,'amphoe_affected' => 5,  'tambon_affected' => 35,  'farmers_affected' => 9840,  'damaged_area_rai' => 58740],
            ['name_th' => 'เชียงใหม่',    'risk_level' => 'watch',    'lat' => 18.7883, 'lng' => 98.9853, 'amphoe_affected' => 10, 'tambon_affected' => 72,  'farmers_affected' => 17600, 'damaged_area_rai' => 52100],
            ['name_th' => 'พะเยา',        'risk_level' => 'watch',    'lat' => 19.1664, 'lng' => 99.9019, 'amphoe_affected' => 5,  'tambon_affected' => 30,  'farmers_affected' => 8100,  'damaged_area_rai' => 41200],
            ['name_th' => 'ลำปาง',        'risk_level' => 'normal',   'lat' => 18.2888, 'lng' => 99.4909, 'amphoe_affected' => 3,  'tambon_affected' => 20,  'farmers_affected' => 5200,  'damaged_area_rai' => 22300],
            ['name_th' => 'ขอนแก่น',      'risk_level' => 'normal',   'lat' => 16.4419, 'lng' => 102.8360,'amphoe_affected' => 2,  'tambon_affected' => 15,  'farmers_affected' => 4100,  'damaged_area_rai' => 18000],
            ['name_th' => 'สุราษฎร์ธานี', 'risk_level' => 'normal',   'lat' => 9.1382,  'lng' => 99.3215, 'amphoe_affected' => 1,  'tambon_affected' => 8,   'farmers_affected' => 2100,  'damaged_area_rai' => 9200],
        ];

        foreach ($provinces as $p) {
            Province::updateOrCreate(
                ['name_th' => $p['name_th']],
                $p + ['data_updated_at' => now()]
            );
        }

        // ---- Monthly trend for the last 10 months (แนวโน้มพื้นที่เสียหาย) ----
        $cumulativeMillionRai = [0.42, 0.55, 0.67, 0.81, 0.95, 1.15, 1.28, 1.42, 1.55, 1.71, 1.82];
        $droughtType = DisasterType::where('code', 'drought')->first();
        $anyProvince = Province::first();

        $prev = 0;
        foreach ($cumulativeMillionRai as $i => $millionRai) {
            $monthTotalRai = ($millionRai - $prev) * 1_000_000;
            $prev = $millionRai;

            DisasterReport::updateOrCreate(
                [
                    'province_id' => $anyProvince->id,
                    'disaster_type_id' => $droughtType->id,
                    'report_month' => now()->startOfYear()->addMonths($i)->format('Y-m-d'),
                ],
                [
                    'damaged_area_rai' => max($monthTotalRai, 0),
                    'farmers_affected' => (int) ($monthTotalRai / 3),
                    'status' => 'assessing',
                ]
            );
        }

        // ---- Disaster-type breakdown for the donut chart (สัดส่วนประเภทภัยพิบัติ ปี 2568) ----
        $breakdown = [
            'drought' => 44, 'flood' => 32, 'cold' => 12, 'pest' => 7, // "โรคพืช" reuses pest bucket at 5% in a real schema you'd add a 5th type
        ];
        foreach ($breakdown as $code => $percent) {
            $type = DisasterType::where('code', $code)->first();
            if (! $type) {
                continue;
            }
            DisasterReport::updateOrCreate(
                [
                    'province_id' => $anyProvince->id,
                    'disaster_type_id' => $type->id,
                    'report_month' => now()->startOfYear()->format('Y-m-d'),
                ],
                [
                    'damaged_area_rai' => $percent * 10000,
                    'farmers_affected' => $percent * 300,
                    'status' => 'assisted',
                ]
            );
        }

        // ---- A handful of demo farmers so the assistance-progress card has data ----
        foreach (range(1, 50) as $n) {
            Farmer::updateOrCreate(
                ['national_id' => str_pad((string) $n, 13, '0', STR_PAD_LEFT)],
                [
                    'full_name' => "เกษตรกรตัวอย่าง #{$n}",
                    'province_id' => Province::inRandomOrder()->first()->id,
                    'amphoe' => 'อำเภอตัวอย่าง',
                    'tambon' => 'ตำบลตัวอย่าง',
                    'assistance_amount' => 3120.00,
                    'assistance_paid' => $n % 10 !== 0, // ~89% paid, matches mockup
                    'registered_at' => now()->subDays(30),
                ]
            );
        }

        // ---- Active alert banner (พายุฝนตกหนักและฝนสะสมต่อเนื่อง) ----
        Alert::updateOrCreate(
            ['title' => 'พายุฝนตกหนักและฝนสะสมต่อเนื่อง'],
            [
                'message' => 'พื้นที่เสี่ยงน้ำท่วมฉับพลัน น้ำป่าไหลหลาก และดินถล่ม',
                'risk_level' => 'critical',
                'watch_provinces' => ['เชียงราย', 'น่าน', 'แพร่', 'ตาก', 'อุบลราชธานี'],
                'issued_at' => now(),
                'expires_at' => now()->addDays(3),
                'is_active' => true,
            ]
        );
    }
}
