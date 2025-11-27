# MART Documentation

MART (Mobile Assessment Research Tool) is the mobile experience sampling component of Metag Analyze.

## Overview

MART enables researchers to:
- Create questionnaire schedules (single or repeating)
- Collect device information and usage statistics
- Track participant responses with version control
- Support dynamic study dates per participant

## Documentation

- [API Reference](./API.md) - Endpoints and authentication
- [Data Mapping](./DATA_MAPPING.md) - How API types map to database

## Key Concepts

### Questionnaire Types

| Type | Description |
|------|-------------|
| Single | Shown once at a specific date/time |
| Repeating | Shown multiple times within a date range |

### Question Types

| Type | Description |
|------|-------------|
| `radio` | Single choice |
| `radioWithText` | Single choice with "Other" text field |
| `checkbox` | Multiple choice |
| `checkboxWithText` | Multiple choice with "Other" text field |
| `range` | Numeric slider |
| `rangeValues` | Labeled slider |
| `text` | Short text input |
| `textarea` | Long text input |
| `number` | Numeric input (with optional min/max) |
| `photoUpload` | Camera/gallery photo |
| `audioUpload` | Audio recording |
| `videoUpload` | Video recording |

### Page Types

| Type | Purpose |
|------|---------|
| `success` | Shown after questionnaire completion |
| `android_stats_permission` | Android log data access instructions |
| `android_notification_permission` | Android notification permission |
| `ios_notification_permission` | iOS notification permission |

## Database

MART uses a separate database (`mart` connection) with tables:
- `mart_projects` - Links to main projects
- `mart_schedules` - Questionnaire configurations
- `mart_questions` - Questions with UUID tracking
- `mart_entries` - Submission metadata
- `mart_answers` - Individual responses
- `mart_pages` - Information pages
- `mart_case_schedules` - Per-participant date overrides
