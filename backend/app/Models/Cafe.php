<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cafe extends Model
{
    use HasFactory;

    public const STATUSES = ['visited', 'wishlist'];

    public const FACILITIES = ['wifi', 'colokan', 'musala', 'parkir'];

    public const COLORS = ['pink', 'yellow', 'purple', 'green', 'blue'];

    protected $fillable = [
        'name',
        'area',
        'status',
        'price_level',
        'rating',
        'work_friendly',
        'facilities',
        'favorite_menu',
        'notes',
        'last_visited_at',
        'visit_count',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'price_level' => 'integer',
            'rating' => 'integer',
            'work_friendly' => 'boolean',
            'facilities' => 'array',
            'last_visited_at' => 'date',
            'visit_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}