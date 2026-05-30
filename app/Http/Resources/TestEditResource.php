<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestEditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'estado' => $this->estado,
            'categoria_id' => $this->documento->categoria_id,
            'documento_id' => $this->documento->id,
            'configuracion' => $this->configuracion
        ];
    }
}
