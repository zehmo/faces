<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Town extends Model
{
    public $timestamps = false;

    protected $fillable = ['lga_id', 'name'];

    public function lga(): BelongsTo
    {
        return $this->belongsTo(Lga::class);
    }
}
