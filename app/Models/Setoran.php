<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;

#[Unguarded]

class Setoran extends Model
{
    protected $table = 'setoran';

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function jukir()
    {
        return $this->belongsTo(Jukir::class);
    }
}
