<?php

namespace App\Http\Resources\Mart;

use Illuminate\Http\Resources\Json\JsonResource;

class MartPageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pageId' => $this->id,
            'name' => $this->name,
            'content' => $this->content,
            'options' => [
                'buttonText' => $this->button_text,
            ],
        ];
    }
}
