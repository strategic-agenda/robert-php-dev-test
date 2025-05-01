<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Translation extends Model
{
    use SoftDeletes;

    protected $fillable = ['translation_unit_id', 'target_text', 'target_language', 'translator_id', 'status'];

    public function translationUnit()
    {
        return $this->belongsTo(TranslationUnit::class);
    }

    public function history()
    {
        return $this->hasMany(TranslationHistory::class);
    }
}
?>