<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    /**
     * Pengguna yang terdaftar pada instansi ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Unit kerja yang berada di bawah instansi ini.
     */
    public function workUnits(): HasMany
    {
        return $this->hasMany(WorkUnit::class, 'unit_id');
    }

    /**
     * Surat keluar dari instansi ini.
     */
    public function sentLetters(): HasMany
    {
        return $this->hasMany(Letter::class, 'sender_unit_id');
    }

    /**
     * Surat masuk ke instansi ini.
     */
    public function receivedLetters(): HasMany
    {
        return $this->hasMany(Letter::class, 'receiver_unit_id');
    }

    /**
     * Disposisi yang ditugaskan kepada instansi ini.
     */
    public function assignedDispositions(): HasMany
    {
        return $this->hasMany(
            LetterDisposition::class,
            'assigned_to_unit_id'
        );
    }
}
