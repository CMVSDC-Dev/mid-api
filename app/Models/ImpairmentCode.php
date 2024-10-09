<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpairmentCode extends Model
{
    use HasFactory;

    protected $table = 'ImpairmentCodes';

    public $timestamps = false;
}
