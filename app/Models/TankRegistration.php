<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TankRegistration extends Model
{
    use HasFactory;

    private const SRI_LANKA_BOUNDS = [
        'lat_min' => 5.85,
        'lat_max' => 9.95,
        'lng_min' => 79.45,
        'lng_max' => 82.05,
    ];

    private const PROJECT_COORDINATE_BOUNDS = [
        'x_min' => 300000,
        'x_max' => 900000,
        'y_min' => 100000,
        'y_max' => 800000,
    ];

    private const DISTRICT_CENTROIDS = [
        'ampara' => [7.3018, 81.6747],
        'anuradhapura' => [8.3114, 80.4037],
        'badulla' => [6.9934, 81.0550],
        'batticaloa' => [7.7170, 81.7000],
        'colombo' => [6.9271, 79.8612],
        'galle' => [6.0535, 80.2210],
        'gampaha' => [7.0873, 80.0144],
        'hambantota' => [6.1241, 81.1185],
        'jaffna' => [9.6615, 80.0255],
        'kalutara' => [6.5854, 79.9607],
        'kandy' => [7.2906, 80.6337],
        'kegalle' => [7.2513, 80.3464],
        'kilinochchi' => [9.3803, 80.3770],
        'kurunegala' => [7.4863, 80.3647],
        'mannar' => [8.9810, 79.9044],
        'matale' => [7.4675, 80.6234],
        'matara' => [5.9549, 80.5550],
        'monaragala' => [6.8728, 81.3507],
        'mullaitivu' => [9.2671, 80.8142],
        'nuwaraeliya' => [6.9497, 80.7891],
        'polonnaruwa' => [7.9403, 81.0188],
        'puttalam' => [8.0362, 79.8283],
        'ratnapura' => [6.7056, 80.3847],
        'trincomalee' => [8.5874, 81.2152],
        'vavuniya' => [8.7514, 80.4971],
    ];

    private const PROVINCE_CENTROIDS = [
        'central' => [7.3410, 80.6540],
        'eastern' => [7.7850, 81.5310],
        'northcentral' => [8.1996, 80.6327],
        'northern' => [9.2500, 80.5000],
        'northwestern' => [7.7580, 80.1870],
        'sabaragamuwa' => [6.7396, 80.3659],
        'southern' => [6.2000, 80.7500],
        'uva' => [6.8428, 81.3399],
        'western' => [6.8271, 80.0144],
    ];

    protected $fillable = [
        'tank_id',
        'tank_name',
        'river_basin',
        'cascade_name',
        'province',
        'district',
        'ds_division',
        'gn_division',
        'as_centre',
        'agency',
        'no_of_family',
        'longitude',
        'latitude',
        'progress',
        'contractor',
        'contractor_address',
        'contractor_contact_number',
        'contractor_cida_grade',
        'construction_start_date',
        'payment',
        'awarded_date',
        'construction_period_days',
        'extension_of_time_months',
        'status',
        'remarks',
        'open_ref_no',
        'cumulative_amount',
        'paid_advanced_amount',
        'recommended_ipc_no',
        'recommended_ipc_amount',
        'base_cost',
        'physical_contingencies',
        'price_contingencies',
        'net_value',
        'vat',
        'grand_total',
        'pre_construction_images',
        'during_construction_images',
        'post_construction_images',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'no_of_family' => 'integer',
            'longitude' => 'decimal:7',
            'latitude' => 'decimal:7',
            'progress' => 'decimal:2',
            'construction_start_date' => 'date',
            'awarded_date' => 'date',
            'construction_period_days' => 'integer',
            'extension_of_time_months' => 'integer',
            'cumulative_amount' => 'decimal:2',
            'paid_advanced_amount' => 'decimal:2',
            'recommended_ipc_amount' => 'decimal:2',
            'base_cost' => 'decimal:2',
            'physical_contingencies' => 'decimal:2',
            'price_contingencies' => 'decimal:2',
            'net_value' => 'decimal:2',
            'vat' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'pre_construction_images' => 'array',
            'during_construction_images' => 'array',
            'post_construction_images' => 'array',
            'imported_at' => 'datetime',
        ];
    }

    public function sriLankaMapPoint(): ?array
    {
        $rawLatitude = $this->numericCoordinate($this->latitude);
        $rawLongitude = $this->numericCoordinate($this->longitude);
        $source = null;
        $latitude = null;
        $longitude = null;
        $x = null;
        $y = null;

        if ($this->isSriLankaDecimalCoordinate($rawLatitude, $rawLongitude)) {
            $latitude = $rawLatitude;
            $longitude = $rawLongitude;
            $source = 'Exact GPS coordinates';
            [$x, $y] = $this->decimalPointToMapPosition($latitude, $longitude);
        } elseif ($centroid = $this->administrativeCentroid()) {
            [$latitude, $longitude] = $centroid;
            $source = filled($this->district) ? 'Detected from district' : 'Detected from province';
            [$x, $y] = $this->decimalPointToMapPosition($latitude, $longitude);
        } elseif ($rawLatitude !== null && $rawLongitude !== null) {
            $source = 'Project coordinate view';
            [$x, $y] = $this->projectCoordinateToMapPosition($rawLatitude, $rawLongitude);
        }

        if ($x === null || $y === null) {
            return null;
        }

        return [
            'id' => $this->id,
            'tank_id' => $this->tank_id,
            'tank_name' => $this->tank_name,
            'province' => $this->province,
            'district' => $this->district,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'raw_latitude' => $rawLatitude,
            'raw_longitude' => $rawLongitude,
            'x' => $x,
            'y' => $y,
            'source' => $source,
            'maps_url' => $latitude !== null && $longitude !== null
                ? 'https://www.google.com/maps/search/?api=1&query='.$latitude.','.$longitude
                : null,
        ];
    }

    private function numericCoordinate(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function isSriLankaDecimalCoordinate(?float $latitude, ?float $longitude): bool
    {
        return $latitude !== null
            && $longitude !== null
            && $latitude >= self::SRI_LANKA_BOUNDS['lat_min']
            && $latitude <= self::SRI_LANKA_BOUNDS['lat_max']
            && $longitude >= self::SRI_LANKA_BOUNDS['lng_min']
            && $longitude <= self::SRI_LANKA_BOUNDS['lng_max'];
    }

    private function administrativeCentroid(): ?array
    {
        $districtKey = $this->locationKey($this->district);

        if ($districtKey && isset(self::DISTRICT_CENTROIDS[$districtKey])) {
            return self::DISTRICT_CENTROIDS[$districtKey];
        }

        $provinceKey = $this->locationKey($this->province);

        return $provinceKey && isset(self::PROVINCE_CENTROIDS[$provinceKey])
            ? self::PROVINCE_CENTROIDS[$provinceKey]
            : null;
    }

    private function locationKey(?string $value): ?string
    {
        $key = preg_replace('/[^a-z]/', '', strtolower((string) $value));

        return $key !== '' ? $key : null;
    }

    private function decimalPointToMapPosition(float $latitude, float $longitude): array
    {
        $relativeX = ($longitude - self::SRI_LANKA_BOUNDS['lng_min'])
            / (self::SRI_LANKA_BOUNDS['lng_max'] - self::SRI_LANKA_BOUNDS['lng_min']);
        $relativeY = (self::SRI_LANKA_BOUNDS['lat_max'] - $latitude)
            / (self::SRI_LANKA_BOUNDS['lat_max'] - self::SRI_LANKA_BOUNDS['lat_min']);

        return [
            $this->clampMapPercent(18 + ($relativeX * 64)),
            $this->clampMapPercent(4 + ($relativeY * 92)),
        ];
    }

    private function projectCoordinateToMapPosition(float $latitude, float $longitude): array
    {
        $relativeX = ($longitude - self::PROJECT_COORDINATE_BOUNDS['x_min'])
            / (self::PROJECT_COORDINATE_BOUNDS['x_max'] - self::PROJECT_COORDINATE_BOUNDS['x_min']);
        $relativeY = (self::PROJECT_COORDINATE_BOUNDS['y_max'] - $latitude)
            / (self::PROJECT_COORDINATE_BOUNDS['y_max'] - self::PROJECT_COORDINATE_BOUNDS['y_min']);

        return [
            $this->clampMapPercent(18 + ($relativeX * 64)),
            $this->clampMapPercent(4 + ($relativeY * 92)),
        ];
    }

    private function clampMapPercent(float $value): float
    {
        return round(min(96, max(4, $value)), 2);
    }
}
