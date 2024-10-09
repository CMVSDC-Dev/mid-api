<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpairmentAudit extends Model
{
    use HasFactory;

    protected $table= 'MibImpairment_Audit';

    // public $timestamps = false;

    protected $fillable = [
        'EntryID',
        'MibEntryId',
        'ImpairmentCodeId',
        'NewImpairmentCode',
        'OldImpairmentCode',
        'NumberId',
        'LetterId',
        'ImpairmentDate',
        'ReportedDate',
        'EncodeDate',
        'IsShared',
        'IsDeactivated',
        'Remarks',
        'ActionCodeId',
        'UnderwritingDate',
        'vr',
        'DateOfAction',
        'PersonInvolved',
        'ActionDone',
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
