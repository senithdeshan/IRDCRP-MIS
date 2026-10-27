<?php

namespace Database\Seeders;

use App\Models\CercFarmerOrganization;
use App\Models\CercInfrastructureAsset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CercSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            [
                'tank_type' => 'minor', 'tank_name' => 'CERC Demo Minor Tank 01',
                'tank_registration_number' => 'DEMO-MN-001', 'river_basin' => 'Malwathu Oya', 'river_basin_number' => 'DEMO-MN-01',
                'cascade_name' => 'CERC Demo Minor Cascade A', 'province' => 'North Central', 'district' => 'Anuradhapura',
                'ds_division' => 'Thalawa', 'asc' => 'Demo Agrarian Service Centre', 'gn_division' => 'Demo GN Division A',
                'gn_code' => 'DEMO-MN-A', 'gn_division_families' => 145, 'tank_capacity_ac_ft' => 680.00,
                'catchment_area_ha' => 420.00, 'irrigable_area_existing_ha' => 86.00, 'irrigable_area_cultivated_ha' => 72.00,
                'main_crop_cultivation' => 'Paddy', 'tank_farmer_families' => 112, 'farmer_organization_families' => 98,
                'scheme_type_tank' => 'Minor', 'canal_length_left_bank_km' => 2.350, 'canal_length_center_bank_km' => 0.750,
                'canal_length_right_bank_km' => 1.900, 'tank_status' => 'Functional',
                'cmc_name' => 'CERC Demo CMC A', 'cac_name' => 'CERC Demo CAC A',
                'cac_members' => [
                    ['member_name' => 'Demo CAC Member A1', 'position' => 'Co-chairperson', 'gender' => 'Female'],
                    ['member_name' => 'Demo CAC Member A2', 'position' => 'Member', 'gender' => 'Male'],
                ],
                'fo' => ['name' => 'CERC Demo Farmer Organization A', 'legal_registration_status' => 'Registered', 'registration_number' => 'DEMO-FO-A', 'registration_date' => '2024-06-15', 'address' => 'Demo address, Anuradhapura', 'contact_number' => '0700000001', 'email' => 'cerc-demo-a@example.com', 'total_members' => 98],
                'fo_members' => [
                    ['name_with_initials' => 'Demo FO Member A1', 'gender' => 'Female', 'role_in_organization' => 'President', 'land_extent_acres' => 1.250],
                    ['name_with_initials' => 'Demo FO Member A2', 'gender' => 'Male', 'role_in_organization' => 'Member', 'land_extent_acres' => 0.750],
                ],
            ],
            [
                'tank_type' => 'minor', 'tank_name' => 'CERC Demo Minor Tank 02',
                'tank_registration_number' => 'DEMO-MN-002', 'river_basin' => 'Kala Oya', 'river_basin_number' => 'DEMO-MN-02',
                'cascade_name' => 'CERC Demo Minor Cascade B', 'province' => 'North Western', 'district' => 'Puttalam',
                'ds_division' => 'Anamaduwa', 'asc' => 'Demo Agrarian Service Centre B', 'gn_division' => 'Demo GN Division B',
                'gn_code' => 'DEMO-MN-B', 'gn_division_families' => 92, 'tank_capacity_ac_ft' => 410.00,
                'catchment_area_ha' => 255.00, 'irrigable_area_existing_ha' => 54.00, 'irrigable_area_cultivated_ha' => 46.00,
                'main_crop_cultivation' => 'Paddy and Chena', 'tank_farmer_families' => 74, 'farmer_organization_families' => 65,
                'scheme_type_tank' => 'Minor', 'canal_length_left_bank_km' => 1.650, 'canal_length_center_bank_km' => 0.500,
                'canal_length_right_bank_km' => 1.250, 'tank_status' => 'Partially functional',
                'cmc_name' => 'CERC Demo CMC B', 'cac_name' => 'CERC Demo CAC B',
                'cac_members' => [
                    ['member_name' => 'Demo CAC Member B1', 'position' => 'Co-chairperson', 'gender' => 'Male'],
                    ['member_name' => 'Demo CAC Member B2', 'position' => 'Member', 'gender' => 'Female'],
                ],
                'fo' => ['name' => 'CERC Demo Farmer Organization B', 'legal_registration_status' => 'Registration in progress', 'registration_number' => 'DEMO-FO-B', 'registration_date' => '2025-02-10', 'address' => 'Demo address, Puttalam', 'contact_number' => '0700000002', 'email' => 'cerc-demo-b@example.com', 'total_members' => 65],
                'fo_members' => [
                    ['name_with_initials' => 'Demo FO Member B1', 'gender' => 'Male', 'role_in_organization' => 'Secretary', 'land_extent_acres' => 1.000],
                    ['name_with_initials' => 'Demo FO Member B2', 'gender' => 'Female', 'role_in_organization' => 'Member', 'land_extent_acres' => 0.500],
                ],
            ],
            [
                'tank_type' => 'major', 'tank_name' => 'CERC Demo Major Tank 01',
                'tank_registration_number' => 'DEMO-MJ-001', 'river_basin' => 'Mahaweli', 'river_basin_number' => 'DEMO-MJ-01',
                'cascade_name' => 'CERC Demo Major Cascade A', 'province' => 'North Central', 'district' => 'Polonnaruwa',
                'ds_division' => 'Hingurakgoda', 'asc' => 'Demo Agrarian Service Centre C', 'gn_division' => 'Demo GN Division C',
                'gn_code' => 'DEMO-MJ-C', 'gn_division_families' => 520, 'tank_capacity_ac_ft' => 12500.00,
                'catchment_area_ha' => 7100.00, 'irrigable_area_existing_ha' => 1850.00, 'irrigable_area_cultivated_ha' => 1620.00,
                'main_crop_cultivation' => 'Paddy', 'tank_farmer_families' => 1460, 'farmer_organization_families' => 1320,
                'scheme_type_tank' => 'Major', 'canal_length_left_bank_km' => 12.500, 'canal_length_center_bank_km' => 4.250,
                'canal_length_right_bank_km' => 10.750, 'tank_status' => 'Functional',
                'cmc_name' => 'CERC Demo CMC C', 'cac_name' => 'CERC Demo CAC C',
                'cac_members' => [
                    ['member_name' => 'Demo CAC Member C1', 'position' => 'Co-chairperson', 'gender' => 'Female'],
                    ['member_name' => 'Demo CAC Member C2', 'position' => 'Member', 'gender' => 'Male'],
                ],
                'fo' => ['name' => 'CERC Demo Farmer Organization C', 'legal_registration_status' => 'Registered', 'registration_number' => 'DEMO-FO-C', 'registration_date' => '2023-11-20', 'address' => 'Demo address, Polonnaruwa', 'contact_number' => '0700000003', 'email' => 'cerc-demo-c@example.com', 'total_members' => 1320],
                'fo_members' => [
                    ['name_with_initials' => 'Demo FO Member C1', 'gender' => 'Female', 'role_in_organization' => 'Treasurer', 'land_extent_acres' => 2.500],
                    ['name_with_initials' => 'Demo FO Member C2', 'gender' => 'Male', 'role_in_organization' => 'Member', 'land_extent_acres' => 1.750],
                ],
            ],
            [
                'tank_type' => 'major', 'tank_name' => 'CERC Demo Major Tank 02',
                'tank_registration_number' => 'DEMO-MJ-002', 'river_basin' => 'Yan Oya', 'river_basin_number' => 'DEMO-MJ-02',
                'cascade_name' => 'CERC Demo Major Cascade B', 'province' => 'Eastern', 'district' => 'Trincomalee',
                'ds_division' => 'Morawewa', 'asc' => 'Demo Agrarian Service Centre D', 'gn_division' => 'Demo GN Division D',
                'gn_code' => 'DEMO-MJ-D', 'gn_division_families' => 380, 'tank_capacity_ac_ft' => 8400.00,
                'catchment_area_ha' => 4900.00, 'irrigable_area_existing_ha' => 1240.00, 'irrigable_area_cultivated_ha' => 1050.00,
                'main_crop_cultivation' => 'Paddy and pulses', 'tank_farmer_families' => 980, 'farmer_organization_families' => 875,
                'scheme_type_tank' => 'Major', 'canal_length_left_bank_km' => 9.800, 'canal_length_center_bank_km' => 3.100,
                'canal_length_right_bank_km' => 8.400, 'tank_status' => 'Partially functional',
                'cmc_name' => 'CERC Demo CMC D', 'cac_name' => 'CERC Demo CAC D',
                'cac_members' => [
                    ['member_name' => 'Demo CAC Member D1', 'position' => 'Co-chairperson', 'gender' => 'Male'],
                    ['member_name' => 'Demo CAC Member D2', 'position' => 'Member', 'gender' => 'Female'],
                ],
                'fo' => ['name' => 'CERC Demo Farmer Organization D', 'legal_registration_status' => 'Registered', 'registration_number' => 'DEMO-FO-D', 'registration_date' => '2024-09-05', 'address' => 'Demo address, Trincomalee', 'contact_number' => '0700000004', 'email' => 'cerc-demo-d@example.com', 'total_members' => 875],
                'fo_members' => [
                    ['name_with_initials' => 'Demo FO Member D1', 'gender' => 'Male', 'role_in_organization' => 'President', 'land_extent_acres' => 2.000],
                    ['name_with_initials' => 'Demo FO Member D2', 'gender' => 'Female', 'role_in_organization' => 'Member', 'land_extent_acres' => 1.250],
                ],
            ],
        ];

        DB::transaction(function () use ($samples): void {
            foreach ($samples as $sample) {
                $cacMembers = $sample['cac_members'];
                $cmcName = $sample['cmc_name'];
                $cacName = $sample['cac_name'];
                $organizationData = $sample['fo'];
                $organizationMembers = $sample['fo_members'];
                unset($sample['cac_members'], $sample['cmc_name'], $sample['cac_name'], $sample['fo'], $sample['fo_members']);

                $asset = CercInfrastructureAsset::firstOrCreate(
                    ['tank_type' => $sample['tank_type'], 'tank_name' => $sample['tank_name']],
                    $sample,
                );

                foreach ($cacMembers as $member) {
                    $asset->cacMembers()->firstOrCreate(
                        ['member_name' => $member['member_name']],
                        [...$member, 'cmc_name' => $cmcName, 'cac_name' => $cacName],
                    );
                }

                $organization = CercFarmerOrganization::firstOrCreate(
                    ['cerc_infrastructure_asset_id' => $asset->id],
                    $organizationData,
                );

                foreach ($organizationMembers as $member) {
                    $organization->members()->firstOrCreate(
                        ['name_with_initials' => $member['name_with_initials']],
                        $member,
                    );
                }
            }
        });

        $this->command?->info(sprintf(
            'CERC demo data is available: %d minor tank records and %d major tank records.',
            CercInfrastructureAsset::where('tank_type', 'minor')->count(),
            CercInfrastructureAsset::where('tank_type', 'major')->count(),
        ));
    }
}
