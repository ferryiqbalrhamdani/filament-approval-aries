<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\HasName;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements HasAvatar, HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'password',
        'jk',
        'status',
        'avatar_url',
        'custom_fields',
        'company_id',
        'division_id',
        'status_karyawan',
        'sisa_cuti',
        'user_approve_id',
        'user_mengetahui_id',
        'sisa_cuti_sebelumnya',
        'remember_token',
        'email',
        'email_verified_at',
    ];

    public function getFilamentName(): string
    {
        return $this->getAttributeValue('first_name') . ' ' . $this->getAttributeValue('last_name');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url("$this->avatar_url") : null;;
    }

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function userApprove()
    {
        return $this->belongsTo(User::class, 'user_approve_id');
    }
    public function userMengetahui()
    {
        return $this->belongsTo(User::class, 'user_mengetahui_id');
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function userPasswordReset()
    {
        return $this->belongsTo(PasswordReset::class);
    }

    // public function getRouteKeyName()
    // {
    //     return 'username';
    // }

    public function cutis()
    {
        return $this->hasMany(UserCuti::class);
    }

    public function getSisaCutiTerbaruAttribute()
    {
        return $this->cutis()->latest('tahun')->value('sisa_cuti') ?? 0;
    }


}
