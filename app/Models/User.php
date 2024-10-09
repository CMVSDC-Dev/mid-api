<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject, Auditable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, AuditableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'jwt_secret',
        'member_id',
        'company_id',
        'last_active_at',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attributes to exclude from the Audit.
     *
     * @var array
     */
    protected $auditExclude = [
        'last_active_at',
        'is_active',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_active_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected $appends = ['company_name'];

    protected $with = ['roles'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Generate and store the secret key per user account
     * @return void
     */
    public function generateJWTSecret()
    {
        $this->jwt_secret = Str::random(32);
        $this->save();
    }

    public function getCompanyNameAttribute()
    {
        $member = $this->member()->first();
        return $member->CompanyName ?? null;
    }

    // public function company()
    // {
    //     return $this->belongsTo(Company::class, 'company_id', 'Id');
    // }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'Id');
    }
}
