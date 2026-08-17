<?php

namespace Tests\Feature;

use App\Models\TankRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class TankRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tank_registration_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tank-registration.index'));

        $response->assertOk();
        $response->assertSee('Tank Registration');
        $response->assertSee('Add Tank');
    }

    public function test_tank_registration_show_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $tank = TankRegistration::create([
            'tank_id' => 'TANK-0042',
            'tank_name' => 'View Wewa',
            'progress' => 62.5,
            'latitude' => 7.8731,
            'longitude' => 80.7718,
            'grand_total' => 1200000,
        ]);

        $response = $this->actingAs($user)->get(route('tank-registration.show', $tank));

        $response->assertOk();
        $response->assertSee('View Wewa');
        $response->assertSee('Physical Progress');
        $response->assertSee('Print Report');
        $response->assertSee('Location Map');
    }

    public function test_tank_record_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tank-registration.store'), [
            'tank_id' => 'TANK-0099',
            'tank_name' => 'Test Wewa',
            'river_basin' => 'Malwathu Oya',
            'cascade_name' => 'Test Cascade',
            'province' => 'North Central',
            'district' => 'Anuradhapura',
            'ds_division' => 'Nochchiyagama',
            'gn_division' => 'Test GN Division',
            'as_centre' => 'Test ASC',
            'agency' => 'Department of Agrarian Development',
            'no_of_family' => 72,
            'longitude' => 80.1234567,
            'latitude' => 8.1234567,
            'progress' => 35,
            'contractor' => 'ABC Construction',
            'contractor_address' => '123 Canal Road, Anuradhapura',
            'contractor_contact_number' => '0712345678',
            'contractor_cida_grade' => 'C4',
            'construction_start_date' => '2026-08-10',
            'payment' => 'Advance Paid',
            'awarded_date' => '2026-08-01',
            'construction_period_days' => 180,
            'extension_of_time_months' => 2,
            'status' => 'Ongoing',
            'remarks' => 'Test remarks',
            'open_ref_no' => 'OPEN/2026/099',
            'cumulative_amount' => 2500000,
            'paid_advanced_amount' => 500000,
            'recommended_ipc_no' => 'IPC-01',
            'recommended_ipc_amount' => 750000,
            'base_cost' => 6000000,
            'physical_contingencies' => 300000,
            'price_contingencies' => 250000,
            'net_value' => 6550000,
            'vat' => 1179000,
            'grand_total' => 7729000,
        ]);

        $response->assertRedirect(route('tank-registration.index'));
        $this->assertDatabaseHas('tank_registrations', [
            'tank_id' => 'TANK-0099',
            'tank_name' => 'Test Wewa',
            'contractor_contact_number' => '0712345678',
            'contractor_cida_grade' => 'C4',
            'status' => 'Ongoing',
            'grand_total' => 7729000,
        ]);
    }

    public function test_tank_records_can_be_imported_from_excel(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('tanks.xlsx', $this->xlsx([
            ['Tank Id', 'Tank Name', 'River Basin', 'Cascade Name', 'Province', 'District', 'DS Division', 'GN Division', 'AS Centre', 'Agency', 'No. of Family', 'Longitude', 'Latitude', 'Progress', 'Contractor', 'Contractor Address', 'Contractor Contact Number', 'Contractor CIDA Grade', 'Construction Start Date', 'Payment', 'Awarded Date', 'Construction Period (Days)', 'Extension of Time(EOT)(months)', 'Status', 'Remarks', 'Open Ref No', 'Cumulative Amount', 'Paid Advanced Amount', 'Recommended IPC No', 'Recommended IPC Amount', 'Base Cost', 'Physical Contingencies', 'Price Contingencies', 'Net Value', 'VAT', 'Grand Total'],
            ['7', 'Imported Wewa', 'Malwathu Oya', 'Sample Cascade', 'North Central', 'Anuradhapura', 'Nochchiyagama', 'Sample GND', 'Sample ASC', 'Department of Agrarian Development', '148', '80.1234567', '8.1234567', '45', 'ABC Construction', 'Tank contractor address', '0771234567', 'C3', '2026-08-05', 'Advance Paid', '2026-08-01', '180', '2', 'Ongoing', 'Imported record', 'OPEN/2026/001', '2500000', '500000', 'IPC-01', '750000', '6000000', '300000', '250000', '6550000', '1179000', '7729000'],
        ]));

        $response = $this->actingAs($user)->post(route('tank-registration.import'), [
            'tank_file' => $file,
        ]);

        $response->assertRedirect(route('tank-registration.index'));
        $this->assertDatabaseHas('tank_registrations', [
            'tank_id' => 'TANK-0007',
            'tank_name' => 'Imported Wewa',
            'no_of_family' => 148,
            'contractor_address' => 'Tank contractor address',
            'contractor_contact_number' => '0771234567',
            'contractor_cida_grade' => 'C3',
            'construction_start_date' => '2026-08-05 00:00:00',
            'recommended_ipc_no' => 'IPC-01',
        ]);
    }

    public function test_real_bulk_upload_style_excel_row_can_be_imported(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('real-tanks.xlsx', $this->xlsx([
            ['Tank Id', 'Tank Name', 'River Basin', 'Cascade Name', 'Province', 'District', 'DS Division', 'GN Division', 'AS Centre', 'Agency', 'No. of Family', 'Longitude', 'Latitude', 'Progress', 'Contractor', 'Payment', 'Awarded Date', 'Construction Period (Days)', 'Extension of Time(EOT)(months)', 'Status', 'Remarks', 'Open Ref No', 'Cumulative Amount', 'Paid Advanced Amount', 'Recommended IPC No', 'Recommended IPC Amount', 'Base Cost', 'Physical Contingencies', 'Price Contingencies', 'Net Value', 'VAT', 'Grand Total'],
            ['1', 'Heenuk Wewa', 'Mi Oya', 'Gampola Maha Wewa', 'North Western Province', 'Kurunegala', 'Giribawa', '32-Aliyawatunuwewa', 'Thambuththa', 'DAD', '30', '616831', '437986', '', 'Resus Engineering (Pvt) Ltd', '', '2026-05-22', '180', '', 'Contract awareded', '', '', '', '', '', '', '15386370', '1153977.75', '769318.5', '17309666.25', '3115739.925', '20425406.175'],
        ]));

        $response = $this->actingAs($user)->post(route('tank-registration.import'), [
            'tank_file' => $file,
        ]);

        $response->assertRedirect(route('tank-registration.index'));
        $this->assertDatabaseHas('tank_registrations', [
            'tank_id' => 'TANK-0001',
            'tank_name' => 'Heenuk Wewa',
            'longitude' => 616831,
            'latitude' => 437986,
            'status' => 'Contract awareded',
        ]);
    }

    public function test_csv_files_are_rejected_for_tank_imports(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('tanks.csv', 'Tank Id,Tank Name');

        $response = $this->actingAs($user)->post(route('tank-registration.import'), [
            'tank_file' => $file,
        ]);

        $response->assertSessionHasErrors('tank_file');
    }

    public function test_tank_record_can_be_updated_and_deleted(): void
    {
        $user = User::factory()->create();
        $tank = TankRegistration::create([
            'tank_id' => 'TANK-0010',
            'tank_name' => 'Original Wewa',
        ]);

        $update = $this->actingAs($user)->patch(route('tank-registration.update', $tank), [
            'tank_id' => 'TANK-0010',
            'tank_name' => 'Updated Wewa',
            'progress' => 100,
            'payment' => 'Fully Paid',
            'status' => 'Completed',
            'grand_total' => 4500000,
        ]);

        $update->assertRedirect(route('tank-registration.index'));
        $this->assertDatabaseHas('tank_registrations', [
            'tank_id' => 'TANK-0010',
            'tank_name' => 'Updated Wewa',
            'status' => 'Completed',
            'grand_total' => 4500000,
        ]);

        $delete = $this->actingAs($user)->delete(route('tank-registration.destroy', $tank));

        $delete->assertRedirect(route('tank-registration.index'));
        $this->assertDatabaseMissing('tank_registrations', [
            'tank_id' => 'TANK-0010',
        ]);
    }

    public function test_construction_images_can_be_uploaded_on_update(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $tank = TankRegistration::create([
            'tank_id' => 'TANK-0030',
            'tank_name' => 'Image Wewa',
        ]);

        $response = $this->actingAs($user)->patch(route('tank-registration.update', $tank), [
            'tank_id' => 'TANK-0030',
            'tank_name' => 'Image Wewa',
            'pre_construction_images' => [UploadedFile::fake()->image('pre.jpg')],
            'during_construction_images' => [UploadedFile::fake()->image('during.jpg')],
            'post_construction_images' => [UploadedFile::fake()->image('post.jpg')],
        ]);

        $response->assertRedirect(route('tank-registration.index'));

        $tank->refresh();

        $this->assertCount(1, $tank->pre_construction_images);
        $this->assertCount(1, $tank->during_construction_images);
        $this->assertCount(1, $tank->post_construction_images);

        Storage::disk('public')->assertExists($tank->pre_construction_images[0]);
        Storage::disk('public')->assertExists($tank->during_construction_images[0]);
        Storage::disk('public')->assertExists($tank->post_construction_images[0]);
    }

    public function test_template_and_export_are_excel_files(): void
    {
        $user = User::factory()->create();

        TankRegistration::create([
            'tank_id' => 'TANK-0020',
            'tank_name' => 'Export Wewa',
        ]);

        $template = $this->actingAs($user)->get(route('tank-registration.template'));
        $export = $this->actingAs($user)->get(route('tank-registration.export'));

        $template->assertOk();
        $template->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment; filename=tank-registration-template.xlsx', $template->headers->get('content-disposition'));

        $export->assertOk();
        $export->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment; filename=tank-registration-export.xlsx', $export->headers->get('content-disposition'));
    }

    private function xlsx(array $rows): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'tank-test-xlsx-');
        $zip = new ZipArchive;
        $zip->open($tempFile, ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets><sheet name="Tank Registration" sheetId="1" r:id="rId1"/></sheets>
</workbook>');
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
            $cellXml = '';

            foreach ($row as $columnIndex => $value) {
                $reference = $this->columnName($columnIndex + 1).($rowIndex + 1);
                $cellXml .= '<c r="'.$reference.'" t="inlineStr"><is><t>'.htmlspecialchars((string) $value, ENT_XML1).'</t></is></c>';
            }

            $sheetRows .= '<row r="'.($rowIndex + 1).'">'.$cellXml.'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetRows.'</sheetData></worksheet>';
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
