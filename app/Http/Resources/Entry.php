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

        // Load file data if this entry has an associated file
        $fileObject = null;
        $filePath = null;

        if (isset($inputs['file'])) {
            $file = $this->file();
            if ($file && file_exists($file->path)) {
                try {
                    // Decrypt the file content
                    $decryptedContent = decrypt(file_get_contents($file->path));

                    $fileObject = [
                        'id' => $file->id,
                        'audiofile' => $decryptedContent,
                        'created_at' => $file->created_at,
                        'size' => $file->size,
                    ];

                    $filePath = $file->path;
                } catch (\Exception $e) {
                    \Log::error('Failed to decrypt file for entry', [
                        'entry_id' => $this->id,
                        'file_id' => $file->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

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
            'file_object' => $fileObject,
            'file_path' => $filePath,
        ];

    }
}
