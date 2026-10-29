<?php

namespace App\Http\Controllers;

use App\Rules\SriLankanNic;
use App\Models\IrrigationFarmerOrganization;
use App\Models\IrrigationInfrastructureAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IrrigationFarmerOrganizationController extends Controller
{
    private const REGISTRATION_STATUSES = ['Registered', 'Not registered', 'Registration in progress'];
    private const GENDERS = ['Male', 'Female'];
    private const ROLES = ['Member', 'President', 'Secretary', 'Treasurer', 'Other'];

    public function index(): View
    {
        return view('irrigation-farmer-organizations.index', [
            'organizations' => IrrigationFarmerOrganization::with(['asset', 'members'])->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        $availableAssets = IrrigationInfrastructureAsset::query()
            ->whereDoesntHave('farmerOrganization')
            ->orderBy('tank_name')
            ->get();

        return view('irrigation-farmer-organizations.form', [
            'organization' => new IrrigationFarmerOrganization,
            'availableAssets' => $availableAssets,
            'editing' => false,
        ]);
    }

    public function show(IrrigationFarmerOrganization $irrigationFarmerOrganization): View
    {
        $irrigationFarmerOrganization->load(['asset', 'members']);

        return view('irrigation-farmer-organizations.show', [
            'organization' => $irrigationFarmerOrganization,
        ]);
    }

    public function destroy(IrrigationFarmerOrganization $irrigationFarmerOrganization): RedirectResponse
    {
        DB::transaction(function () use ($irrigationFarmerOrganization): void {
            $irrigationFarmerOrganization->members()->delete();
            $irrigationFarmerOrganization->delete();
        });

        return redirect()->route('irrigation-farmer-organizations.index')
            ->with('status', 'Farmer Organization and its members were deleted.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $members = $data['members'];
        unset($data['members']);
        $data['created_by'] = $request->user()->id;

        DB::transaction(function () use ($data, $members): void {
            $organization = IrrigationFarmerOrganization::create($data);
            $organization->members()->createMany($members);
        });

        return redirect()->route('irrigation-farmer-organizations.index')->with('status', 'Farmer Organization and its members were saved.');
    }

    public function edit(Request $request, IrrigationFarmerOrganization $irrigationFarmerOrganization): View
    {
        $irrigationFarmerOrganization->load('members');
        $availableAssets = IrrigationInfrastructureAsset::query()
            ->where(fn ($query) => $query->whereDoesntHave('farmerOrganization')
                ->orWhere('id', $irrigationFarmerOrganization->irrigation_infrastructure_asset_id))
            ->orderBy('tank_name')
            ->get();

        return view('irrigation-farmer-organizations.form', [
            'organization' => $irrigationFarmerOrganization,
            'availableAssets' => $availableAssets,
            'editing' => true,
        ]);
    }

    public function update(Request $request, IrrigationFarmerOrganization $irrigationFarmerOrganization): RedirectResponse
    {
        $data = $this->validated($request, $irrigationFarmerOrganization);
        $members = $data['members'];
        unset($data['members']);

        DB::transaction(function () use ($irrigationFarmerOrganization, $data, $members): void {
            $irrigationFarmerOrganization->update($data);
            $irrigationFarmerOrganization->members()->delete();
            $irrigationFarmerOrganization->members()->createMany($members);
        });

        return redirect()->route('irrigation-farmer-organizations.index')->with('status', 'Farmer Organization and its members were updated.');
    }

    private function validated(Request $request, ?IrrigationFarmerOrganization $organization = null): array
    {
        return $request->validate([
            'irrigation_infrastructure_asset_id' => [
                'required', 'integer',
                Rule::exists('irrigation_infrastructure_assets', 'id'),
                Rule::unique('irrigation_farmer_organizations', 'irrigation_infrastructure_asset_id')->ignore($organization),
            ],
            'name' => ['required', 'string', 'max:255'],
            'legal_registration_status' => ['required', Rule::in(self::REGISTRATION_STATUSES)],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'registration_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:5000'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'total_members' => ['nullable', 'integer', 'min:0'],
            'members' => ['required', 'array', 'min:1'],
            'members.*.membership_number' => ['nullable', 'string', 'max:100'],
            'members.*.name_with_initials' => ['required', 'string', 'max:255'],
            'members.*.gender' => ['nullable', Rule::in(self::GENDERS)],
            'members.*.nic_number' => ['nullable', 'string', 'max:12', new SriLankanNic],
            'members.*.phone_number' => ['nullable', 'regex:/^\d{10}$/'],
            'members.*.address' => ['nullable', 'string', 'max:5000'],
            'members.*.province' => ['nullable', 'string', 'max:255'],
            'members.*.district' => ['nullable', 'string', 'max:255'],
            'members.*.ds_division' => ['nullable', 'string', 'max:255'],
            'members.*.gn_division' => ['nullable', 'string', 'max:255'],
            'members.*.land_extent_acres' => ['nullable', 'numeric', 'min:0'],
            'members.*.role_in_organization' => ['nullable', Rule::in(self::ROLES)],
        ], [
            'irrigation_infrastructure_asset_id.required' => 'Please select the related scheme.',
            'irrigation_infrastructure_asset_id.unique' => 'A Farmer Organization is already registered for the selected scheme.',
            'email.email' => 'Enter a valid Email Address.',
            'members.required' => 'Add at least one Farmer Organization Member.',
            'members.*.phone_number.regex' => 'Member Phone Number must contain exactly 10 digits.',
        ], [
            'irrigation_infrastructure_asset_id' => 'Scheme',
            'name' => 'Name of Farmer Organization',
            'legal_registration_status' => 'Legal / Registration Status',
            'registration_number' => 'Registration Number',
            'registration_date' => 'Registration Date',
            'address' => 'Address',
            'contact_number' => 'Organization Contact Number',
            'email' => 'Email Address',
            'total_members' => 'Total Number of Members above available',
            'members.*.membership_number' => 'Membership Number',
            'members.*.name_with_initials' => 'Name with Initials',
            'members.*.gender' => 'Gender',
            'members.*.nic_number' => 'NIC',
            'members.*.phone_number' => 'Phone Number',
            'members.*.address' => 'Member Address',
            'members.*.province' => 'Province',
            'members.*.district' => 'District',
            'members.*.ds_division' => 'DS Division',
            'members.*.gn_division' => 'GN Division',
            'members.*.land_extent_acres' => 'Land Extent (acres)',
            'members.*.role_in_organization' => 'Role in Farmer Organization',
        ]);
    }
}
