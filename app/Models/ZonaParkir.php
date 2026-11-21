<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

#[Unguarded]
class ZonaParkir extends Model
{
    protected $table = 'zona_parkir';
    protected $casts = ['koordinat' => 'array'];
}
