<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Impairment extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $table= 'MibImpairments';

    // public $timestamps = false;

    protected $fillable = [
        'MibEntryId',
        'ImpairmentCodeId',
        'NumberId',
        'LetterId',
        'ImpairmentDate',
        'ReportedDate',
        'EncodeDate',
        'IsShared',
        'Remarks',
        'OldImpairmentCode',
        'IsDeactivated',
        'ActionCodeId',
        'UnderwritingDate',
        'NewImpairmentCode',
        'vr',
        'ActionCode',
        'LetterCode',
        'ImpairmentCodes',
        'NumberCode',
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

    protected $appends = ['NumberLetterCode','ImpairmentDescription'];

    public function getNumberLetterCodeAttribute()
    {
        return $this->NumberCode . $this->LetterCode;
    }

    // public function getImpairmentCodesAttribute()
    // {
    //     $code = $this->code()->pluck('Code')->first();
    //     return $code . ' ' . $this->OldImpairmentCode;
    // }

    public function getImpairmentDescriptionAttribute()
    {
        $description = $this->code()->pluck('Description')->first();
        return empty($this->ImpairmentCodes) || $this->ImpairmentCodes == 'NA-'
            ? 'Old Impairment'
            : $description;
    }

    public function code()
    {
        return $this->hasOne(ImpairmentCode::class, 'Id', 'ImpairmentCodeId');
    }
}
