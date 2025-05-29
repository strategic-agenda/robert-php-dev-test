<?php

declare(strict_types=1);

namespace Api\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranslationHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'translation_unit_id' => $this->translation_unit_id,
            'previous_text' => $this->previous_text,
            'new_text' => $this->new_text,
            'changed_by' => $this->changed_by,
            'change_reason' => $this->change_reason,
            'created_at' => $this->created_at
        ];
    }
} 