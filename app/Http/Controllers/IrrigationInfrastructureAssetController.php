<?php

namespace App\Http\Controllers;

use App\Models\IrrigationInfrastructureAsset;
use App\Models\IrrigationAssetCacMember;
use App\Models\IrrigationFarmerOrganization;
use App\Models\IrrigationFarmerOrganizationMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IrrigationInfrastructureAssetController extends Controller
{
    private const SCHEME_TYPES = ['Major', 'Medium', 'Minor'];
    private const ANICUT_TYPES = ['Large', 'Minor'];
    private const TANK_STATUSES = ['Functional', 'Partially functional', 'Non-functional', 'Abandoned'];
    private const POSITIONS = ['Member', 'Co-chairperson'];

    public function overview(): View
    {
        return view('irrigation-assets.overview', [
            'schemeCount' => IrrigationInfrastructureAsset::count(),
            'cacMemberCount' => IrrigationAssetCacMember::count(),
            'organizationCount' => IrrigationFarmerOrganization::count(),
            'farmerMemberCount' => IrrigationFarmerOrganizationMember::count(),
            'functionalSchemeCount' => IrrigationInfrastructureAsset::where('tank_status', 'Functional')->count(),
            'partiallyFunctionalSchemeCount' => IrrigationInfrastructureAsset::where('tank_status', 'Partially functional')->count(),
            'nonFunctionalSchemeCount' => IrrigationInfrastructureAsset::where('tank_status', 'Non-functional')->count(),
            'abandonedSchemeCount' => IrrigationInfrastructureAsset::where('tank_status', 'Abandoned')->count(),
        ]);
    }

    public function index(): View
    {
        return view('irrigation-assets.index', [
            'assets' => IrrigationInfrastructureAsset::withCount('cacMembers')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('irrigation-assets.form', ['asset' => new IrrigationInfrastructureAsset, 'editing' => false]);
    }

    public function show(IrrigationInfrastructureAsset $irrigationInfrastructureAsset): View
    {
        $irrigationInfrastructureAsset->load(['cacMembers', 'farmerOrganization.members']);

        return view('irrigation-assets.show', ['asset' => $irrigationInfrastructureAsset]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $members = $data['cac_members'] ?? [];
        $cmcName = $data['cmc_name'] ?? null;
        $cacName = $data['cac_name'] ?? null;
        unset($data['cmc_name'], $data['cac_name']);
        $members = array_map(fn ($member) => [...$member, 'cmc_name' => $cmcName, 'cac_name' => $cacName], $members);
        unset($data['cac_members']);
        $data['created_by'] = $request->user()->id;
        $asset = IrrigationInfrastructureAsset::create($data);
        $asset->cacMembers()->createMany($members);

        return redirect()->route('irrigation-assets.index')->with('status', 'Irrigation infrastructure asset saved.');
    }

    public function edit(IrrigationInfrastructureAsset $irrigationInfrastructureAsset): View
    {
        $irrigationInfrastructureAsset->load('cacMembers');

        return view('irrigation-assets.form', ['asset' => $irrigationInfrastructureAsset, 'editing' => true]);
    }

    public function update(Request $request, IrrigationInfrastructureAsset $irrigationInfrastructureAsset): RedirectResponse
    {
        $data = $this->validated($request);
        $members = $data['cac_members'] ?? [];
        $cmcName = $data['cmc_name'] ?? null;
        $cacName = $data['cac_name'] ?? null;
        unset($data['cmc_name'], $data['cac_name']);
        $members = array_map(fn ($member) => [...$member, 'cmc_name' => $cmcName, 'cac_name' => $cacName], $members);
        unset($data['cac_members']);
        $irrigationInfrastructureAsset->update($data);
        $irrigationInfrastructureAsset->cacMembers()->delete();
        $irrigationInfrastructureAsset->cacMembers()->createMany($members);

        return redirect()->route('irrigation-assets.index')->with('status', 'Irrigation infrastructure asset updated.');
    }

    public function destroy(IrrigationInfrastructureAsset $irrigationInfrastructureAsset): RedirectResponse
    {
        $irrigationInfrastructureAsset->delete();

        return redirect()->route('irrigation-assets.index')->with('status', 'Scheme and its linked CAC, Farmer Organization, and member records were deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'river_basin' => ['nullable', 'string', 'max:255'],
            'river_basin_number' => ['nullable', 'string', 'max:255'],
            'tank_name' => ['required', 'string', 'max:255'],
            'tank_registration_number' => ['nullable', 'string', 'max:255'],
            'cascade_name' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ds_division' => ['nullable', 'string', 'max:255'],
            'asc' => ['nullable', 'string', 'max:255'],
            'gn_division' => ['nullable', 'string', 'max:255'],
            'gn_code' => ['nullable', 'string', 'max:30'],
            'gn_division_families' => ['nullable', 'integer', 'min:0'],
            'tank_capacity_ac_ft' => ['nullable', 'numeric', 'min:0'],
            'catchment_area_ha' => ['nullable', 'numeric', 'min:0'],
            'irrigable_area_existing_ha' => ['nullable', 'numeric', 'min:0'],
            'irrigable_area_cultivated_ha' => ['nullable', 'numeric', 'min:0'],
            'main_crop_cultivation' => ['nullable', 'string', 'max:255'],
            'tank_farmer_families' => ['nullable', 'integer', 'min:0'],
            'farmer_organization_families' => ['nullable', 'integer', 'min:0'],
            'scheme_type_tank' => ['nullable', 'in:'.implode(',', self::SCHEME_TYPES)],
            'scheme_type_anicut' => ['nullable', 'in:'.implode(',', self::ANICUT_TYPES)],
            'canal_length_left_bank_km' => ['nullable', 'numeric', 'min:0'],
            'canal_length_center_bank_km' => ['nullable', 'numeric', 'min:0'],
            'canal_length_right_bank_km' => ['nullable', 'numeric', 'min:0'],
            'tank_status' => ['nullable', 'in:'.implode(',', self::TANK_STATUSES)],
            'cmc_name' => ['nullable', 'string', 'max:255'],
            'cac_name' => ['nullable', 'string', 'max:255'],
            'cac_members' => ['nullable', 'array'],
            'cac_members.*.member_name' => ['required', 'string', 'max:255'],
            'cac_members.*.position' => ['required', 'in:'.implode(',', self::POSITIONS)],
            'cac_members.*.nic_number' => ['nullable', 'regex:/^(\d{9}[vVxX]|\d{12})$/'],
            'cac_members.*.gender' => ['nullable', 'in:Male,Female'],
            'cac_members.*.contact_number' => ['nullable', 'regex:/^\d{10}$/'],
        ], [
            'cac_members.*.contact_number.regex' => 'The Member Contact Number must contain exactly 10 digits.',
            'cac_members.*.nic_number.regex' => 'The Member NIC Number must contain 10 or 12 digits.',
        ], [
            'river_basin' => 'River Basin',
            'river_basin_number' => 'River Basin Number',
            'tank_name' => 'Tank Name',
            'tank_registration_number' => 'Tank Registration Number',
            'cascade_name' => 'Cascade Name',
            'province' => 'Province',
            'district' => 'District',
            'ds_division' => 'DS Division',
            'asc' => 'ASC',
            'gn_division' => 'GN Division',
            'gn_code' => 'GN Code',
            'gn_division_families' => 'GN Division Families',
            'tank_capacity_ac_ft' => 'Tank Capacity (ac.ft)',
            'catchment_area_ha' => 'Catchment Area (ha)',
            'irrigable_area_existing_ha' => 'Existing Irrigable Area (ha)',
            'irrigable_area_cultivated_ha' => 'Cultivated Irrigable Area (ha)',
            'main_crop_cultivation' => 'Main Crop Cultivation',
            'tank_farmer_families' => 'Farmer Families in the Tank',
            'farmer_organization_families' => 'Farmer Families in the Farmer Organization',
            'scheme_type_tank' => 'Tank Scheme Type',
            'scheme_type_anicut' => 'Anicut Scheme Type',
            'canal_length_left_bank_km' => 'Left Bank Canal Length (km)',
            'canal_length_center_bank_km' => 'Center Bank Canal Length (km)',
            'canal_length_right_bank_km' => 'Right Bank Canal Length (km)',
            'tank_status' => 'Tank Status / Condition',
            'cmc_name' => 'CMC Name',
            'cac_name' => 'CAC Name',
            'cac_members.*.member_name' => 'CAC Member Name',
            'cac_members.*.position' => 'Member Position',
            'cac_members.*.nic_number' => 'Member NIC Number',
            'cac_members.*.gender' => 'Member Gender',
            'cac_members.*.contact_number' => 'Member Contact Number',
        ]);
    }
}
