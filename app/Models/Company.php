<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use HasFactory;

    protected $table = 'Companies';

    public $timestamps = false;

    public function owner() : BelongsTo {
        return $this->belongsTo(
            \App\Models\Member::class,
            'CompanyId'
        );
    }
}
