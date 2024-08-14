<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Contracts\Database\Eloquent\Builder;
class Area extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'valid_from',
        'valid_to',
        'display_in_breaches',
        'geojson_data'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'display_in_breaches' => 'boolean',
        'geojson_data' => 'array',
    ];

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @param Builder $query
     * @param string $term
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where('name', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%");
    }
}
