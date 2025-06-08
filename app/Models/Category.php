<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category_type_id'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function type()
    {
        return $this->belongsTo(CategoryType::class, 'category_type_id');
    }
}
