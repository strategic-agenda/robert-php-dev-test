<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationHistory extends Model
{
    protected $fillable = ['translation_id', 'previous_text', 'changed_by', 'change_reason'];
}
?>