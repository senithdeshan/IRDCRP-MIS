<?php

namespace App\Http\Controllers;

use App\Models\CercInfrastructureAsset;
use App\Models\TankRegistration;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CercInfrastructureAssetController extends Controller
{
    private const IMAGE_FIELDS = [
        'before_work_photo' => 'before_work_photo_path',
        'current_photo_1' => 'current_photo_1_path',
        'current_photo_2' => 'current_photo_2_path',
        'contractor_board_photo' => 'contractor_board_photo_path',
    ];

    private function divisions(): array
    {
        return config('admin_divisions.provinces', []);
    }

    public function overview(): View
    {
        return view('cerc-assets.overview', [
            'schemeCount' => CercInfrastructureAsset::where('tank_type', 'minor')->count(),
            'minorSchemeCount' => CercInfrastructureAsset::where('tank_type', 'minor')->count(),
            'majorSchemeCount' => 0,
        ]);
    }

    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;
        $baseQuery = CercInfrastructureAsset::query()->where('tank_type', 'minor');
        $query = clone $baseQuery;

        foreach (['province', 'district', 'contract_status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter)->toString());
            }
        }
        if ($request->filled('search')) {
            $term = '%'.$request->string('search')->toString().'%';
            $query->where(function ($builder) use ($term) {
                $builder->where('tank_name', 'like', $term)
                    ->orWhere('contract_number', 'like', $term)
                    ->orWhere('contractor_name', 'like', $term);
            });
        }

        return view('cerc-assets.index', [
            'tankType' => 'minor',
            'assets' => $query->latest()->paginate($perPage)->appends($request->query()),
            'perPage' => $perPage,
            'summary' => [
                'total' => (clone $baseQuery)->count(),
                'in_progress' => (clone $baseQuery)->where('progress', '>', 0)->where('progress', '<', 100)->count(),
                'completed' => (clone $baseQuery)->where('progress', '>=', 100)->count(),
            ],
            'provinces' => (clone $baseQuery)->whereNotNull('province')->where('province', '<>', '')->distinct()->orderBy('province')->pluck('province'),
            'districts' => (clone $baseQuery)->when($request->filled('province'), fn ($builder) => $builder->where('province', $request->string('province')->toString()))->whereNotNull('district')->where('district', '<>', '')->distinct()->orderBy('district')->pluck('district'),
            'contractStatuses' => (clone $baseQuery)->whereNotNull('contract_status')->where('contract_status', '<>', '')->distinct()->orderBy('contract_status')->pluck('contract_status'),
        ]);
    }

    public function create(): View
    {
        $asset = new CercInfrastructureAsset(['tank_type' => 'minor']);
        return view('cerc-assets.form', [
            'asset' => $asset,
            'editing' => false,
            'administrativeDivisions' => $this->divisions(),
            'locationPoint' => $this->locationPoint($asset),
        ]);
    }

    public function show(CercInfrastructureAsset $cercInfrastructureAsset): View
    {
        abort_unless($cercInfrastructureAsset->tank_type === 'minor', 404);

        $mapRecord = new TankRegistration([
            'tank_name' => $cercInfrastructureAsset->tank_name,
            'province' => $cercInfrastructureAsset->province,
            'district' => $cercInfrastructureAsset->district,
            'latitude' => $cercInfrastructureAsset->latitude,
            'longitude' => $cercInfrastructureAsset->longitude,
        ]);

        return view('cerc-assets.show', [
            'asset' => $cercInfrastructureAsset,
            'locationPoint' => $mapRecord->sriLankaMapPoint(),
        ]);
    }

    public function pdf(CercInfrastructureAsset $cercInfrastructureAsset)
    {
        abort_unless($cercInfrastructureAsset->tank_type === 'minor', 404);

        $photos = [];
        foreach (self::IMAGE_FIELDS as $label => $column) {
            $path = $cercInfrastructureAsset->{$column};
            $absolutePath = $path ? Storage::disk('public')->path($path) : null;
            $photos[$label] = $absolutePath && is_file($absolutePath)
                ? 'data:'.(mime_content_type($absolutePath) ?: 'image/jpeg').';base64,'.base64_encode(file_get_contents($absolutePath))
                : null;
        }

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('cerc-assets.pdf', ['asset' => $cercInfrastructureAsset, 'photos' => $photos])->render(), 'UTF-8');
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.\Illuminate\Support\Str::slug($cercInfrastructureAsset->tank_name ?: 'minor-tank-scheme').'-details.pdf"',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->storePhotos($request, $data);
        $data['tank_type'] = 'minor';
        $data['created_by'] = $request->user()->id;
        $asset = CercInfrastructureAsset::create($data);

        return redirect()->route('cerc-assets.show', $asset)->with('status', 'Minor Tank scheme registered successfully.');
    }

    public function edit(CercInfrastructureAsset $cercInfrastructureAsset): View
    {
        abort_unless($cercInfrastructureAsset->tank_type === 'minor', 404);

        return view('cerc-assets.form', [
            'asset' => $cercInfrastructureAsset,
            'editing' => true,
            'administrativeDivisions' => $this->divisions(),
            'locationPoint' => $this->locationPoint($cercInfrastructureAsset),
        ]);
    }

    public function update(Request $request, CercInfrastructureAsset $cercInfrastructureAsset): RedirectResponse
    {
        abort_unless($cercInfrastructureAsset->tank_type === 'minor', 404);
        $data = $this->validated($request);
        $oldPhotos = [];
        foreach (self::IMAGE_FIELDS as $input => $column) {
            unset($data[$input]);
            if ($request->hasFile($input)) {
                $oldPhotos[] = $cercInfrastructureAsset->{$column};
                $data[$column] = $request->file($input)->store('cerc/minor-tank', 'public');
            }
        }
        $cercInfrastructureAsset->update($data);
        foreach (array_filter($oldPhotos) as $oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()->route('cerc-assets.show', $cercInfrastructureAsset)->with('status', 'Minor Tank scheme updated successfully.');
    }

    public function destroy(CercInfrastructureAsset $cercInfrastructureAsset): RedirectResponse
    {
        abort_unless($cercInfrastructureAsset->tank_type === 'minor', 404);
        foreach (self::IMAGE_FIELDS as $column) {
            if ($cercInfrastructureAsset->{$column}) {
                Storage::disk('public')->delete($cercInfrastructureAsset->{$column});
            }
        }
        $cercInfrastructureAsset->delete();

        return redirect()->route('cerc-assets.index')->with('status', 'Minor Tank scheme deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'progress' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:5.85,9.95'],
            'longitude' => ['nullable', 'numeric', 'between:79.45,82.05'],
            'before_work_photo' => ['nullable', 'image', 'max:10240'],
            'current_photo_1' => ['nullable', 'image', 'max:10240'],
            'current_photo_2' => ['nullable', 'image', 'max:10240'],
            'contractor_board_photo' => ['nullable', 'image', 'max:10240'],
            'institution' => ['nullable', 'string', 'max:255'],
            'programme_type' => ['nullable', 'string', 'max:255'],
            'contract_status' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ds_division' => ['nullable', 'string', 'max:255'],
            'asc' => ['nullable', 'string', 'max:255'],
            'tank_name' => ['required', 'string', 'max:255'],
            'civil_cost' => ['nullable', 'numeric', 'min:0'],
            'preliminaries_cost' => ['nullable', 'numeric', 'min:0'],
            'direct_cost' => ['nullable', 'numeric', 'min:0'],
            'vat_18' => ['nullable', 'numeric', 'min:0'],
            'contingencies_cost' => ['nullable', 'numeric', 'min:0'],
            'total_cost_estimate' => ['nullable', 'numeric', 'min:0'],
            'awarded_civil_cost' => ['nullable', 'numeric', 'min:0'],
            'awarded_preliminaries_cost' => ['nullable', 'numeric', 'min:0'],
            'awarded_amount_without_vat_contingencies' => ['nullable', 'numeric', 'min:0'],
            'advance_imprest' => ['nullable', 'numeric', 'min:0'],
            'provisional_sum' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2200'],
            'size' => ['nullable', 'string', 'max:255'],
            'beneficiary_families' => ['nullable', 'integer', 'min:0'],
            'command_area_ac' => ['nullable', 'numeric', 'min:0'],
            'lot' => ['nullable', 'string', 'max:255'],
            'cluster' => ['nullable', 'string', 'max:255'],
            'date_of_award' => ['nullable', 'date'],
            'agreement_sign_date' => ['nullable', 'date'],
            'contract_period_days' => ['nullable', 'integer', 'min:0'],
            'completion_date' => ['nullable', 'date'],
            'contract_number' => ['nullable', 'string', 'max:255'],
            'contractor_name' => ['nullable', 'string', 'max:255'],
            'contractor_address' => ['nullable', 'string', 'max:2000'],
            'contractor_contact_number' => ['nullable', 'string', 'max:50'],
            'emp_clearance_received_date' => ['nullable', 'date'],
            'smp_clearance_received_date' => ['nullable', 'date'],
            'esmp_submitted_to_procurement_date' => ['nullable', 'date'],
        ]);
    }

    private function storePhotos(Request $request, array &$data): void
    {
        foreach (self::IMAGE_FIELDS as $input => $column) {
            unset($data[$input]);
            if ($request->hasFile($input)) {
                $data[$column] = $request->file($input)->store('cerc/minor-tank', 'public');
            }
        }
    }

    private function locationPoint(CercInfrastructureAsset $asset): ?array
    {
        $mapRecord = new TankRegistration([
            'tank_name' => $asset->tank_name,
            'province' => $asset->province,
            'district' => $asset->district,
            'latitude' => $asset->latitude,
            'longitude' => $asset->longitude,
        ]);

        return $mapRecord->sriLankaMapPoint();
    }
}
