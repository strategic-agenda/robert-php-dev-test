<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TranslationUnit extends Model
{
    protected $fillable = [
        'document_id',
        'segment_index',
        'source_text',
        'source_locale',
        'target_locale',
    ];

    /**
     * A translation unit has many versions (history)
     */
    public function versions(): HasMany
    {
        return $this->hasMany(TranslationUnitVersion::class)
            ->orderBy('version_number', 'desc');
    }
}
