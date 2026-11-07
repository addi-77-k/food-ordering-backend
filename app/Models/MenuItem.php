<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class MenuItem extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'menu_items';

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image_url',
        'availability',
        'preparation_time',
    ];

    protected $attributes = [
        'availability' => true,
        'preparation_time' => 15,
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'availability' => 'boolean',
            'preparation_time' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
