<?php

namespace App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use App\Models\Area;
use App\Models\Category;
use Livewire\WithFileUploads;
use Livewire\Features\SupportRedirects\Redirector;

class AreaForm extends Component
{
    use WithFileUploads;

    /**
     * @var int|null
     */
    public ?int $areaId = null;
    /**
     * @var string
     */
    public string $name = '';
    /**
     * @var string|null
     */
    public ?string $description = null;
    /**
     * @var array
     */
    public array $categories = [];
    /**
     * @var string|null
     */
    public ?string $validFrom = null;
    /**
     * @var string|null
     */
    public ?string $validTo = null;
    /**
     * @var bool
     */
    public bool $displayInBreaches = false;
    /**
     * @var UploadedFile|null
     */
    public ?UploadedFile $geoJSONFile = null;
    /**
     * @var string|null
     */
    public ?string $drawnGeoJSON = null;

    /**
     * @return string[]
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categories' => 'required|array|min:1',
            'validFrom' => 'required|date',
            'validTo' => 'nullable|date|after:validFrom',
            'displayInBreaches' => 'required|boolean',
            'drawnGeoJSON' => [
                'required',
                'json',
                function ($attribute, $value, $fail) {
                    $decoded = json_decode($value, true);
                    if (!$this->isValidGeoJSON($decoded)) {
                        $fail('The GeoJSON data is invalid or contains no valid geographic data.');
                    }
                },
            ],
        ];
    }

    /**
     * @param int|null $areaId
     * @return void
     */
    public function mount(int $areaId = null): void
    {
        $this->area = new Area();

        if ($areaId) {
            $area = Area::findOrFail($areaId);
            $this->area = $area;
            $this->areaId = $area->id;
            $this->name = $area->name;
            $this->description = $area->description;
            $this->categories = $area->categories->pluck('id')->toArray();
            $this->validFrom = $area->valid_from->format('Y-m-d');
            $this->validTo = $area->valid_to ? $area->valid_to->format('Y-m-d') : null;
            $this->displayInBreaches = $area->display_in_breaches;
            $this->drawnGeoJSON = json_encode($area->geojson_data);
        }
    }

    /**
     * @return void
     */
    public function updatedGeoJSONFile(): void
    {
        $this->validate([
            'geoJSONFile' => 'required|file|mimes:json,geojson',
        ]);

        try {
            $content = json_decode(file_get_contents($this->geoJSONFile->getRealPath()), true);

            $validator = Validator::make(['geojson' => json_encode($content)], [
                'geojson' => [
                    'required',
                    'json',
                    function ($attribute, $value, $fail) {
                        if (!$this->isValidGeoJSON(json_decode($value, true))) {
                            $fail('The uploaded GeoJSON file contains no valid geographic data.');
                        }
                    },
                ],
            ]);

            if ($validator->fails()) {
                $this->addError('geoJSONFile', $validator->errors()->first('geojson'));
            } else {
                $this->drawnGeoJSON = json_encode($content);
                $this->dispatch('geoJSONUploaded', $this->drawnGeoJSON);
            }
        } catch (\Exception $e) {
            $this->addError('geoJSONFile', 'Error parsing GeoJSON: ' . $e->getMessage());
        }
    }

    /**
     * @param $geoJSON
     * @return void
     */
    public function drawnGeoJSONUpdated($geoJSON): void
    {
        $this->drawnGeoJSON = $geoJSON;
    }

    /**
     * @return Redirector
     */
    public function save(): Redirector
    {
        $this->validate();

        $areaData = [
            'name' => $this->name,
            'description' => $this->description,
            'valid_from' => $this->validFrom,
            'valid_to' =>  $this->validTo ?? null,
            'display_in_breaches' => $this->displayInBreaches,
            'geojson_data' => json_decode($this->drawnGeoJSON, true),
        ];

        if ($this->areaId) {
            $area = Area::findOrFail($this->areaId);
            $area->update($areaData);
        } else {
            $area = Area::create($areaData);
        }

        $area->categories()->sync($this->categories);

        session()->flash('message', $this->areaId ? 'Area updated successfully.' : 'Area created successfully.');
        return redirect()->route('areas.index');
    }

    /**
     * @param $geoJson
     * @return bool
     */
    private function isValidGeoJSON($geoJson): bool
    {
        if (!is_array($geoJson) || !isset($geoJson['type'])) {
            return false;
        }

        if ($geoJson['type'] === 'FeatureCollection' && isset($geoJson['features']) && empty($geoJson['features'])) {
            return false;
        }

        if ($geoJson['type'] === 'FeatureCollection' && isset($geoJson['features'])) {
            foreach ($geoJson['features'] as $feature) {
                if (!$this->isValidGeometry($feature['geometry'])) {
                    return false;
                }
            }

            return true;
        }

        if ($geoJson['type'] === 'Feature' && isset($geoJson['geometry'])) {
            return $this->isValidGeometry($geoJson['geometry']);
        }

        return $this->isValidGeometry($geoJson);
    }

    /**
     * @param $geometry
     * @return bool
     */
    private function isValidGeometry($geometry): bool
    {
        if (!is_array($geometry) || !isset($geometry['type']) || !isset($geometry['coordinates'])) {
            return false;
        }

        $coordinates = $geometry['coordinates'];

        return match ($geometry['type']) {
            'Point' => is_array($coordinates) && count($coordinates) >= 2,
            'LineString' => is_array($coordinates) && count($coordinates) >= 2 && is_array($coordinates[0]),
            'Polygon' => is_array($coordinates) && count($coordinates) >= 1 && is_array($coordinates[0]) && count($coordinates[0]) >= 4,
            default => false,
        };
    }

    /**
     * @return View|Factory|Application
     */
    public function render(): View|Factory|Application
    {
        return view('livewire.area-form', [
            'allCategories' => Category::all()
        ]);
    }
}
