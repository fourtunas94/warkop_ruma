<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $table = 'menus';

    protected $fillable = [
        'kategori_id',
        'nama_menu',
        'deskripsi',
        'harga',
        'stok',
        'gambar',
        'is_active',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function pesananItems(): HasMany
    {
        return $this->hasMany(PesananItem::class, 'menu_id');
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Scope to return only active and in-stock menus.
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', 1)->where('stok', 1);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Get the full public URL for the menu image.
     */
    public function getGambarUrlAttribute(): ?string
    {
        if ($this->gambar) {
            return asset('uploads/menu/' . $this->gambar);
        }
        return null;
    }
}
