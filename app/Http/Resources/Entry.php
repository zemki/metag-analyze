<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Entry extends JsonResource
{

            /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray()
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
            "place_id" => $this->place_id,
            "communication_partner_id" => $this->communication_partner_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

}
