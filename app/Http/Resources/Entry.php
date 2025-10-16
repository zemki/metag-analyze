<?php

namespace App\Http\Resources;

use App\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Entry extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $inputs = json_decode($this->inputs, true);
        unset($inputs['firstValue']);

        return [
            'id' => $this->id,
            'begin' => $this->begin,
            'end' => $this->end,
            'inputs' => $this->inputs,
            'case_id' => $this->case_id,
            'media_id' => $this->media_id,
            'media_name' => $this->media_id ? Media::where('id', $this->media_id)->first()?->name : null,
            'place_id' => $this->place_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

    }
}
