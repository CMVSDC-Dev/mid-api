<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Entry extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $table= 'MibEntries';

    // public $timestamps = false;

    protected $fillable = [
        'Alias',
        'BirthDate',
        'FirstName',
        'Gender',
        'LastName',
        'MaidenName',
        'MiddleName',
        'Nationality',
        'Suffix',
        'Title',
        'CompanyId',
        'DownloadStatus',
        'IsShared',
        'MemberId',
        'IsDeactivated',
        'OtherName',
        'BirthPlace',
        'PolicyNumber',
        'ActionCodeId',
        'UnderwritingDate',
        'MemberControlNumber',
        'ActionCode',
        'CompanyCode',
    ];

    // protected $hidden = ['created_at', 'updated_at'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected $appends = ['CompanyName'];

    protected $with = ['impairments'];

    public function getCompanyNameAttribute()
    {
        return $this->company()->pluck('Name')->first();;
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'CompanyId', 'Id');
    }

    public function impairments()
    {
        return $this->hasMany(Impairment::class, 'MibEntryId', 'Id');
    }
}
