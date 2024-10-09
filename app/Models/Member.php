<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $table = 'Members';

    public $timestamps = false;

    protected $appends = ['CompanyName'];

    public function getCompanyNameAttribute()
    {
        return $this->company()->pluck('Name')->first();
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'CompanyId', 'Id');
    }
}
