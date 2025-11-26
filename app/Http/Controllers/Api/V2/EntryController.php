<?php

namespace App\Http\Controllers\Api\V2;

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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Log;

class EntryController extends Controller
{
    protected const BEGIN = 'begin';

    protected const REQUIRED = 'required';

    protected const ENTITY_ID = 'entity_id';    // V2 uses entity_id exclusively

    protected const INPUTS = 'inputs';

    protected const ENTRIES = 'entries';

    /**
     * Get entries for a specific case
     *
     * @return AnonymousResourceCollection
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

        // In V2, we only use entity_id
        // If someone directly passes 'entity' param, convert it to entity_id
        if (request()->has('entity') && ! request()->has(self::ENTITY_ID)) {
            request()->merge([self::ENTITY_ID => request()->entity]);
        }

        // Check if project uses entity field
        $project = $case->project;
        $useEntity = $project->use_entity ?? true;

        // Conditionally require entity_id based on project settings
        $entityRule = $useEntity ? self::REQUIRED : 'nullable';

        $attributes = request()->validate([
            self::BEGIN => self::REQUIRED,
            'end' => self::REQUIRED,
            'case_id' => self::REQUIRED,
            self::ENTITY_ID => $entityRule,
            self::INPUTS => 'nullable',
        ]);

        // Convert Unix timestamps to MySQL datetime format if needed
        $attributes[self::BEGIN] = $this->convertTimestampToDatetime($attributes[self::BEGIN]);
        $attributes['end'] = $this->convertTimestampToDatetime($attributes['end']);

        // Only process entity_id if it's provided (when use_entity is true)
        if (isset($attributes[self::ENTITY_ID]) && $attributes[self::ENTITY_ID] !== null) {
            $isComingFromBackend = is_numeric($attributes[self::ENTITY_ID]);

            if ($isComingFromBackend) {
                $attributes[self::INPUTS] = json_encode($attributes[self::INPUTS]);
            } else {
                // Create or find the media entry but store as entity_id
                $attributes[self::ENTITY_ID] = Media::firstOrCreate(['name' => $attributes[self::ENTITY_ID]])->id;
                $attributes[self::INPUTS] = json_encode(request()->inputs);
            }

            // Convert entity_id to media_id for database column (V2 API compatibility)
            $attributes['media_id'] = $attributes[self::ENTITY_ID];
            unset($attributes[self::ENTITY_ID]);
        } else {
            // When entity is not used, just encode inputs
            $attributes[self::INPUTS] = json_encode($attributes[self::INPUTS] ?? request()->inputs);
            $attributes['media_id'] = null;
            unset($attributes[self::ENTITY_ID]);
        }

        $entry = Entry::create($attributes);

        if (request()->hasHeader('x-file-token') && request()->header('x-file-token') !== '0' && request()->header('x-file-token') !== '') {
            $appToken = request()->header('x-file-token');
            $clientFileTokenIsSameWithServer = ! hash_equals(Crypt::decryptString($case->file_token), $appToken);
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

        // In V2, we only use entity_id
        // If someone directly passes 'entity' param, convert it to entity_id
        if (request()->has('entity') && ! request()->has(self::ENTITY_ID)) {
            request()->merge([self::ENTITY_ID => request()->entity]);
        }

        // Check if project uses entity field
        $project = $case->project;
        $useEntity = $project->use_entity ?? true;

        // Conditionally require entity_id based on project settings
        $entityRule = $useEntity ? self::REQUIRED : 'nullable';

        $attributes = request()->validate([
            self::BEGIN => self::REQUIRED,
            'end' => self::REQUIRED,
            'case_id' => self::REQUIRED,
            self::ENTITY_ID => $entityRule,
            self::INPUTS => 'nullable',
        ]);

        // Convert Unix timestamps to MySQL datetime format if needed
        $attributes[self::BEGIN] = $this->convertTimestampToDatetime($attributes[self::BEGIN]);
        $attributes['end'] = $this->convertTimestampToDatetime($attributes['end']);

        // Only process entity_id if it's provided (when use_entity is true)
        if (isset($attributes[self::ENTITY_ID]) && $attributes[self::ENTITY_ID] !== null) {
            if (is_string($attributes[self::ENTITY_ID])) {
                // Create or find the media entry but store as entity_id
                $attributes[self::ENTITY_ID] = Media::firstOrCreate(['name' => $attributes[self::ENTITY_ID]])->id;
            }

            // Convert entity_id to media_id for database column (V2 API compatibility)
            $attributes['media_id'] = $attributes[self::ENTITY_ID];
            unset($attributes[self::ENTITY_ID]);
        } else {
            // When entity is not used, set to null
            $attributes['media_id'] = null;
            unset($attributes[self::ENTITY_ID]);
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
            Log::error('Entry deletion failed', [
                'entry_id' => $entry->id,
                'case_id' => $case->id,
                'error' => $error->getMessage(),
            ]);

            return response(['error' => 'Unable to delete entry'], 500);
        }

        return response('entry deleted', 200);
    }

    /**
     * Convert Unix timestamp to MySQL datetime format.
     * Handles both seconds (10 digits) and milliseconds (13 digits).
     *
     * @param mixed $value The value to convert
     * @return string The datetime string in Y-m-d H:i:s format, or original value if not a timestamp
     */
    private function convertTimestampToDatetime($value): string
    {
        if (! is_numeric($value)) {
            return $value;
        }

        $length = strlen((string) $value);

        // 10 digits = Unix timestamp in seconds
        if ($length === 10) {
            return date('Y-m-d H:i:s', (int) $value);
        }

        // 13 digits = Unix timestamp in milliseconds
        if ($length === 13) {
            return date('Y-m-d H:i:s', (int) ($value / 1000));
        }

        // Return as-is if not a recognized timestamp format
        return $value;
    }
}
