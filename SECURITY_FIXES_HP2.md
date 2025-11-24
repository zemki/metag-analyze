# Security Fix: HP-2 Loose File Extension Parsing

## Vulnerability Summary

**Severity**: High
**Location**: `app/Helpers/Helper.php` (Method: `extension()`)
**CVE**: N/A (Internal security audit finding)

### Original Vulnerability

The original implementation naively parsed the MIME type from the base64 data URI header:

```php
// VULNERABLE CODE (BEFORE)
public static function extension($uri)
{
    $img = explode(',', $uri);
    $ini = substr($img[0], 11); // Assumes "data:image/" prefix
    $type = explode(';', $ini);
    return $type[0]; // Returns the CLAIMED MIME type
}
```

**Attack Vector**:
- Attacker sends: `data:application/x-php;base64,<?php system($_GET['cmd']); ?>`
- System extracts: `application/x-php` (or mangled version)
- File saved as: `interview_case123.application/x-php` or similar
- If storage moved to public directory: **Remote Code Execution (RCE)**

## Implemented Fix

### Secure Implementation

```php
// SECURE CODE (AFTER)
public static function extension($uri)
{
    // 1. Whitelist of allowed MIME types → safe extensions
    $allowedMimeTypes = [
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        // ... etc
    ];

    // 2. Decode base64 data
    $parts = explode(',', $uri, 2);
    $data = base64_decode($parts[1], true);

    // 3. Use finfo to detect ACTUAL MIME type from content
    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $detectedMimeType = $finfo->buffer($data);

    // 4. Validate against whitelist
    if (!isset($allowedMimeTypes[$detectedMimeType])) {
        throw new \InvalidArgumentException("File type not allowed");
    }

    // 5. Return safe extension
    return $allowedMimeTypes[$detectedMimeType];
}
```

## Security Improvements

### ✅ Content-Based Validation
- **Before**: Trusted the declared MIME type in data URI
- **After**: Inspects actual file content using PHP's `finfo` extension

### ✅ Whitelist Enforcement
- **Before**: Accepted any MIME type
- **After**: Only allows specific audio and image formats needed for research

### ✅ Extension Mapping
- **Before**: Used raw MIME type as extension (e.g., `audio/mpeg`)
- **After**: Maps MIME types to safe extensions (e.g., `mp3`)

### ✅ Logging & Monitoring
- **Before**: Silent failures
- **After**: Logs all validation failures for security monitoring

### ✅ Exception Handling
- **Before**: Continued with invalid/dangerous files
- **After**: Throws exception to prevent insecure file storage

## Allowed File Types

### Audio Formats (Research Data Collection)
- MP3 (`audio/mpeg`, `audio/mp3`, `audio/x-mpeg`)
- M4A (`audio/mp4`, `audio/x-m4a`)
- AAC (`audio/aac`)
- WAV (`audio/wav`, `audio/x-wav`, `audio/wave`)
- OGG (`audio/ogg`)
- WebM (`audio/webm`)
- FLAC (`audio/flac`)

### Image Formats (Optional Upload)
- JPEG (`image/jpeg`, `image/jpg`)
- PNG (`image/png`)
- GIF (`image/gif`)
- WebP (`image/webp`)
- SVG (`image/svg+xml`)

## Impact Assessment

### ✅ Backward Compatibility
- **Legitimate uploads**: Continue to work (audio/image files)
- **Existing files**: Not affected (already stored)
- **Mobile apps**: No changes required (send same data URIs)
- **API contracts**: Unchanged

### ⚠️ Breaking Changes
- **Malicious uploads**: Now rejected with error
- **Non-whitelisted formats**: Rejected (e.g., PDF, video files)
- **Error responses**: Return 400/422 instead of silently accepting

### 📊 Affected Components
1. **Entry Controllers** (V1, V2, Main)
   - `app/Http/Controllers/EntryController.php`
   - `app/Http/Controllers/Api/V1/EntryController.php`
   - `app/Http/Controllers/Api/V2/EntryController.php`
2. **File Storage**
   - `app/Files.php` (`storeEntryFile`, `updateEntryFile`)
3. **Mobile Apps**
   - Android app (audio recording uploads)
   - iOS app (audio recording uploads)

## Testing Recommendations

### Unit Tests
```php
// Test legitimate audio file
$mp3DataUri = 'data:audio/mpeg;base64,' . base64_encode($realMp3Data);
$extension = Helper::extension($mp3DataUri);
// Expected: 'mp3'

// Test malicious PHP file
$phpDataUri = 'data:application/x-php;base64,' . base64_encode('<?php phpinfo(); ?>');
try {
    $extension = Helper::extension($phpDataUri);
    // Expected: InvalidArgumentException thrown
} catch (\InvalidArgumentException $e) {
    // Success - malicious file blocked
}
```

### Integration Tests
1. Upload legitimate audio file via mobile app → Should succeed
2. Upload image file → Should succeed
3. Attempt to upload PHP file disguised as audio → Should fail with error
4. Check Laravel logs for security warnings

### Monitoring
- Monitor Laravel logs for: `File upload security validation failed`
- Track rejected file types to identify attack patterns
- Review allowed MIME types list quarterly

## Deployment Notes

### Pre-Deployment
1. ✅ Review whitelist of allowed MIME types
2. ✅ Ensure PHP `fileinfo` extension is installed
3. ✅ Test with real mobile app uploads (staging environment)
4. ✅ Update monitoring/alerting for new log messages

### Post-Deployment
1. Monitor error logs for false positives
2. Track rejected file uploads
3. Communicate with mobile app users if needed
4. Document any additional MIME types that need whitelisting

## Additional Recommendations

### Short-Term (Completed in this fix)
- ✅ Content-based MIME type validation
- ✅ Whitelist enforcement
- ✅ Security logging

### Medium-Term (Future Enhancements)
- [ ] Add file size validation (prevent DoS via large uploads)
- [ ] Implement virus scanning integration (ClamAV)
- [ ] Add rate limiting on file uploads per user/case
- [ ] Create Filament admin panel for viewing blocked uploads

### Long-Term (Best Practices)
- [ ] Move file storage to S3 with signed URLs
- [ ] Implement Content Security Policy (CSP) headers
- [ ] Add file integrity verification (checksums)
- [ ] Periodic security audits of file upload system

## References

- **Security Audit**: HP-2: Loose File Extension Parsing
- **OWASP**: [Unrestricted File Upload](https://owasp.org/www-community/vulnerabilities/Unrestricted_File_Upload)
- **PHP Documentation**: [finfo extension](https://www.php.net/manual/en/book.fileinfo.php)
- **Code Location**: `app/Helpers/Helper.php:38-115`

## Questions & Support

If legitimate file uploads are being rejected:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Look for: `File upload security validation failed`
3. Review detected MIME type in log entry
4. If MIME type is legitimate, add to whitelist in `Helper.php:48-69`

---

**Applied**: 2025-11-24
**Verified By**: Security audit implementation
**Status**: ✅ Deployed
