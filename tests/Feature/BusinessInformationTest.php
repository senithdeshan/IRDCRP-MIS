<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\YouthWomenApplicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BusinessInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_information_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('business-information.index'));

        $response->assertOk();
        $response->assertSee('Business Information');
    }

    public function test_youth_women_component_overview_can_be_rendered(): void
    {
        $user = User::factory()->create();

        YouthWomenApplicant::create([
            'eoi_number' => 'EOI/YW/2026/0009',
            'applicant_name' => 'Individual Entrepreneur',
            'gender' => 'Female',
            'nic' => '200012345678',
            'telephone' => '0712345678',
            'business_name' => 'Individual Business',
            'legal_status' => 'Proprietor',
            'province' => 'Central',
            'district' => 'Matale',
            'initial_screening_result' => 'Selected',
            'proposed_total_investment' => 750000,
        ]);

        $response = $this->actingAs($user)->get(route('youth-women.index'));

        $response->assertOk();
        $response->assertSee('Youth and Women Entrepreneurs');
        $response->assertSee('Individual Workflow');
        $response->assertSee('Individual Entrepreneur');
        $response->assertSee('Selected Entrepreneurs');
    }

    public function test_applicants_can_be_imported_from_csv(): void
    {
        $user = User::factory()->create();
        $csv = implode("\n", [
            'Project Reference,EOI Number,Applicant Name,Gender,NIC,Date of Birth,Age as at 2026-01-01,Telephone,WhatsApp,Email,Business Name,Legal Status,Business Registered Address,Province,District,DS Division,Business Registration Number,Business Registration Date,Business Sectors,Proposed Total Investment,Initial Screening Result',
            'IRDCRP-C1.3,EOI/YW/2026/1,Test Applicant,Female,200012345678,2000-01-15,25,0712345678,0712345678,test@example.com,Test Business,Proprietor,No 1,Central,Matale,Dambulla,BR-1,2024-01-01,Agriculture; Food & Beverages,1500000,Selected',
        ]);

        $file = UploadedFile::fake()->createWithContent('applicants.csv', $csv);

        $response = $this->actingAs($user)->post(route('business-information.import'), [
            'applicant_file' => $file,
        ]);

        $response->assertRedirect(route('business-information.index'));
        $this->assertDatabaseHas('youth_women_applicants', [
            'eoi_number' => 'EOI/YW/2026/0001',
            'applicant_name' => 'Test Applicant',
            'initial_screening_result' => 'Selected',
        ]);
        $this->assertSame(['Agriculture', 'Food & Beverages'], YouthWomenApplicant::first()->business_sectors);
    }
}
