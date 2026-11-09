<?php

namespace Database\Seeders;

use App\Models\CercInfrastructureAsset;
use Illuminate\Database\Seeder;

class CercSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $schemes = [
            [
                'institution' => 'Demo Institution', 'programme_type' => 'Minor Tank Rehabilitation',
                'contract_status' => 'Ongoing', 'province' => 'North Central', 'district' => 'Anuradhapura',
                'ds_division' => 'Thalawa', 'asc' => 'Demo Agrarian Service Centre', 'tank_name' => 'CERC Demo Minor Tank 01',
                'civil_cost' => 25000000, 'preliminaries_cost' => 2000000, 'direct_cost' => 27000000,
                'vat_18' => 4860000, 'contingencies_cost' => 1350000, 'total_cost_estimate' => 33210000,
                'awarded_civil_cost' => 24000000, 'awarded_preliminaries_cost' => 1800000,
                'awarded_amount_without_vat_contingencies' => 25800000, 'advance_imprest' => 2500000,
                'provisional_sum' => 500000, 'category' => 'Rehabilitation', 'year' => 2026, 'size' => 'Minor',
                'beneficiary_families' => 112, 'command_area_ac' => 86, 'lot' => 'Lot 01', 'cluster' => 'Cluster A',
                'date_of_award' => '2026-01-15', 'agreement_sign_date' => '2026-01-22', 'contract_period_days' => 180,
                'completion_date' => '2026-07-21', 'contract_number' => 'DEMO-MN-001',
                'contractor_name' => 'Demo Contractor', 'contractor_address' => 'Anuradhapura',
                'contractor_contact_number' => '0700000000', 'tank_type' => 'minor',
            ],
        ];

        foreach ($schemes as $scheme) {
            CercInfrastructureAsset::firstOrCreate(
                ['tank_type' => 'minor', 'tank_name' => $scheme['tank_name']],
                $scheme,
            );
        }

        $this->command?->info(sprintf(
            'CERC Minor Tank demo data is available: %d records.',
            CercInfrastructureAsset::where('tank_type', 'minor')->count(),
        ));
    }
}
