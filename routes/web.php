<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductivePartnershipEoiController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TankRegistrationController;
use App\Http\Controllers\YouthWomenApplicantController;
use App\Http\Controllers\FarmerOrganizationController;
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

    Route::prefix('/component-1-2/selected-eois')->name('selected-eois.')->controller(ProductivePartnershipEoiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('/import', 'import')->name('import');
        Route::get('/template', 'template')->name('template');
        Route::get('/export', 'export')->name('export');
        Route::get('/{eoi}', 'show')->name('show');
        Route::patch('/{eoi}/initial-stage', 'updateInitialStage')->name('initial-stage');
        Route::delete('/{eoi}', 'destroy')->name('destroy');
    });

    Route::get('/component-1-2/selected-for-reviewed-interview', [ProductivePartnershipEoiController::class, 'reviewedInterviews'])->name('reviewed-interviews.index');
    Route::patch('/component-1-2/selected-for-reviewed-interview/{eoi}/interview-marks', [ProductivePartnershipEoiController::class, 'updateInterviewMarks'])->name('reviewed-interviews.interview-marks');
    Route::patch('/component-1-2/selected-for-verification-field-visit-pass/{eoi}/interview-marks', [ProductivePartnershipEoiController::class, 'updateInterviewMarks'])->name('field-visits.interview-marks');
    Route::get('/component-1-2/selected-for-verification-field-visit-pass', [ProductivePartnershipEoiController::class, 'fieldVisits'])->name('field-visits.index');
    Route::patch('/component-1-2/selected-for-verification-field-visit-pass/{eoi}/verification-status', [ProductivePartnershipEoiController::class, 'updateVerificationStatus'])->name('field-visits.verification-status');
    Route::redirect('/component-1-2/selected-for-field-visit', '/component-1-2/selected-for-verification-field-visit-pass');
    Route::patch('/component-1-2/selected-for-field-visit/{eoi}/interview-marks', [ProductivePartnershipEoiController::class, 'updateInterviewMarks']);
    Route::patch('/component-1-2/selected-for-field-visit/{eoi}/verification-status', [ProductivePartnershipEoiController::class, 'updateVerificationStatus']);
    Route::get('/component-1-2/approved-farmer-producer-groups', [ProductivePartnershipEoiController::class, 'approved'])->name('approved-farmer-producer-groups.index');
    Route::get('/component-1-2/approved-farmer-producer-groups/{eoi}/edit', [ProductivePartnershipEoiController::class, 'editApproved'])->name('approved-farmer-producer-groups.edit');
    Route::patch('/component-1-2/approved-farmer-producer-groups/{eoi}', [ProductivePartnershipEoiController::class, 'updateApproved'])->name('approved-farmer-producer-groups.update');
    Route::patch('/component-1-2/approved-farmer-producer-groups/{eoi}/agreement-tracking', [ProductivePartnershipEoiController::class, 'updateAgreementTracking'])->name('approved-farmer-producer-groups.agreement-tracking');

    Route::get('/component-1-2/selected-for-full-proposal-preparation', [ProductivePartnershipEoiController::class, 'fullProposals'])->name('full-proposals.index');
    Route::patch('/component-1-2/selected-for-full-proposal-preparation/{eoi}/status', [ProductivePartnershipEoiController::class, 'updateFullProposalStatus'])->name('full-proposals.status');
    Route::get('/component-1-2/agreement-sign-fop', [ProductivePartnershipEoiController::class, 'agreementSignFop'])->name('agreement-sign-fop.index');
    Route::patch('/component-1-2/agreement-sign-fop/{eoi}/data', [ProductivePartnershipEoiController::class, 'updateAgreementEoiData'])->name('agreement-sign-fop.data');
    Route::get('/component-1-2/agreement-sign-fop/{eoi}/documents/{document}', [ProductivePartnershipEoiController::class, 'downloadAgreementDocument'])->name('agreement-sign-fop.document');
    Route::get('/component-1-2/agreement-sign-fop/{eoi}/print/{stage?}', [\App\Http\Controllers\AgreementPrintController::class, 'farmer'])->name('agreement-sign-fop.print');

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
