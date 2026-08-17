<?php

namespace Tests\Feature;

use App\Models\ProductivePartnershipEoi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class ProductivePartnershipEoiTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_all_eoi_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('selected-eois.index'));

        $response->assertOk();
        $response->assertSee('Selected All EOI');
        $response->assertSee('All Provinces');
        $response->assertSee('Western');
        $response->assertSee('Sabaragamuwa');
    }

    public function test_eois_can_be_imported_and_moved_to_field_visit(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('eois.xlsx', $this->xlsx([
            ['EOI Number', '2.1 Name of the Organization', '2.2 Number of Members', '2.3 Legal Status', '2.4 Place of Registration', '2.5.1 Registration Number', '2.5.2 Registration Date', '2.6 Name of the Contact Person', '2.7 Designation of the Contact Person', '2.8 Telephone Number of the Contact Person', '2.9 Registered Address of the Organization', '2.10 email Address of the Contact Person', '2.11 Proposed Business Location Address', 'Province', 'District', 'Divisional_Secretariat_DS_Division', '3.1 Title of the Business Proposal', '3.2 Sector', '4.7 Proposed Total Investment (Rs)', '4.7.1 Expected Grant from IRDCRP (Rs.)', '5.1 Completeness of Application with Mandatory Requirement', '5.1.2 Status after Initial Desk Review', '5.1.3 Date of Initial Screening', '_id', '_uuid', '_submission_time', '_validation_status', '_notes', '_status'],
            ['EOI/PP/2026/0001', 'Green Valley FO', '42', 'Registered Society', 'Matale', 'REG-1', '2026-01-15', 'Saman Kumara', 'Chairman', '0712345678', 'Main Road', 'contact@example.com', 'Dambulla', 'Central', 'Matale', 'Dambulla', 'Vegetable Processing', 'Agriculture', '2500000', '1500000', 'Complete', 'Selected', '2026-02-01', '123', 'uuid-1', '2026-02-02 10:30:00', 'validated', '', 'submitted'],
        ]));

        $this->actingAs($user)->post(route('selected-eois.import'), [
            'eoi_file' => $file,
        ])->assertRedirect(route('selected-eois.index'));

        $eoi = ProductivePartnershipEoi::firstOrFail();
        $this->assertSame('Green Valley FO', $eoi->organization_name);
        $this->assertSame(42, $eoi->number_of_members);
        $this->assertSame(2026, $eoi->eoi_year);
        $this->assertNull($eoi->eoi_call_number);

        $this->actingAs($user)->patch(route('selected-eois.initial-stage', $eoi), [
            'initial_stage' => '1',
        ])->assertSessionHas('status');

        $this->assertTrue($eoi->fresh()->initial_stage);
        $this->actingAs($user)->get(route('reviewed-interviews.index'))->assertSee('Green Valley FO');
        $this->actingAs($user)->get(route('field-visits.index'))->assertDontSee('Green Valley FO');
        $this->actingAs($user)->get(route('selected-eois.show', $eoi))->assertSee('Organization Information');
    }

    public function test_eois_can_be_filtered_by_year_and_call_number(): void
    {
        $user = User::factory()->create();

        ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2026/1/131',
            'organization_name' => 'First Call FO',
        ]);

        ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2027/2/131',
            'organization_name' => 'Second Call FO',
        ]);

        ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2027/1/132',
            'organization_name' => 'Another Call FO',
        ]);

        $this->actingAs($user)
            ->get(route('selected-eois.index', [
                'eoi_year' => 2027,
                'eoi_call_number' => 2,
            ]))
            ->assertOk()
            ->assertSee('Second Call FO')
            ->assertSee('Call 2')
            ->assertDontSee('First Call FO')
            ->assertDontSee('Another Call FO');
    }

    public function test_period_summaries_are_shown_from_all_eoi_to_later_stages(): void
    {
        $user = User::factory()->create();

        ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2027/2/131',
            'organization_name' => 'Stage Summary FO',
            'initial_stage' => true,
            'interview_marks' => 78,
            'verification_stage' => true,
            'verification_status' => 'approved',
            'verified_at' => now(),
            'approved_at' => now(),
        ]);

        foreach ([
            route('selected-eois.index'),
            route('reviewed-interviews.index'),
            route('field-visits.index'),
            route('approved-farmer-producer-groups.index'),
            route('full-proposals.index'),
        ] as $url) {
            $this->actingAs($user)
                ->get($url)
                ->assertOk()
                ->assertSee('Year Summary')
                ->assertSee('Call Summary')
                ->assertSee('2027')
                ->assertSee('Call 2')
                ->assertDontSee('Investment Rs.')
                ->assertDontSee('Grant Rs.');
        }
    }

    public function test_eoi_records_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $eoi = ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2026/0002',
            'organization_name' => 'Delete Me FO',
        ]);

        $this->actingAs($user)->delete(route('selected-eois.destroy', $eoi))
            ->assertRedirect(route('selected-eois.index'));

        $this->assertDatabaseMissing('productive_partnership_eois', [
            'eoi_number' => 'EOI/PP/2026/0002',
        ]);
    }

    public function test_template_and_export_are_xlsx_files(): void
    {
        $user = User::factory()->create();
        ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2026/0003',
            'organization_name' => 'Export FO',
        ]);

        $this->actingAs($user)->get(route('selected-eois.template'))
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($user)->get(route('selected-eois.export'))
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_interview_marks_above_fifty_move_eoi_to_approved_groups(): void
    {
        $user = User::factory()->create();
        $eoi = ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2026/0004',
            'organization_name' => 'Approved FO',
            'initial_stage' => true,
        ]);

        $this->actingAs($user)->patch(route('reviewed-interviews.interview-marks', $eoi), [
            'interview_marks' => '72',
            'interview_status' => 'likely_mature',
            'interview_notes' => 'Passed interview',
        ])->assertSessionHas('status');

        $this->assertEquals('72.00', $eoi->fresh()->interview_marks);
        $this->assertSame('likely_mature', $eoi->fresh()->interview_status);
        $this->assertTrue($eoi->fresh()->verification_stage);
        $this->assertSame('pending', $eoi->fresh()->verification_status);
        $this->actingAs($user)->get(route('field-visits.index'))->assertSee('Approved FO');

        $this->actingAs($user)->patch(route('field-visits.verification-status', $eoi), [
            'verification_status' => 'approved',
            'verification_notes' => 'Passed verification field visit',
        ])->assertSessionHas('status');

        $this->assertNotNull($eoi->fresh()->approved_at);
        $this->actingAs($user)->get(route('approved-farmer-producer-groups.index'))->assertSee('Approved FO');
    }

    public function test_resubmit_interview_status_keeps_database_trace_after_status_changes(): void
    {
        $user = User::factory()->create();
        $eoi = ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2026/0007',
            'organization_name' => 'Resubmit Trace FO',
            'initial_stage' => true,
        ]);

        $this->actingAs($user)->patch(route('reviewed-interviews.interview-marks', $eoi), [
            'interview_marks' => '45',
            'interview_status' => 'resubmit',
            'interview_notes' => 'Needs resubmission',
        ])->assertSessionHas('status');

        $resubmittedAt = $eoi->fresh()->interview_resubmitted_at;
        $this->assertSame('resubmit', $eoi->fresh()->interview_status);
        $this->assertNotNull($resubmittedAt);

        $this->actingAs($user)->patch(route('reviewed-interviews.interview-marks', $eoi), [
            'interview_marks' => '68',
            'interview_status' => 'likely_mature',
            'interview_notes' => 'Improved after resubmission',
        ])->assertSessionHas('status');

        $fresh = $eoi->fresh();
        $this->assertSame('likely_mature', $fresh->interview_status);
        $this->assertEquals($resubmittedAt, $fresh->interview_resubmitted_at);
        $this->assertTrue($fresh->verification_stage);
        $this->assertSame(2, $fresh->interviewHistories()->count());

        $this->actingAs($user)->get(route('selected-eois.show', $eoi))
            ->assertSee('Interview Change History')
            ->assertSee('Resubmit')
            ->assertSee('Likely amature')
            ->assertSee('Improved after resubmission');
    }

    public function test_reviewed_interview_page_can_be_filtered_for_summary_reports(): void
    {
        $user = User::factory()->create();

        ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2027/2/201',
            'organization_name' => 'Filtered Mature FO',
            'initial_stage' => true,
            'interview_marks' => 76,
            'interview_status' => 'likely_mature',
            'province' => 'Central',
            'district' => 'Matale',
        ]);

        ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2027/1/202',
            'organization_name' => 'Other Resubmit FO',
            'initial_stage' => true,
            'interview_marks' => 42,
            'interview_status' => 'resubmit',
            'province' => 'Central',
            'district' => 'Kandy',
        ]);

        ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2026/2/203',
            'organization_name' => 'Other Year FO',
            'initial_stage' => true,
            'interview_marks' => 80,
            'interview_status' => 'likely_mature',
            'province' => 'Western',
            'district' => 'Colombo',
        ]);

        $this->actingAs($user)
            ->get(route('reviewed-interviews.index', [
                'eoi_year' => 2027,
                'eoi_call_number' => 2,
                'province' => 'Central',
                'district' => 'Matale',
                'interview_status' => 'likely_mature',
                'next_step' => 'verification',
            ]))
            ->assertOk()
            ->assertSee('Filtered Mature FO')
            ->assertSee('Interview Review Summary')
            ->assertSee('Next Step Summary')
            ->assertDontSee('Other Resubmit FO')
            ->assertDontSee('Other Year FO');
    }

    public function test_field_visit_page_has_report_filters(): void
    {
        $user = User::factory()->create();

        ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2027/2/301',
            'organization_name' => 'Filtered Field Visit FO',
            'initial_stage' => true,
            'interview_marks' => 82,
            'interview_status' => 'likely_mature',
            'verification_stage' => true,
            'verification_status' => 'pending',
            'province' => 'Central',
            'district' => 'Matale',
        ]);

        ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2027/2/302',
            'organization_name' => 'Approved Field Visit FO',
            'initial_stage' => true,
            'interview_marks' => 84,
            'interview_status' => 'likely_mature',
            'verification_stage' => true,
            'verification_status' => 'approved',
            'province' => 'Central',
            'district' => 'Matale',
        ]);

        $this->actingAs($user)
            ->get(route('field-visits.index', [
                'eoi_year' => 2027,
                'eoi_call_number' => 2,
                'province' => 'Central',
                'district' => 'Matale',
                'interview_status' => 'likely_mature',
                'verification_status' => 'pending',
            ]))
            ->assertOk()
            ->assertSee('Filtered Field Visit FO')
            ->assertSee('Interview Review')
            ->assertSee('Verification')
            ->assertDontSee('Approved Field Visit FO');
    }

    public function test_approved_group_documents_can_be_uploaded(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $eoi = ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2026/0005',
            'organization_name' => 'Document FO',
            'initial_stage' => true,
            'interview_marks' => 80,
            'verification_stage' => true,
            'verification_status' => 'approved',
            'verified_at' => now(),
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($user)->patch(route('approved-farmer-producer-groups.update', $eoi), [
            'pre_construction_date' => '2026-07-09',
            'pre_construction_notes' => 'Pre construction data',
            'pre_construction_images' => [
                UploadedFile::fake()->image('pre.jpg'),
            ],
            'during_construction_images' => [
                UploadedFile::fake()->image('during.jpg'),
            ],
            'post_construction_images' => [
                UploadedFile::fake()->image('post.jpg'),
            ],
            'business_registration_image' => UploadedFile::fake()->image('br.jpg'),
            'business_proposal_pdf' => UploadedFile::fake()->create('proposal.pdf', 120, 'application/pdf'),
        ]);

        $response->assertRedirect(route('approved-farmer-producer-groups.index'));

        $fresh = $eoi->fresh();
        $this->assertCount(1, $fresh->pre_construction_images);
        $this->assertNotNull($fresh->business_registration_image);
        $this->assertNotNull($fresh->business_proposal_pdf);

        $this->actingAs($user)->get(route('selected-eois.show', $fresh))
            ->assertSee('Uploaded Construction Photos')
            ->assertSee('Business Registration Image')
            ->assertSee('Business Proposal PDF')
            ->assertSee('Open PDF');
    }

    public function test_approved_group_agreement_tracking_can_be_updated(): void
    {
        $user = User::factory()->create();
        $eoi = ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2026/0008',
            'organization_name' => 'Agreement Tracking FO',
            'initial_stage' => true,
            'interview_marks' => 81,
            'verification_stage' => true,
            'verification_status' => 'approved',
            'verified_at' => now(),
            'approved_at' => now(),
        ]);

        $this->actingAs($user)->patch(route('approved-farmer-producer-groups.agreement-tracking', $eoi), [
            'fop_agreement_tracking' => [
                'draft_proposal_fop' => [
                    'date' => '2026-07-20',
                    'value' => 'Draft proposal dispatched',
                ],
                'agreement_sign' => [
                    'date' => '2026-07-25',
                    'value' => 'Agreement signed',
                ],
            ],
        ])->assertSessionHas('status');

        $fresh = $eoi->fresh();
        $this->assertSame('Draft proposal dispatched', $fresh->fop_agreement_tracking['draft_proposal_fop']['value']);
        $this->assertSame('Agreement Sign', $fresh->currentAgreementTrackingStage()['label']);

        $this->actingAs($user)->get(route('approved-farmer-producer-groups.index'))
            ->assertOk()
            ->assertSee('Agreement Tracking FO')
            ->assertSee('Agreement Sign')
            ->assertSee('Agreement Signed');

        $this->actingAs($user)->get(route('selected-eois.show', $fresh))
            ->assertOk()
            ->assertSee('FOP Agreement Path')
            ->assertSee('Draft proposal dispatched')
            ->assertSee('Agreement signed');
    }

    public function test_full_proposal_status_can_be_approved_or_rejected(): void
    {
        $user = User::factory()->create();
        $eoi = ProductivePartnershipEoi::create([
            'eoi_number' => 'EOI/PP/2026/0006',
            'organization_name' => 'Full Proposal FO',
            'initial_stage' => true,
            'interview_marks' => 76,
            'verification_stage' => true,
            'verification_status' => 'approved',
            'verified_at' => now(),
            'approved_at' => now(),
            'business_proposal_title' => 'Full proposal title',
        ]);

        $this->actingAs($user)->get(route('full-proposals.index'))
            ->assertOk()
            ->assertSee('Full Proposal FO');

        $this->actingAs($user)->patch(route('full-proposals.status', $eoi), [
            'full_proposal_status' => 'approved',
            'full_proposal_notes' => 'Approved after final review',
        ])->assertSessionHas('status');

        $this->assertSame('approved', $eoi->fresh()->full_proposal_status);
        $this->assertNotNull($eoi->fresh()->full_proposal_reviewed_at);
        $this->actingAs($user)->get(route('selected-eois.show', $eoi))
            ->assertSee('Full Proposal Preparation')
            ->assertSee('Approved after final review');
    }

    private function xlsx(array $rows): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test-xlsx-');
        $zip = new ZipArchive;
        $zip->open($tempFile, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml($rows));
        $zip->close();

        $contents = file_get_contents($tempFile);
        unlink($tempFile);

        return $contents;
    }

    private function sheetXml(array $rows): string
    {
        $sheetRows = '';

        foreach ($rows as $rowIndex => $row) {
            $cells = '';

            foreach ($row as $columnIndex => $value) {
                $reference = $this->columnName($columnIndex + 1).($rowIndex + 1);
                $cells .= '<c r="'.$reference.'" t="inlineStr"><is><t>'.htmlspecialchars((string) $value, ENT_XML1).'</t></is></c>';
            }

            $sheetRows .= '<row r="'.($rowIndex + 1).'">'.$cells.'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetRows.'</sheetData></worksheet>';
    }

    private function columnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)).$name;
            $index = intdiv($index, 26);
        }

        return $name;
    }
}
