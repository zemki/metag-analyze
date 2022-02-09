<?php

namespace App\Http\Resources;

use App\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Entry extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        {
            return [
            'id' => $this->id,
            'begin' => $this->begin,
            'end' => $this->end,
            "inputs" => $this->inputs,
            "content" => $this->content,
            "comment" => $this->comment,
            "case_id" => $this->case_id,
            "media_id" => $this->media_id,
            "media_name" => Media::where('id', $this->media_id)->first()->name,
            "place_id" => $this->place_id,
            "communication_partner_id" => $this->communication_partner_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        }
    }
}
