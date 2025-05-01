<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TranslationUnit extends Model
{
    use SoftDeletes;

    protected $fillable = ['source_text', 'source_language', 'project_id'];

    public function translations()
    {
        return $this->hasMany(Translation::class);
    }

    public function history()
    {
        return $this->hasManyThrough(TranslationHistory::class, Translation::class);
    }
}
?>