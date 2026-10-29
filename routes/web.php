<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductivePartnershipEoiController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffProfileController;
use App\Http\Controllers\TankRegistrationController;
use App\Http\Controllers\YouthWomenApplicantController;
use App\Http\Controllers\FarmerOrganizationController;
use App\Http\Controllers\IrrigationInfrastructureAssetController;
use App\Http\Controllers\IrrigationFarmerOrganizationController;
use App\Http\Controllers\IrrigationInfrastructureExcelController;
use App\Http\Controllers\CercInfrastructureAssetController;
use App\Http\Controllers\CercInfrastructureExcelController;
use App\Http\Controllers\CercFarmerOrganizationController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return view('dashboard', [
        'staffCount' => User::count(),
        'activeStaffCount' => User::where('status', 'active')->count(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/staff-profiles', [StaffProfileController::class, 'index'])->name('staff-profiles.index');
    Route::get('/staff-profiles/create', [StaffProfileController::class, 'create'])->name('staff-profiles.create');
    Route::post('/staff-profiles', [StaffProfileController::class, 'store'])->name('staff-profiles.store');
    Route::get('/staff-profiles/{staffProfile}', [StaffProfileController::class, 'show'])->name('staff-profiles.show');
    Route::get('/staff-profiles/{staffProfile}/edit', [StaffProfileController::class, 'edit'])->name('staff-profiles.edit');
    Route::put('/staff-profiles/{staffProfile}', [StaffProfileController::class, 'update'])->name('staff-profiles.update');
    Route::delete('/staff-profiles/{staffProfile}', [StaffProfileController::class, 'destroy'])->name('staff-profiles.destroy');
    Route::get('/staff-profiles/{staffProfile}/documents/{type}', [StaffProfileController::class, 'document'])->name('staff-profiles.document');

    Route::resource('staff', StaffController::class)->except(['show', 'destroy']);

    Route::view('/projects', 'modules.coming-soon', [
        'title' => 'Projects',
        'eyebrow' => 'Program operations',
        'description' => 'Project tracking, milestones, funding updates, and field progress will live here.',
    ])->name('projects.index');

    Route::view('/beneficiaries', 'modules.coming-soon', [
        'title' => 'Beneficiaries',
        'eyebrow' => 'Community records',
        'description' => 'Beneficiary profiles, eligibility details, support history, and verification workflows will live here.',
    ])->name('beneficiaries.index');

    Route::view('/activities', 'modules.coming-soon', [
        'title' => 'Activities',
        'eyebrow' => 'Field execution',
        'description' => 'Training sessions, site visits, distributions, and team activity logs will live here.',
    ])->name('activities.index');

    Route::view('/reports', 'modules.coming-soon', [
        'title' => 'Reports',
        'eyebrow' => 'Insights and exports',
        'description' => 'Printable reports, Excel exports, and management summaries will live here.',
    ])->name('reports.index');

    Route::get('/component-1-2/productive-partnership', [FarmerOrganizationController::class, 'overview'])->name('productive-partnership.index');
    Route::prefix('/component-1-2/farmer-organization-information')->name('farmer-organizations.')->controller(FarmerOrganizationController::class)->group(function () {
        Route::get('/', 'overview')->name('index');
        Route::get('/records', 'records')->name('records');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/template', 'template')->name('template');
        Route::post('/import', 'import')->name('import');
        Route::get('/{eoi}/edit', 'edit')->name('edit');
        Route::patch('/{eoi}', 'update')->name('update');
    });

    Route::prefix('/component-1-2/received-all-eoi')->name('selected-eois.')->controller(ProductivePartnershipEoiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::get('/{eoi}/edit', 'editEntry')->name('edit');
        Route::post('/', 'store')->name('store');
        Route::patch('/{eoi}', 'updateEntry')->name('update');
        Route::post('/import', 'import')->name('import');
        Route::get('/template', 'template')->name('template');
        Route::get('/export', 'export')->name('export');
        Route::get('/{eoi}', 'show')->name('show');
        Route::patch('/{eoi}/initial-stage', 'updateInitialStage')->name('initial-stage');
        Route::delete('/{eoi}', 'destroy')->name('destroy');
    });
    Route::redirect('/component-1-2/selected-eois', '/component-1-2/received-all-eoi');

    Route::get('/component-1-2/selected-for-review-interview', [ProductivePartnershipEoiController::class, 'reviewedInterviews'])->name('reviewed-interviews.index');
    Route::redirect('/component-1-2/selected-for-reviewed-interview', '/component-1-2/selected-for-review-interview');
    Route::patch('/component-1-2/selected-for-reviewed-interview/{eoi}/interview-marks', [ProductivePartnershipEoiController::class, 'updateInterviewMarks'])->name('reviewed-interviews.interview-marks');
    Route::patch('/component-1-2/selected-for-verification-field-visit/{eoi}/interview-marks', [ProductivePartnershipEoiController::class, 'updateInterviewMarks'])->name('field-visits.interview-marks');
    Route::get('/component-1-2/selected-for-verification-field-visit', [ProductivePartnershipEoiController::class, 'fieldVisits'])->name('field-visits.index');
    Route::patch('/component-1-2/selected-for-verification-field-visit/{eoi}/verification-status', [ProductivePartnershipEoiController::class, 'updateVerificationStatus'])->name('field-visits.verification-status');
    Route::redirect('/component-1-2/selected-for-verification-field-visit-pass', '/component-1-2/selected-for-verification-field-visit');
    Route::redirect('/component-1-2/selected-for-field-visit', '/component-1-2/selected-for-verification-field-visit');
    Route::patch('/component-1-2/selected-for-field-visit/{eoi}/interview-marks', [ProductivePartnershipEoiController::class, 'updateInterviewMarks']);
    Route::patch('/component-1-2/selected-for-field-visit/{eoi}/verification-status', [ProductivePartnershipEoiController::class, 'updateVerificationStatus']);
    Route::get('/component-1-2/selected-for-proposal-preparation', [ProductivePartnershipEoiController::class, 'approved'])->name('approved-farmer-producer-groups.index');
    Route::redirect('/component-1-2/approved-farmer-producer-groups', '/component-1-2/selected-for-proposal-preparation');
    Route::get('/component-1-2/selected-for-proposal-preparation/{eoi}/edit', [ProductivePartnershipEoiController::class, 'editApproved'])->name('approved-farmer-producer-groups.edit');
    Route::patch('/component-1-2/selected-for-proposal-preparation/{eoi}', [ProductivePartnershipEoiController::class, 'updateApproved'])->name('approved-farmer-producer-groups.update');
    Route::patch('/component-1-2/selected-for-proposal-preparation/{eoi}/agreement-tracking', [ProductivePartnershipEoiController::class, 'updateAgreementTracking'])->name('approved-farmer-producer-groups.agreement-tracking');

    Route::redirect('/component-1-2/selected-for-full-proposal-preparation', '/component-1-2/selected-for-proposal-preparation')->name('full-proposals.index');
    Route::get('/component-1-2/agreement-signed-fpo', [ProductivePartnershipEoiController::class, 'agreementSignFop'])->name('agreement-sign-fop.index');
    Route::redirect('/component-1-2/agreement-sign-fop', '/component-1-2/agreement-signed-fpo');
    Route::patch('/component-1-2/agreement-signed-fpo/{eoi}/data', [ProductivePartnershipEoiController::class, 'updateAgreementEoiData'])->name('agreement-sign-fop.data');
    Route::get('/component-1-2/agreement-signed-fpo/{eoi}/documents/{document}', [ProductivePartnershipEoiController::class, 'downloadAgreementDocument'])->name('agreement-sign-fop.document');
    Route::get('/component-1-2/agreement-signed-fpo/{eoi}/print/{stage?}', [\App\Http\Controllers\AgreementPrintController::class, 'farmer'])->name('agreement-sign-fop.print');

    Route::get('/component-1-3/youth-women-entrepreneurs', [YouthWomenApplicantController::class, 'overview'])->name('youth-women.index');
    Route::get('/component-1-3/received-eois', [YouthWomenApplicantController::class, 'index'])->name('individual-eois.index');
    Route::patch('/component-1-3/received-eois/{applicant}/initial-stage', [YouthWomenApplicantController::class, 'updateInitialStage'])->name('individual-eois.initial-stage');

    foreach ([
        'reviewed-interviews' => 'selected-for-reviewed-interview',
        'field-visits' => 'selected-for-verification-field-visit-pass',
        'approved' => 'approved-individual-entrepreneurs',
        'full-proposals' => 'selected-for-full-proposal-preparation',
        'agreements' => 'agreement-sign',
    ] as $stage => $path) {
        Route::get('/component-1-3/'.$path, [\App\Http\Controllers\IndividualWorkflowController::class, 'index'])
            ->defaults('stage', $stage)->name('individual-workflow.'.$stage);
    }
    Route::patch('/component-1-3/individual-workflow/{applicant}', [\App\Http\Controllers\IndividualWorkflowController::class, 'advance'])->name('individual-workflow.advance');
    Route::patch('/component-1-3/individual-agreements/{eoi}/data', [\App\Http\Controllers\IndividualAgreementController::class, 'updateAgreementEoiData'])->name('individual-agreement.data');
    Route::get('/component-1-3/individual-agreements/{eoi}/documents/{document}', [\App\Http\Controllers\IndividualAgreementController::class, 'downloadAgreementDocument'])->name('individual-agreement.document');
    Route::get('/component-1-3/individual-agreements/{eoi}/print/{stage?}', [\App\Http\Controllers\AgreementPrintController::class, 'individual'])->name('individual-agreement.print');
    Route::view('/component-2', 'modules.coming-soon', [
        'title' => 'Component 2',
        'eyebrow' => 'Program component',
        'description' => 'Main workspace for cascade and tank registration workflows will be developed here.',
    ])->name('component-two.index');

    Route::view('/component-2/cascade-registration', 'cascade-registration.index')->name('cascade-registration.index');

    Route::get('/irrigation-infrastructure-assets/template', [IrrigationInfrastructureExcelController::class, 'template'])->name('irrigation-assets.template');
    Route::get('/irrigation-infrastructure-assets/export', [IrrigationInfrastructureExcelController::class, 'export'])->name('irrigation-assets.export');
    Route::post('/irrigation-infrastructure-assets/import', [IrrigationInfrastructureExcelController::class, 'import'])->name('irrigation-assets.import');
    Route::get('/irrigation-infrastructure-assets', [IrrigationInfrastructureAssetController::class, 'overview'])
        ->name('irrigation-assets.overview');

    Route::resource('component-2/irrigation-infrastructure-assets', IrrigationInfrastructureAssetController::class)
        ->except(['destroy'])
        ->names('irrigation-assets');
    Route::delete('/component-2/irrigation-infrastructure-assets/{irrigation_infrastructure_asset}', [IrrigationInfrastructureAssetController::class, 'destroy'])
        ->name('irrigation-assets.destroy');

    Route::resource('irrigation-farmer-organizations', IrrigationFarmerOrganizationController::class)
        ->names('irrigation-farmer-organizations');

    Route::get('/cerc', [CercInfrastructureAssetController::class, 'overview'])->name('cerc.index');
    Route::get('/cerc-assets/template', [CercInfrastructureExcelController::class, 'template'])->name('cerc-assets.template');
    Route::get('/cerc-assets/export', [CercInfrastructureExcelController::class, 'export'])->name('cerc-assets.export');
    Route::post('/cerc-assets/import', [CercInfrastructureExcelController::class, 'import'])->name('cerc-assets.import');
    Route::resource('cerc-assets', CercInfrastructureAssetController::class)
        ->except(['destroy'])
        ->names('cerc-assets')
        ->parameters(['cerc-assets' => 'cercInfrastructureAsset']);
    Route::delete('/cerc-assets/{cercInfrastructureAsset}', [CercInfrastructureAssetController::class, 'destroy'])
        ->name('cerc-assets.destroy');
    Route::resource('cerc-farmer-organizations', CercFarmerOrganizationController::class)
        ->names('cerc-farmer-organizations');

    Route::prefix('/component-2/tank-registration')->name('tank-registration.')->controller(TankRegistrationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('/import', 'import')->name('import');
        Route::get('/template', 'template')->name('template');
        Route::get('/export', 'export')->name('export');
        Route::get('/{tankRegistration}', 'show')->name('show');
        Route::get('/{tankRegistration}/edit', 'edit')->name('edit');
        Route::patch('/{tankRegistration}', 'update')->name('update');
        Route::delete('/{tankRegistration}', 'destroy')->name('destroy');
    });

    Route::prefix('/component-1-3/business-information')->name('business-information.')->controller(YouthWomenApplicantController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('/import', 'import')->name('import');
        Route::get('/template', 'template')->name('template');
        Route::get('/{businessInformation}/edit', 'edit')->name('edit');
        Route::patch('/{businessInformation}', 'update')->name('update');
    });

    Route::view('/settings', 'modules.coming-soon', [
        'title' => 'Settings',
        'eyebrow' => 'System control',
        'description' => 'Organization settings, master data, audit rules, and workflow configuration will live here.',
    ])->name('settings.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
