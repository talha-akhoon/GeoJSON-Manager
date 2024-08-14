<?php

namespace App\Livewire;

use App\Models\Area;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;
use Livewire\WithPagination;

class AreaIndex extends Component
{
    use WithPagination;

    /**
     * @var string
     */
    public string $search = '';

    /**
     * @var array|array[]
     */
    protected array $queryString = ['search' => ['except' => '']];

    /**
     * @return void
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return Factory|View|Application
     */
    public function render(): View|Factory|Application
    {
        $areas = Area::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('categories', function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->with('categories')
            ->paginate(10);

        return view('livewire.area-index', [
            'areas' => $areas,
        ]);
    }
}
