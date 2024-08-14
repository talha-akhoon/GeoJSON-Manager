<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\AreaForm;

class AreaFormTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @return void
     */
    public function test_it_successfully_renders_area_create_form(): void
    {
        $this->get(route('areas.create'))
            ->assertStatus(200)
            ->assertSeeLivewire(AreaForm::class);
    }

    /**
     * @return void
     */
    public function test_it_successfully_creates_new_area(): void
    {
        $category = Category::factory()->create();

        Livewire::test(AreaForm::class)
            ->set('name', 'Test Area')
            ->set('description', 'This is a test area')
            ->set('categories', [$category->id])
            ->set('validFrom', '2023-01-01')
            ->set('validTo', '2023-12-31')
            ->set('displayInBreaches', true)
            ->set('drawnGeoJSON', json_encode([
                'type' => 'Polygon',
                'coordinates' => [[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]
            ]))
            ->call('save')
            ->assertRedirect(route('areas.index'));

        $this->assertDatabaseHas('areas', [
            'name' => 'Test Area',
            'description' => 'This is a test area',
            'valid_from' => '2023-01-01',
            'valid_to' => '2023-12-31',
            'display_in_breaches' => true,
        ]);
    }

    /**
     * @return void
     */
    public function test_it_successfully_renders_edit_area_page(): void
    {
        $area = Area::factory()->create();
        $category = Category::factory()->create(['name' => 'Test Category']);
        $area->categories()->attach($category);

        $response = $this->get(route('areas.edit', $area));

        $response->assertStatus(200)
            ->assertSeeLivewire('area-form')
            ->assertSee($area->name)
            ->assertSee($area->description)
            ->assertSee($category->name);
    }

    /**
     * @return void
     */
    public function test_it_successfully_updates_existing_area(): void
    {
        $area = Area::factory()->create();
        $category = Category::factory()->create();

        Livewire::test(AreaForm::class, ['areaId' => $area->id])
            ->set('name', 'Updated Area')
            ->set('description', 'This is an updated area')
            ->set('categories', [$category->id])
            ->set('validFrom', '2023-02-01')
            ->set('validTo', '2023-11-30')
            ->set('displayInBreaches', false)
            ->set('drawnGeoJSON', json_encode([
                'type' => 'Polygon',
                'coordinates' => [[[0, 0], [2, 0], [2, 2], [0, 2], [0, 0]]]
            ]))
            ->call('save')
            ->assertRedirect(route('areas.index'));

        $this->assertDatabaseHas('areas', [
            'id' => $area->id,
            'name' => 'Updated Area',
            'description' => 'This is an updated area',
            'valid_from' => '2023-02-01',
            'valid_to' => '2023-11-30',
            'display_in_breaches' => false,
        ]);
    }

    /**
     * @return void
     */
    public function test_it_successfully_validates_of_required_fields(): void
    {
        Livewire::test(AreaForm::class)
            ->set('name', '')
            ->set('validFrom', '')
            ->set('drawnGeoJSON', '')
            ->call('save')
            ->assertHasErrors(['name', 'validFrom', 'drawnGeoJSON']);
    }

    /**
     * @return void
     */
    public function test_it_successfully_validates_date_range(): void
    {
        Livewire::test(AreaForm::class)
            ->set('validFrom', '2023-12-31')
            ->set('validTo', '2023-01-01')
            ->call('save')
            ->assertHasErrors(['validTo']);
    }

    /**
     * @return void
     */
    public function test_it_successfully_handles_geojson_file_upload(): void
    {
        $geojsonContent = json_encode([
            'type' => 'Polygon',
            'coordinates' => [[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]
        ]);

        $file = UploadedFile::fake()->createWithContent(
            'test.geojson',
            $geojsonContent
        );

        Livewire::test(AreaForm::class)
            ->set('geoJSONFile', $file)
            ->assertDispatched('geoJSONUploaded')
            ->assertSet('drawnGeoJSON', $geojsonContent);
    }

    /**
     * @return void
     */
    public function test_it_validates_valid_geojson(): void
    {
        $validGeoJSON = json_encode([
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]]
                ]
            ]
        ]);

        Livewire::test(AreaForm::class)
            ->set('drawnGeoJSON', $validGeoJSON)
            ->call('save')
            ->assertHasNoErrors(['drawnGeoJSON']);
    }

    /**
     * @return void
     */
    public function test_it_invalidates_geojson_without_coordinates(): void
    {
        $invalidGeoJSON = json_encode([
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon'
            ]
        ]);

        Livewire::test(AreaForm::class)
            ->set('drawnGeoJSON', $invalidGeoJSON)
            ->call('save')
            ->assertHasErrors(['drawnGeoJSON']);
    }

    /**
     * @return void
     */
    public function test_it_invalidates_geojson_with_empty_coordinates(): void
    {
        $invalidGeoJSON = json_encode([
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => []
            ]
        ]);

        Livewire::test(AreaForm::class)
            ->set('drawnGeoJSON', $invalidGeoJSON)
            ->call('save')
            ->assertHasErrors(['drawnGeoJSON']);
    }

    /**
     * @return void
     */
    public function test_it_invalidates_non_json_data(): void
    {
        Livewire::test(AreaForm::class)
            ->set('drawnGeoJSON', 'This is not JSON')
            ->call('save')
            ->assertHasErrors(['drawnGeoJSON']);
    }

    /**
     * @return void
     */
    public function test_it_validates_valid_geojson_file_upload(): void
    {
        $validGeoJSON = json_encode([
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]]
                ]
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('valid.geojson', $validGeoJSON);

        Livewire::test(AreaForm::class)
            ->set('geoJSONFile', $file)
            ->assertHasNoErrors(['geoJSONFile'])
            ->assertDispatched('geoJSONUploaded');
    }

    /**
     * @return void
     */
    public function test_it_invalidates_geojson_file_upload_with_invalid_data(): void
    {
        $invalidGeoJSON = json_encode([
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => []
            ]
        ]);

        $file = UploadedFile::fake()->createWithContent('invalid.geojson', $invalidGeoJSON);

        Livewire::test(AreaForm::class)
            ->set('geoJSONFile', $file)
            ->assertHasErrors(['geoJSONFile']);
    }

    /**
     * @return void
     */
    public function test_it_invalidates_non_geojson_file_upload(): void
    {
        $file = UploadedFile::fake()->create('document.txt');

        Livewire::test(AreaForm::class)
            ->set('geoJSONFile', $file)
            ->assertHasErrors(['geoJSONFile']);
    }
}
