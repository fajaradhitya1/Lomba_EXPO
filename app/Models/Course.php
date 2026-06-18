<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    // Sesuaikan fillable dengan field di tabel 'courses' kamu
  protected $fillable = [
    'name',
    'semester'
];

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }
}