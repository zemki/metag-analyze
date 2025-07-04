<?php

namespace App\Http\Resources\Mart;

use Illuminate\Http\Resources\Json\JsonResource;

class MartPageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'pageId' => $this->id,
            'name' => $this->name,
            'content' => $this->content,
            'options' => [
                'showOnFirstAppStart' => $this->show_on_first_app_start,
                'buttonText' => $this->button_text
            ],
            'sortOrder' => $this->sort_order
        ];
    }
}