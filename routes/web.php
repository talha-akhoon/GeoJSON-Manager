<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\AreaForm;
use App\Livewire\AreaIndex;

Route::name('areas.')->group(function () {
    Route::get('/', AreaIndex::class)->name('index');
    Route::get('/create', AreaForm::class)->name('create');
    Route::get('/{areaId}/edit', AreaForm::class)->name('edit');
});
