<?php

namespace Tests\Feature\Livewire;

use App\Livewire\AreaIndex;
use App\Models\Area;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AreaListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     */
    public function test_the_component_can_render(): void
    {
        $component = Livewire::test(AreaIndex::class);

        $component->assertStatus(200);
    }

    /**
     * @return void
     */
    public function test_it_displays_areas(): void
    {
        $areas = Area::factory()->count(5)->create();

        Livewire::test(AreaIndex::class)
            ->assertViewHas('areas', function ($viewAreas) use ($areas) {
                return $viewAreas->count() === 5 &&
                    $viewAreas->pluck('id')->diff($areas->pluck('id'))->isEmpty();
            });
    }

    /**
     * @return void
     */
    public function test_it_paginates_areas(): void
    {
        Area::factory()->count(15)->create();

        $component = Livewire::test(AreaIndex::class);

        $component->assertViewHas('areas', function ($viewAreas) {
            return $viewAreas->count() === 10; // Default pagination is 10
        });

        $component->assertSee('Next');
    }

    /**
     * @return void
     */
    public function test_it_searches_areas_by_name(): void
    {
        Area::factory()->create(['name' => 'Test Area']);
        Area::factory()->create(['name' => 'Another Area']);

        Livewire::test(AreaIndex::class)
            ->set('search', 'Test')
            ->assertViewHas('areas', function ($areas) {
                return $areas->count() === 1 &&
                    $areas->first()->name === 'Test Area';
            });
    }

    /**
     * @return void
     */
    public function test_it_searches_areas_by_description(): void
    {
        Area::factory()->create(['description' => 'This is a test description']);
        Area::factory()->create(['description' => 'This is another description']);

        Livewire::test(AreaIndex::class)
            ->set('search', 'test description')
            ->assertViewHas('areas', function ($areas) {
                return $areas->count() === 1 &&
                    $areas->first()->description === 'This is a test description';
            });
    }

    /**
     * @return void
     */
    public function test_it_searches_areas_by_category_name(): void
    {
        $category = Category::factory()->create(['name' => 'Test Category']);
        $area = Area::factory()->create();
        $area->categories()->attach($category);

        Area::factory()->create(); // Another area without the category

        Livewire::test(AreaIndex::class)
            ->set('search', 'Test Category')
            ->assertViewHas('areas', function ($areas) use ($area) {
                return $areas->count() === 1 &&
                    $areas->first()->id === $area->id;
            });
    }

    /**
     * @return void
     */
    public function test_it_resets_page_when_searching(): void
    {
        Area::factory()->count(20)->create();

        $component = Livewire::test(AreaIndex::class)
            ->call('setPage', 2)
            ->set('search', 'Test');

        $this->assertEquals(1, $component->get('paginators')['page']);
    }

    /**
     * @return void
     */
    public function test_search_query_is_kept_in_url(): void
    {
        Livewire::withQueryParams(['search' => 'Test'])
            ->test(AreaIndex::class)
            ->assertSet('search', 'Test');
    }
}
