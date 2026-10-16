<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DisasterSubsidyTest extends TestCase
{
    public function test_it_proxies_filters_and_returns_the_subsidy_summary(): void
    {
        $filters = [
            'date_from' => '2026-01',
            'date_to' => '2026-12',
            'danger_type_id' => null,
            'danger_type_name' => null,
            'province_code' => '10',
            'amphur_code' => null,
            'tambon_code' => null,
        ];
        $payload = [
            'filters' => [
                'date_from' => '202601',
                'date_to' => '202612',
                'danger_type_id' => null,
                'danger_type_name' => null,
                'province_code' => '10',
                'amphur_code' => null,
                'tambon_code' => null,
            ],
            'summary' => [
                'total_farmer' => 1,
                'damage_rai_decimal' => 2.5,
                'repair_rai_decimal' => 2.25,
                'by_plant_group' => [],
            ],
        ];

        Http::fake([
            'https://efarmer.doae.go.th/api/disaster/subsidy' => Http::response($payload),
        ]);

        $this->postJson('/api/disaster/subsidy', $filters)
            ->assertOk()
            ->assertExactJson($payload);

        Http::assertSent(fn ($request) =>
            $request->url() === 'https://efarmer.doae.go.th/api/disaster/subsidy'
            && $request->method() === 'POST'
            && $request->data() === $filters
        );
    }

    public function test_it_rejects_invalid_month_filters_without_calling_efarmer(): void
    {
        Http::fake();

        $this->postJson('/api/disaster/subsidy', [
            'date_from' => '2026-13',
            'date_to' => '2026-12',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['date_from']);

        Http::assertNothingSent();
    }
}
