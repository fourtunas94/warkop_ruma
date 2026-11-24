<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kategori extends Model
{
    protected $table = 'kategoris';

    protected $fillable = [
        'nama_kategori',
        'slug',
        'deskripsi',
        'is_active',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'kategori_id');
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Scope to only return active categories.
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', 1);
    }
}
