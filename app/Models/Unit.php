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
    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }

    public function sentLetters(): HasMany
    {
        return $this->hasMany(Letter::class, 'sender_unit_id');
    }

    public function receivedLetters(): HasMany
    {
        return $this->hasMany(Letter::class, 'receiver_unit_id');
    }
}
