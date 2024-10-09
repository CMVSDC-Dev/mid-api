<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntryAudit extends Model
{
    use HasFactory;

    protected $table= 'MibEntry_Audit';

    // public $timestamps = false;

    protected $fillable = [
        'EntryID',
        'Alias',
        'BirthDate',
        'BirthPlace',
        'CompanyId',
        'DownloadStatus',
        'FirstName',
        'OtherName',
        'Gender',
        'IsShared',
        'IsDeactivated',
        'LastName',
        'MaidenName',
        'MemberId',
        'MiddleName',
        'Nationality',
        'Suffix',
        'Title',
        'DateOfAction',
        'PersonInvolved',
        'ActionDone',
        'PolicyNumber',
        'UnderwritingDate'
    ];

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

    public function getCompanyNameAttribute()
    {
        return $this->company()->pluck('Name')->first();;
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'CompanyId', 'Id');
    }
}
