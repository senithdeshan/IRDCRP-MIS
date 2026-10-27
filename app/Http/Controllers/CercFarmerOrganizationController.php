<?php

namespace App\Http\Controllers;

use App\Models\CercFarmerOrganization;
use App\Models\CercInfrastructureAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CercFarmerOrganizationController extends Controller
{
    private const REGISTRATION_STATUSES = ['Registered', 'Not registered', 'Registration in progress'];
    private const GENDERS = ['Male', 'Female'];
    private const ROLES = ['Member', 'President', 'Secretary', 'Treasurer', 'Other'];

    private function tankType(Request $request): string
    {
        return in_array($request->input('tank_type', $request->query('tank_type')), ['minor', 'major'], true)
            ? $request->input('tank_type', $request->query('tank_type')) : 'minor';
    }

    public function index(): View
    {
        $tankType = request('tank_type', 'minor');
        return view('cerc-farmer-organizations.index', [
            'tankType' => $tankType,
            'organizations' => CercFarmerOrganization::with(['asset', 'members'])->whereHas('asset', fn ($q) => $q->where('tank_type', $tankType))->latest()->paginate(15)->appends(request()->query()),
        ]);
    }

    public function create(Request $request): View
    {
        $tankType = $this->tankType($request);
        $availableAssets = CercInfrastructureAsset::query()->where('tank_type', $tankType)
            ->whereDoesntHave('farmerOrganization')
            ->orderBy('tank_name')
            ->get();

        return view('cerc-farmer-organizations.form', [
            'organization' => new CercFarmerOrganization,
            'availableAssets' => $availableAssets,
            'editing' => false,
            'tankType' => $tankType,
        ]);
    }

    public function show(CercFarmerOrganization $cercFarmerOrganization): View
    {
        $cercFarmerOrganization->load(['asset', 'members']);

        return view('cerc-farmer-organizations.show', [
            'organization' => $cercFarmerOrganization,
            'tankType' => $cercFarmerOrganization->asset->tank_type,
        ]);
    }

    public function destroy(Request $request, CercFarmerOrganization $cercFarmerOrganization): RedirectResponse
    {
        $tankType = $cercFarmerOrganization->asset->tank_type;
        DB::transaction(function () use ($cercFarmerOrganization): void {
            $cercFarmerOrganization->members()->delete();
            $cercFarmerOrganization->delete();
        });

        return redirect()->route('cerc-farmer-organizations.index', ['tank_type' => $tankType])
            ->with('status', 'Farmer Organization and its members were deleted.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $members = $data['members'];
        unset($data['members']);
        $data['created_by'] = $request->user()->id;

        DB::transaction(function () use ($data, $members): void {
            $organization = CercFarmerOrganization::create($data);
            $organization->members()->createMany($members);
        });

        return redirect()->route('cerc-farmer-organizations.index', ['tank_type' => $this->tankType($request)])->with('status', 'CERC Farmer Organization and its members were saved.');
    }

    public function edit(Request $request, CercFarmerOrganization $cercFarmerOrganization): View
    {
        $cercFarmerOrganization->load('members');
        $tankType = $cercFarmerOrganization->asset->tank_type;
        $availableAssets = CercInfrastructureAsset::query()
            ->where('tank_type', $tankType)
            ->where(fn ($query) => $query->whereDoesntHave('farmerOrganization')
                ->orWhere('id', $cercFarmerOrganization->cerc_infrastructure_asset_id))
            ->orderBy('tank_name')
            ->get();

        return view('cerc-farmer-organizations.form', [
            'organization' => $cercFarmerOrganization,
            'availableAssets' => $availableAssets,
            'editing' => true,
            'tankType' => $tankType,
        ]);
    }

    public function update(Request $request, CercFarmerOrganization $cercFarmerOrganization): RedirectResponse
    {
        $data = $this->validated($request, $cercFarmerOrganization);
        $members = $data['members'];
        unset($data['members']);

        DB::transaction(function () use ($cercFarmerOrganization, $data, $members): void {
            $cercFarmerOrganization->update($data);
            $cercFarmerOrganization->members()->delete();
            $cercFarmerOrganization->members()->createMany($members);
        });

        return redirect()->route('cerc-farmer-organizations.index', ['tank_type' => $cercFarmerOrganization->asset->tank_type])->with('status', 'CERC Farmer Organization and its members were updated.');
    }

    private function validated(Request $request, ?CercFarmerOrganization $organization = null): array
    {
        return $request->validate([
                'cerc_infrastructure_asset_id' => [
                'required', 'integer',
                Rule::exists('cerc_infrastructure_assets', 'id')->where('tank_type', $this->tankType($request)),
                Rule::unique('cerc_farmer_organizations', 'cerc_infrastructure_asset_id')->ignore($organization),
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
            'members.*.nic_number' => ['nullable', 'regex:/^(\d{10}|\d{12})$/'],
            'members.*.phone_number' => ['nullable', 'regex:/^\d{10}$/'],
            'members.*.address' => ['nullable', 'string', 'max:5000'],
            'members.*.province' => ['nullable', 'string', 'max:255'],
            'members.*.district' => ['nullable', 'string', 'max:255'],
            'members.*.ds_division' => ['nullable', 'string', 'max:255'],
            'members.*.gn_division' => ['nullable', 'string', 'max:255'],
            'members.*.land_extent_acres' => ['nullable', 'numeric', 'min:0'],
            'members.*.role_in_organization' => ['nullable', Rule::in(self::ROLES)],
        ], [
            'cerc_infrastructure_asset_id.required' => 'Please select the related CERC tank.',
            'cerc_infrastructure_asset_id.unique' => 'A Farmer Organization is already registered for the selected CERC tank.',
            'email.email' => 'Enter a valid Email Address.',
            'members.required' => 'Add at least one Farmer Organization Member.',
            'members.*.nic_number.regex' => 'Member NIC must contain exactly 10 or 12 digits.',
            'members.*.phone_number.regex' => 'Member Phone Number must contain exactly 10 digits.',
        ], [
            'cerc_infrastructure_asset_id' => 'CERC Tank',
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


