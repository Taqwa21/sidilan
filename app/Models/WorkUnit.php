<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkUnit extends Model
{
    protected $fillable = [
        'unit_id',
        'name',
        'code',
        'description',
    ];

    /**
     * Instansi induk unit kerja.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Pengguna yang bertugas pada unit kerja ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'work_unit_id');
    }

    /**
     * Riwayat tindak lanjut dari unit kerja ini.
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(LetterFollowUp::class, 'work_unit_id');
    }

    /**
     * Disposisi yang ditujukan kepada unit kerja ini.
     */
    public function dispositions(): BelongsToMany
    {
        return $this->belongsToMany(
            LetterDisposition::class,
            'disposition_work_unit',
            'work_unit_id',
            'letter_disposition_id'
        )->withTimestamps();
    }
}
