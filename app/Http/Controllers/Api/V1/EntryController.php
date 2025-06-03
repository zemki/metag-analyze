<?php

namespace App\Http\Controllers\Api\V1;

use App\Cases;
use App\Entry;
use App\Files;
use App\Http\Controllers\Controller;
use App\Http\Resources\Entry as EntryResource;
use App\Media;
use Crypt;
use Exception;
use File;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class EntryController extends Controller
{
    protected const BEGIN = 'begin';

    protected const REQUIRED = 'required';

    protected const MEDIA_ID = 'media_id';

    protected const ENTITY_ID = 'entity_id';

    protected const INPUTS = 'inputs';

    protected const ENTRIES = 'entries';

    /**
     * Get entries for a specific case
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function entriesByCase(Cases $case)
    {
        return EntryResource::collection($case->entries->sortByDesc(self::BEGIN));
    }

    /**
     * Store a new entry
     *
     * @return Response
     *
     * @throws AuthorizationException
     */
    public function store(Cases $case)
    {
        $this->authorize('store', [Entry::class, $case]);

        // V1 primarily uses media_id but accepts entity_id for forward compatibility
        if (request()->has('media')) {
            request()->merge([self::MEDIA_ID => request()->media]);
        }

        // Accept entity_id for forward compatibility
        if (request()->has(self::ENTITY_ID)) {
            request()->merge([self::MEDIA_ID => request()->input(self::ENTITY_ID)]);
        }

        // Also accept entity for forward compatibility
        if (request()->has('entity')) {
            request()->merge([self::MEDIA_ID => request()->entity]);
        }

        $attributes = request()->validate([
            self::BEGIN => self::REQUIRED,
            'end' => self::REQUIRED,
            'case_id' => self::REQUIRED,
            self::MEDIA_ID => self::REQUIRED,
            self::INPUTS => 'nullable',
        ]);

        $isComingFromBackend = is_numeric($attributes[self::MEDIA_ID]);

        if ($isComingFromBackend) {
            $attributes[self::INPUTS] = json_encode($attributes[self::INPUTS]);
        } else {
            $attributes[self::MEDIA_ID] = Media::firstOrCreate(['name' => $attributes[self::MEDIA_ID]])->id;
            $attributes[self::INPUTS] = json_encode(request()->inputs);
        }
        $entry = Entry::create($attributes);

        if (request()->hasHeader('x-file-token') && request()->header('x-file-token') !== '0' && request()->header('x-file-token') !== '') {
            $appToken = request()->header('x-file-token');
            $clientFileTokenIsSameWithServer = strcmp(Crypt::decryptString($case->file_token), $appToken) !== 0;
            if ($clientFileTokenIsSameWithServer) {
                return response('You are not authorized!', 403);
            } else {
                if (request()->has('audio') && ! empty(request()->input('audio'))) {
                    // save file!
                    $filename = '';
                    Files::storeEntryFile(request()->input('audio'), 'audio', $case->project, $case, $entry, $filename);
                } elseif (request()->has('image') && ! empty(request()->input('image'))) {
                    // save file!
                    $filename = '';
                    Files::storeEntryFile(request()->input('image'), 'image', $case->project, $case, $entry, $filename);
                }
            }
        }

        return response(['id' => $entry->id], 200);
    }

    /**
     * Update an existing entry
     *
     * @return ResponseFactory|Response
     *
     * @throws AuthorizationException
     */
    public function update(Cases $case, Entry $entry)
    {
        $this->authorize('update', [Entry::class, $entry]);

        // V1 primarily uses media_id but accepts entity_id for forward compatibility
        if (request()->has('media')) {
            request()->merge([self::MEDIA_ID => request()->media]);
        }

        // Accept entity_id for forward compatibility
        if (request()->has(self::ENTITY_ID)) {
            request()->merge([self::MEDIA_ID => request()->input(self::ENTITY_ID)]);
        }

        // Also accept entity for forward compatibility
        if (request()->has('entity')) {
            request()->merge([self::MEDIA_ID => request()->entity]);
        }

        $attributes = request()->validate([
            self::BEGIN => self::REQUIRED,
            'end' => self::REQUIRED,
            'case_id' => self::REQUIRED,
            self::MEDIA_ID => self::REQUIRED,
            self::INPUTS => 'nullable',
        ]);

        if (is_string($attributes[self::MEDIA_ID])) {
            $attributes[self::MEDIA_ID] = Media::firstOrCreate(['name' => $attributes[self::MEDIA_ID]])->id;
        }

        $oldInputs = $entry->inputs;

        $attributes[self::INPUTS] = json_encode($attributes[self::INPUTS]);
        $oldEntry = $entry->replicate();
        $entry->update($attributes);
        $entry->save();

        if ($case->isConsultable() && ! array_key_exists('firstValue', json_decode($oldInputs, true))) {
            $entry->update(['inputs->firstValue' => $oldEntry]);
        }

        if (request()->hasHeader('x-file-token') && request()->header('x-file-token') !== '0' && request()->header('x-file-token') !== '') {
            $appToken = request()->header('x-file-token');
            $clientFileTokenIsSameWithServer = strcmp(Crypt::decryptString($case->file_token), $appToken) !== 0;
            $keepExistingAudioFile = request()->has('audio') && empty(request()->input('audio') && property_exists($oldInputs, 'file'));

            if ($clientFileTokenIsSameWithServer) {
                return response('You are not authorized!', 403);
            } else {
                if (request()->has('audio') && ! empty(request()->input('audio'))) {
                    // update file!
                    $filename = '';
                    Files::updateEntryFile(request()->input('audio'), 'audio', $case->project, $case, $entry, $filename, $oldInputs);
                } elseif (request()->has('image') && ! empty(request()->input('image'))) {
                    $filename = '';
                    Files::storeEntryFile(request()->input('image'), 'image', $case->project, $case, $entry, $filename);
                } elseif ($keepExistingAudioFile) {
                    $entry->update([
                        'inputs->file' => json_decode($oldInputs)->file,
                    ]);
                }
            }
        }

        return response(['id' => $entry->id], 200);
    }

    /**
     * Delete an entry
     *
     * @return ResponseFactory|Response
     */
    public function destroy(Cases $case, Entry $entry)
    {
        try {
            if ($case->file_token !== '') {
                if (isset(json_decode($entry->inputs)->file)) {
                    $file_id = json_decode($entry->inputs)->file;
                    $file = Files::where('id', '=', $file_id)->first();
                    File::delete($file->path);
                    $file->delete();
                }
            }
            $entry->delete();
        } catch (Exception $error) {
            echo 'Caught exception: ', $error->getMessage(), "\n";

            return response('error!', 500);
        }

        return response('entry deleted', 200);
    }
}
