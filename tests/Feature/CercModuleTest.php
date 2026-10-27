<?php

namespace Tests\Feature;

use App\Models\CercFarmerOrganization;
use App\Models\CercInfrastructureAsset;
use App\Models\IrrigationInfrastructureAsset;
use App\Models\User;
use App\Support\SimpleXlsxWorkbook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CercModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_cerc_category_pages_render_and_keep_their_category_separate(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('cerc.index'))->assertOk()->assertSee('CERC');
        $this->get(route('cerc-assets.index', ['tank_type' => 'minor']))->assertOk()->assertSee('Minor Tank');
        $this->get(route('cerc-assets.index', ['tank_type' => 'major']))->assertOk()->assertSee('Major Tanks');
        $this->get(route('cerc-assets.template', ['tank_type' => 'minor']))->assertOk()->assertDownload('cerc-minor-tank-template.xlsx');
        $this->get(route('cerc-assets.export', ['tank_type' => 'major']))->assertOk()->assertDownload('cerc-major-tank-records.xlsx');
        $this->get(route('cerc-assets.create', ['tank_type' => 'minor']))->assertOk()->assertSee('Section A: Scheme Details');
        $this->get(route('cerc-farmer-organizations.index', ['tank_type' => 'minor']))->assertOk()->assertSee('Sections C and D');
        $this->get(route('cerc-farmer-organizations.index', ['tank_type' => 'major']))->assertOk()->assertSee('Major Tanks');
    }

    public function test_cerc_excel_import_adds_records_only_to_the_selected_tank_category(): void
    {
        $user = User::factory()->create();
        $workbook = app(SimpleXlsxWorkbook::class);
        $headers = [
            'A_Scheme' => ['Tank Registration Number', 'Tank Name'],
            'B_CAC' => ['Tank Registration Number', 'CMC Name', 'CAC Name', 'CAC Member Name', 'Member Position', 'Member NIC Number', 'Member Gender', 'Member Contact Number'],
            'C_Organization' => ['Tank Registration Number', 'Name of Farmer Organization', 'Legal / Registration Status', 'Registration Number', 'Registration Date', 'Address', 'Organization Contact Number', 'Email Address', 'Total Number of Members above available'],
            'D_Members' => ['Tank Registration Number', 'Name with Initials', 'Gender', 'NIC', 'Phone Number', 'Address', 'Land Extent (acres)', 'Role in Farmer Organization'],
        ];

        foreach (['minor', 'major'] as $tankType) {
            $registration = strtoupper($tankType).'-XLSX-001';
            $rows = [
                'A_Scheme' => [$headers['A_Scheme'], [$registration, ucfirst($tankType).' Imported Tank']],
                'B_CAC' => [$headers['B_CAC'], [$registration, 'Test CMC', 'Test CAC', 'CAC Member', 'Member', '', '', '']],
                'C_Organization' => [$headers['C_Organization'], [$registration, 'Farmers Organization', 'Registered', '', '', '', '', '', '1']],
                'D_Members' => [$headers['D_Members'], [$registration, 'FO Member', '', '', '', '', '', 'Member']],
            ];
            $file = UploadedFile::fake()->createWithContent('cerc-import.xlsx', $workbook->write($rows));

            $this->actingAs($user)->post(route('cerc-assets.import', ['tank_type' => $tankType]), [
                'tank_type' => $tankType,
                'excel_file' => $file,
            ])->assertRedirect(route('cerc-assets.index', ['tank_type' => $tankType]));

            $asset = CercInfrastructureAsset::with(['cacMembers', 'farmerOrganization.members'])
                ->where('tank_registration_number', $registration)->firstOrFail();
            $this->assertSame($tankType, $asset->tank_type);
            $this->assertCount(1, $asset->cacMembers);
            $this->assertSame('Farmers Organization', $asset->farmerOrganization->name);
            $this->assertCount(1, $asset->farmerOrganization->members);
        }
    }

    public function test_cerc_minor_major_and_irrigation_records_are_isolated(): void
    {
        $user = User::factory()->create();
        CercInfrastructureAsset::create(['tank_type' => 'minor', 'tank_name' => 'Minor CERC Only']);
        CercInfrastructureAsset::create(['tank_type' => 'major', 'tank_name' => 'Major CERC Only']);
        IrrigationInfrastructureAsset::create(['tank_name' => 'Irrigation Only']);

        $this->actingAs($user)
            ->get(route('cerc-assets.index', ['tank_type' => 'minor']))
            ->assertOk()->assertSee('Minor CERC Only')->assertDontSee('Major CERC Only')->assertDontSee('Irrigation Only');
        $this->get(route('cerc-assets.index', ['tank_type' => 'major']))
            ->assertOk()->assertSee('Major CERC Only')->assertDontSee('Minor CERC Only')->assertDontSee('Irrigation Only');
    }

    public function test_cerc_scheme_cac_farmer_organization_and_members_can_be_saved_and_viewed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('cerc-assets.store'), [
            'tank_type' => 'minor',
            'tank_name' => 'CERC Test Tank',
            'district' => 'Anuradhapura',
            'cac_members' => [[
                'member_name' => 'Test Member',
                'position' => 'Member',
                'nic_number' => '200012345678',
                'gender' => 'Female',
                'contact_number' => '0712345678',
            ]],
        ])->assertRedirect(route('cerc-assets.index', ['tank_type' => 'minor']));

        $asset = CercInfrastructureAsset::with('cacMembers')->firstOrFail();
        $this->assertSame('minor', $asset->tank_type);
        $this->assertCount(1, $asset->cacMembers);
        $this->get(route('cerc-assets.show', $asset))->assertOk()->assertSee('CERC Test Tank')->assertSee('Test Member');
        $this->get(route('cerc-assets.edit', $asset))->assertOk()->assertSee('Edit CERC Tank');

        $this->post(route('cerc-farmer-organizations.store'), [
            'tank_type' => 'minor',
            'cerc_infrastructure_asset_id' => $asset->id,
            'name' => 'CERC Farmers Group',
            'legal_registration_status' => 'Registered',
            'members' => [[
                'name_with_initials' => 'Member A',
                'gender' => 'Female',
                'nic_number' => '200012345678',
                'phone_number' => '0712345678',
            ]],
        ])->assertRedirect(route('cerc-farmer-organizations.index', ['tank_type' => 'minor']));

        $organization = CercFarmerOrganization::with('members')->firstOrFail();
        $this->assertSame($asset->id, $organization->cerc_infrastructure_asset_id);
        $this->assertCount(1, $organization->members);
        $this->get(route('cerc-farmer-organizations.show', $organization))->assertOk()->assertSee('CERC Farmers Group')->assertSee('Member A');
        $this->get(route('cerc-farmer-organizations.edit', $organization))->assertOk()->assertSee('Edit Farmer Organization');

        $this->put(route('cerc-assets.update', $asset), [
            'tank_type' => 'minor',
            'tank_name' => 'Updated CERC Tank',
            'cac_members' => [[
                'member_name' => 'Updated CAC Member',
                'position' => 'Co-chairperson',
            ]],
        ])->assertRedirect(route('cerc-assets.index', ['tank_type' => 'minor']));
        $this->assertDatabaseHas('cerc_infrastructure_assets', ['id' => $asset->id, 'tank_name' => 'Updated CERC Tank']);

        $this->put(route('cerc-farmer-organizations.update', $organization), [
            'tank_type' => 'minor',
            'cerc_infrastructure_asset_id' => $asset->id,
            'name' => 'Updated CERC Farmers Group',
            'legal_registration_status' => 'Registered',
            'members' => [['name_with_initials' => 'Updated Member']],
        ])->assertRedirect(route('cerc-farmer-organizations.index', ['tank_type' => 'minor']));
        $this->assertDatabaseHas('cerc_farmer_organizations', ['id' => $organization->id, 'name' => 'Updated CERC Farmers Group']);

        $this->delete(route('cerc-assets.destroy', $asset))
            ->assertRedirect(route('cerc-assets.index', ['tank_type' => 'minor']));
        $this->assertDatabaseMissing('cerc_infrastructure_assets', ['id' => $asset->id]);
        $this->assertDatabaseMissing('cerc_farmer_organizations', ['id' => $organization->id]);
    }
}
