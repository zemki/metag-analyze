# Entity Field Migration Guide

This document outlines the migration from 'media' to 'entity' fields in the Metag Analyze application and provides guidelines for developers working with the API.

## Overview

The application is transitioning from using 'media' fields to 'entity' fields. To ensure backward compatibility, both field names are supported through a versioned API approach:

- **V1 API** (Legacy): Uses `media_id` as the primary field
- **V2 API** (New): Uses `entity_id` as the primary field

## API Version Selection

API version is determined by:

1. **Project creation date**: Projects created after the cutoff date (defined in `config('app.api_v2_cutoff_date')`, currently March 21, 2025) automatically use V2
2. **Environment setting**: Setting `FORCE_API_V2=true` in `.env` forces all projects to use V2 regardless of creation date
3. **Explicit versioning**: Clients can use `/api/v1/` or `/api/v2/` endpoints directly

The login response includes `api_version` which indicates which version is being used.

## Controller Structure

Three separate controllers handle different aspects of the application:

1. **Main EntryController** (`app/Http/Controllers/EntryController.php`):
   - Handles web routes
   - Supports both `media_id` and `entity_id` for compatibility
   - Maps `entity_id` to `media_id` for database storage

2. **V1 EntryController** (`app/Http/Controllers/Api/V1/EntryController.php`):
   - Handles API endpoints with `/api/v1/` prefix
   - Primarily uses `media_id` but accepts `entity_id` for forward compatibility

3. **V2 EntryController** (`app/Http/Controllers/Api/V2/EntryController.php`):
   - Handles API endpoints with `/api/v2/` prefix
   - Exclusively uses `entity_id` (no references to `media_id`)

## Database Storage

The database still uses the `media_id` column. For V2 API calls:

1. The Entry model's `boot()` method automatically maps `entity_id` to `media_id`
2. The model provides an `entity()` relationship method that maps to the same underlying data as `media()`

## Guidelines for Future Development

### When Adding New Features

1. **Web Controllers**:
   - Add support for both `media_id` and `entity_id` in the main controllers
   - Always map `entity_id` to `media_id` for database operations

2. **API V1 Controllers**:
   - Primarily use `media_id` but accept `entity_id` for compatibility
   - Return both field names in responses

3. **API V2 Controllers**:
   - Exclusively use `entity_id` (no references to `media_id`)
   - Do not include `media_id` in responses

### Client Development

When developing API clients:

1. **Legacy Clients**:
   - Continue using the `/api/v1/` endpoints with `media_id`
   - Will continue to work indefinitely

2. **New Clients**:
   - Use the `/api/v2/` endpoints with `entity_id`
   - Set appropriate entity name and use flag in project settings

### Testing API Endpoints

To test API endpoints:

1. **Login and get token**:
   ```bash
   curl -X POST http://yourdomain.test/api/login -d "email=user@example.com&password=password"
   ```
   The response includes `api_version` indicating which version is being used.

2. **Test V1 endpoints**:
   ```bash
   curl -H "Authorization: Bearer TOKEN" http://yourdomain.test/api/v1/...
   ```

3. **Test V2 endpoints**:
   ```bash
   curl -H "Authorization: Bearer TOKEN" http://yourdomain.test/api/v2/...
   ```

## Common Issues and Solutions

1. **Entity field not saved**: Make sure you're using the correct field name for the API version (`media_id` for V1, `entity_id` for V2)

2. **Missing media field in V2 response**: V2 API only returns `entity` fields, not `media` fields

3. **Version selection issues**: Check the project creation date against the cutoff date or set `FORCE_API_V2=true` in `.env`
