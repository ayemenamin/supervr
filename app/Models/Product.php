<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'language_id',
        'category_id',
        'subcategory_id',
        'feature_image',
        'summary',
        'description',
        'variations',
        'addons',
        'current_price',
        'previous_price',
        'rating',
        'status',
        'is_feature',
        'is_valible',
        'is_special',
    ];

    public function category()
    {
        return $this->hasOne('App\Models\Pcategory', 'id', 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(PsubCategory::class, 'id', 'subcategory_id');
    }

    public function product_reviews()
    {
        return $this->hasMany('App\Models\ProductReview');
    }

    public function product_images()
    {
        return $this->hasMany('App\Models\ProductImage');
    }

    public function language()
    {
        return $this->belongsTo('App\Models\Language');
    }

}
