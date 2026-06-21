<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pop extends Model
{
    protected $fillable = [
        'name',
        'frame_size',
        'layout_type',
        'header_text',
        'brand_name',
        'product_desc',
        'sku',
        'unit',
        'qty_print',
        'primary_price',
        'secondary_price',
        'additional_data',
        'show_starting_from',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'show_starting_from' => 'boolean',
    ];
}
