<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Entry;
use App\Http\Resources\Entry as EntryResource;
use App\Media;
use Exception;
use Helper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EntryController extends Controller
{
    protected const BEGIN = 'begin';
    protected const REQUIRED = 'required';
    protected const MEDIA_ID = 'media_id';
    protected const INPUTS = 'inputs';
    protected const ENTRIES = 'entries';

    /**
     * @param Cases $case
     * @return AnonymousResourceCollection
     */
    public function entriesByCase(Cases $case)
    {
        dump(EntryResource::collection($case->entries->sortByDesc(self::BEGIN)));
        return EntryResource::collection($case->entries->sortByDesc(self::BEGIN));
    }

    /**
     * Store a newly created resource in storage.
     * @param Cases $case
     * @return Response
     * @throws AuthorizationException
     */
    public function store(Cases $case)
    {

        $this->authorize('update', [Entry::class, $case]);
        $attributes = request()->validate([
            self::BEGIN => self::REQUIRED,
            'end' => self::REQUIRED,
            'case_id' => self::REQUIRED,
            self::MEDIA_ID => self::REQUIRED,
            self::INPUTS => 'nullable',
        ]);

        if (is_numeric($attributes[self::MEDIA_ID]))
        {

            $attributes[self::INPUTS] = json_encode($attributes[self::INPUTS]);
        } else
        {
            $attributes[self::MEDIA_ID] = Media::firstOrCreate(['name' => $attributes[self::MEDIA_ID]])->id;
            $attributes[self::INPUTS] = json_encode($attributes[self::INPUTS]);
        }
        $entry = Entry::create($attributes);
        return response(['id' => $entry->id], 200);
    }

    public function update(Cases $case, Entry $entry)
    {

        $this->authorize('update', [Entry::class, $case]);
        $attributes = request()->validate([
            self::BEGIN => self::REQUIRED,
            'end' => self::REQUIRED,
            'case_id' => self::REQUIRED,
            self::MEDIA_ID => self::REQUIRED,
            self::INPUTS => 'nullable',
        ]);
        if (is_string($attributes[self::MEDIA_ID]))
        {
            $attributes[self::MEDIA_ID] = Media::firstOrCreate(['name' => $attributes[self::MEDIA_ID]])->id;
        }
        $attributes[self::INPUTS] = json_encode($attributes[self::INPUTS]);
        $entry->update($attributes);
        $entry->save();
        return response(['id' => $entry->id], 200);
    }

    /**
     * @param Cases $case
     * @return Factory|View
     */
    public function consult(Cases $case)
    {
        $data[self::ENTRIES] = $case->entries()
            ->join('media', 'entries.media_id', '=', 'media.id')->get()->map->only(['name', self::BEGIN, 'end'])
            ->flatten()->chunk(3)->toArray();
        $data[self::ENTRIES] = array_map('array_values', $data[self::ENTRIES]);
        return view('entries.index', $data);
    }

    /**
     * @param Cases $case
     * @param Entry $entry
     * @return ResponseFactory|Response
     */
    public function destroy(Cases $case, Entry $entry)
    {
        try
        {
            $entry->delete();
        } catch (Exception $error)
        {
            echo 'Caught exception: ', $error->getMessage(), "\n";
        }
        return response("entry deleted", 200);
    }
}
