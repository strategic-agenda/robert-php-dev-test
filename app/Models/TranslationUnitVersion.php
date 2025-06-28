<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranslationUnitVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'translation_unit_id',
        'translated_text',
        'edited_by',
        'version_number',
    ];

    // only uses created_at timestamp; disable updated_at
    public $timestamps = false;
    const CREATED_AT = 'created_at';
}
