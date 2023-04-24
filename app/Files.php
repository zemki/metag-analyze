<?php

namespace App;

use File;
use Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Image;

/**
 * App\Files
 *
 * @property int $id
 * @property string $type
 * @property string $path
 * @property string $size
 * @property int|null $case_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Files newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Files newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Files query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Files whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Files whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Files whereInterviewId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Files wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Files whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Files whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Files whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 *
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Cases|null $case
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Files whereCaseId($value)
 */
class Files extends Model
{
    /**
     * @var string
     */
    protected $table = 'files_cases';

    /**
     * @var array
     */
    protected $fillable = [
        'type', 'path', 'size', 'case_id',
    ];

    /**
     * @return mixed
     */
    public static function occupiedStorage()
    {
        return Files::all()->sum('size');
    }

    public static function storeEntryFile($file, $type, Project $project, Cases $case, Entry $entry, &$name): void
    {
        $name = 'interview_' . $case->name . date('dmyhis');
        $projectPath = storage_path('app/project' . $project->id . '/files/');
        $extension = Helper::extension($file);
        $notEncryptedContent = $projectPath . $name . '.' . $extension;
        File::isDirectory($projectPath) or File::makeDirectory($projectPath, 0775, true, true);
        if ($type === 'audio') {
            file_put_contents($notEncryptedContent, base64_decode(substr(explode(',', $file, 2)[1], 0, -1)));
        //exec("ffmpeg -i " . storage_path('app/project' . $project->id . '/files/') . 'entry_audio.bin' . " " . storage_path('app/project' . $project->id . '/files/') . 'entry_audio.mp3');
        } else {
        }
        // open file a image resource
        //  Image::make($sorting['sortingscreenshot'])->save($notEncryptedContent);

        $arr = explode(',', $file, 2);
        self::SaveEncryptedFile($project, $name, $arr, $notEncryptedContent, $encryptedPath);
        self::SaveFileDbRecord($case->id, $name, $projectPath, $encryptedPath, $entry);
    }

    public static function updateEntryFile($file, $type, Project $project, Cases $case, Entry $entry, &$name, $oldInputs): void
    {
        $oldInputs = json_decode($oldInputs);

        ray($oldInputs);

        if (property_exists($oldInputs, 'file')) {
            $existingFile = Files::where('id', '=', $oldInputs->file)->first();
            File::delete($existingFile->path);
            $existingFile->delete();
        }

        $name = 'interview_' . $case->name . date('dmyhis');
        $projectPath = storage_path('app/project' . $project->id . '/files/');
        $extension = Helper::extension($file);
        $notEncryptedContent = $projectPath . $name . '.' . $extension;
        File::isDirectory($projectPath) or File::makeDirectory($projectPath, 0775, true, true);
        if ($type === 'audio') {
            file_put_contents($notEncryptedContent, base64_decode(substr(explode(',', $file, 2)[1], 0, -1)));
        } else {
        }
        // open file a image resource
        //  Image::make($sorting['sortingscreenshot'])->save($notEncryptedContent);

        $arr = explode(',', $file, 2);
        self::SaveEncryptedFile($project, $name, $arr, $notEncryptedContent, $encryptedPath);
        self::SaveFileDbRecord($case->id, $name, $projectPath, $encryptedPath, $entry);
    }

    private static function SaveEncryptedFile(Project $project, &$name, array $arr, string $notEncryptedContent, &$encryptedPath): void
    {
        $base64firstpart = $arr[0];
        $encryptedContent = encrypt($base64firstpart . ',' . base64_encode(file_get_contents($notEncryptedContent)));
        $encryptedPath = 'project' . $project->id . '/files/' . $name . '.mfile';
        // Store the encrypted Content
        Storage::put($encryptedPath, $encryptedContent);
        File::delete($notEncryptedContent);
    }

    private static function SaveFileDbRecord($caseid, &$name, string $projectPath, string $encryptedPath, Entry $entry): void
    {
        $file_interview = new Files();
        // write here if audio or image
        $file_interview->type = 'file_';
        $file_interview->path = $projectPath . $name . '.mfile';
        if (is_file(storage_path('app/' . $encryptedPath))) {
            $file_interview->size = File::size(storage_path('app/' . $encryptedPath));
        } else {
            $file_interview->size = 0;
        }
        $file_interview->case_id = $caseid;
        $file_interview->save();
        $entry->update(
            [
                'inputs->file' => $file_interview->id,
            ],
        );
    }

    public function case()
    {
        return $this->belongsTo(Cases::class);
    }
}
