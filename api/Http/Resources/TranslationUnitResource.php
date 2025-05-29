<?php

declare(strict_types=1);

namespace Api\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * TranslationUnitResource
 * 
 * Resource class for transforming TranslationUnit models into JSON responses.
 * This class handles the API response formatting for translation units.
 */
class TranslationUnitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The incoming HTTP request
     * @return array The transformed data array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source_text' => $this->source_text,
            'source_language' => $this->source_language,
            'target_language' => $this->target_language,
            'target_text' => $this->target_text,
            'project_id' => $this->project_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'history' => TranslationHistoryResource::collection($this->whenLoaded('history'))
        ];
    }
} 