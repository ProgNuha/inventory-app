<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $fillable = ['category_id', 'nama', 'batas_harga'];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
