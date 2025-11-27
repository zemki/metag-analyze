<?php

namespace App\Mart;

use App\Cases;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * MartFile Model
 *
 * Represents uploaded files in MART questionnaire responses.
 *
 * File Types:
 * - 'photo': Images (jpeg, png, gif, webp, heic)
 * - 'video': Videos (mp4, quicktime, webm, 3gpp)
 * - 'audio': Audio (mpeg, mp4, wav, ogg, aac)
 * - 'document': Documents (pdf, images)
 *
 * Files are stored encrypted in: storage/app/mart/project{id}/files/{case_id}/{uuid}.mfile
 */
class MartFile extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'mart';

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_files';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'mart_entry_id',
        'case_id',
        'project_id',
        'question_uuid',
        'file_type',
        'mime_type',
        'original_name',
        'storage_path',
        'size',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Allowed MIME types per file type.
     */
    public const ALLOWED_MIME_TYPES = [
        'photo' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/heic',
            'image/heif',
        ],
        'video' => [
            'video/mp4',
            'video/quicktime',
            'video/webm',
            'video/3gpp',
            'video/x-msvideo',
        ],
        'audio' => [
            'audio/mpeg',
            'audio/mp4',
            'audio/wav',
            'audio/ogg',
            'audio/aac',
            'audio/x-m4a',
            'audio/webm',
            'audio/flac',
        ],
        'document' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ],
    ];

    /**
     * File extensions per MIME type.
     */
    public const MIME_TO_EXTENSION = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
        'video/mp4' => 'mp4',
        'video/quicktime' => 'mov',
        'video/webm' => 'webm',
        'video/3gpp' => '3gp',
        'video/x-msvideo' => 'avi',
        'audio/mpeg' => 'mp3',
        'audio/mp4' => 'm4a',
        'audio/wav' => 'wav',
        'audio/ogg' => 'ogg',
        'audio/aac' => 'aac',
        'audio/x-m4a' => 'm4a',
        'audio/webm' => 'webm',
        'audio/flac' => 'flac',
        'application/pdf' => 'pdf',
    ];

    /**
     * Maximum file size in bytes (50MB).
     */
    public const MAX_FILE_SIZE = 50 * 1024 * 1024;

    /**
     * Boot function to auto-generate UUID.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the MART entry this file belongs to.
     */
    public function martEntry(): BelongsTo
    {
        return $this->belongsTo(MartEntry::class, 'mart_entry_id');
    }

    /**
     * Get the case from main database.
     * Note: Cross-database query.
     */
    public function case(): ?Cases
    {
        return Cases::find($this->case_id);
    }

    /**
     * Scope to get files for a specific case.
     */
    public function scopeForCase($query, $caseId)
    {
        return $query->where('case_id', $caseId);
    }

    /**
     * Scope to get files for a specific entry.
     */
    public function scopeForEntry($query, $entryId)
    {
        return $query->where('mart_entry_id', $entryId);
    }

    /**
     * Scope to get files for a specific question.
     */
    public function scopeForQuestion($query, $questionUuid)
    {
        return $query->where('question_uuid', $questionUuid);
    }

    /**
     * Scope to get unlinked files (not yet attached to an entry).
     */
    public function scopeUnlinked($query)
    {
        return $query->whereNull('mart_entry_id');
    }

    /**
     * Generate storage path for a file.
     */
    public static function generateStoragePath(int $projectId, int $caseId, string $uuid): string
    {
        return "mart/project{$projectId}/files/{$caseId}/{$uuid}.mfile";
    }

    /**
     * Store encrypted file content.
     *
     * @param string $content Raw file content (decoded from base64)
     * @return bool
     */
    public function storeEncrypted(string $content): bool
    {
        $encrypted = Crypt::encrypt($content);

        return Storage::put($this->storage_path, $encrypted);
    }

    /**
     * Retrieve and decrypt file content.
     *
     * @return string|null Raw file content
     */
    public function getDecryptedContent(): ?string
    {
        if (!Storage::exists($this->storage_path)) {
            return null;
        }

        $encrypted = Storage::get($this->storage_path);

        return Crypt::decrypt($encrypted);
    }

    /**
     * Delete the stored file.
     *
     * @return bool
     */
    public function deleteFile(): bool
    {
        if (Storage::exists($this->storage_path)) {
            return Storage::delete($this->storage_path);
        }

        return true;
    }

    /**
     * Check if a MIME type is allowed for a file type.
     *
     * @param string $fileType photo, video, audio, document
     * @param string $mimeType The MIME type to check
     * @return bool
     */
    public static function isAllowedMimeType(string $fileType, string $mimeType): bool
    {
        $allowedTypes = self::ALLOWED_MIME_TYPES[$fileType] ?? [];

        return in_array($mimeType, $allowedTypes, true);
    }

    /**
     * Detect MIME type from file content.
     *
     * @param string $content Raw file content
     * @return string|null
     */
    public static function detectMimeType(string $content): ?string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->buffer($content) ?: null;
    }

    /**
     * Get file extension from MIME type.
     *
     * @param string $mimeType
     * @return string
     */
    public static function getExtensionFromMime(string $mimeType): string
    {
        return self::MIME_TO_EXTENSION[$mimeType] ?? 'bin';
    }

    /**
     * Link this file to a MART entry.
     *
     * @param int $entryId
     * @return bool
     */
    public function linkToEntry(int $entryId): bool
    {
        $this->mart_entry_id = $entryId;

        return $this->save();
    }
}
