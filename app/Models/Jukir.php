<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

#[Unguarded]

class Jukir extends Model
{
    protected $table = 'jukir';

    public function setoran()
    {
        return $this->hasMany(Setoran::class);
    }
}
