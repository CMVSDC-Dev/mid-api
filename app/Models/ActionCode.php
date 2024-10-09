<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionCode extends Model
{
    use HasFactory;

    protected $table = 'ActionCodes';

    public $timestamps = false;
}
