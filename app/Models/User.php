<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'role',
        'regency_id',
        'phone',
        'position',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function changeRequests(): HasMany
    {
        return $this->hasMany(UptChangeRequest::class, 'user_id');
    }

    public function reviewedChangeRequests(): HasMany
    {
        return $this->hasMany(UptChangeRequest::class, 'reviewed_by');
    }

    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(UptDocument::class, 'uploaded_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator_kabupaten';
    }

    public function isExecutive(): bool
    {
        return $this->role === 'eksekutif';
    }

    public function isBpn(): bool
    {
        return $this->role === 'mitra_bpn';
    }
}
