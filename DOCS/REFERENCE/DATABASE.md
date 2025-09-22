# Database Structure

## Overview

Metag-Analyze uses MySQL 8.0+ with a hierarchical data structure designed for ESM (Experience Sampling Method) research studies. The system follows Laravel's Eloquent ORM conventions with migrations for version control.

## Entity Relationship Diagram

```
┌─────────────┐         ┌──────────┐         ┌──────────┐
│   Projects  │────┬───>│   Cases  │────┬───>│  Entries │
└─────────────┘    │    └──────────┘    │    └──────────┘
       │           │          │          │
       │           │          │          │
       ▼           │          ▼          │
┌─────────────┐    │    ┌──────────┐    │
│    Media    │    │    │   Users  │<───┘
└─────────────┘    │    └──────────┘
       ▲           │
       │           │
┌─────────────┐    │
│ Schedules   │<───┘
└─────────────┘
```

## Core Tables

### projects
Research studies/projects created by researchers.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(200) | Project title |
| description | varchar(250) | Brief description |
| inputs | json | Custom questions/fields configuration |
| created_by | int | User who created the project |
| is_locked | boolean | Prevents structural changes |
| entity_name | varchar | Custom name for entities |
| use_entity | boolean | Enable/disable entity usage |
| duration | varchar | Default case duration |
| duration_unit | varchar | Duration unit (days/weeks/months) |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

**Relationships:**
- Has many Cases
- Has many Media (through pivot)
- Has many Schedules
- Belongs to many Users (collaborators)

### cases
Individual participants or data collection units within projects.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar(200) | Case identifier/participant code |
| project_id | bigint | Foreign key to projects |
| user_id | bigint | Optional assigned user |
| status | enum | pending/active/completed |
| duration | varchar | Data collection period |
| case_file_key | varchar | Unique key for file management |
| token | varchar | Authentication token for mobile |
| started_at | timestamp | When case became active |
| completed_at | timestamp | When case was completed |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

**Relationships:**
- Belongs to Project
- Belongs to User (optional)
- Has many Entries
- Has many Files

### entries
Data submissions from participants.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| case_id | bigint | Foreign key to cases |
| inputs | json | Response data |
| media_id | bigint | Optional media reference |
| questionnaire_id | int | Which questionnaire was answered |
| submitted_at | timestamp | When submitted by participant |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

**Relationships:**
- Belongs to Case
- Belongs to Media (optional)

### mart_questionnaire_schedules
ESM scheduling configuration for repeating questionnaires.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| project_id | bigint | Foreign key to projects |
| questionnaire_id | int | Unique questionnaire identifier |
| type | enum | single/repeating |
| start_date_time | datetime | Schedule start |
| end_date_time | datetime | Schedule end |
| daily_interval_duration | int | Hours between questionnaires |
| min_break_between | int | Minimum minutes between |
| max_daily_submits | int | Maximum per day |
| daily_start_time | time | Daily window start |
| daily_end_time | time | Daily window end |
| quest_available_at | enum | startOfInterval/randomTimeWithinInterval |
| show_notifications | boolean | Enable notifications |
| notification_text | varchar | Custom notification message |
| show_progress_bar | boolean | Show completion progress |
| condition_questionnaire_id | int | Conditional trigger |
| condition_completed | boolean | Trigger condition |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

**Relationships:**
- Belongs to Project

### users
System users including researchers and participants.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| email | varchar | Unique email address |
| password | varchar | Hashed password |
| api_token | varchar | API authentication token |
| token_expires_at | timestamp | Token expiration |
| name | varchar | Display name |
| created_at | timestamp | Registration date |
| updated_at | timestamp | Last update |

**Relationships:**
- Has many Projects (created)
- Has many Cases (assigned)
- Belongs to many Projects (collaborator)
- Has many Roles

### media
Uploaded files and media assets.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Original filename |
| path | varchar | Storage path |
| type | varchar | MIME type |
| size | bigint | File size in bytes |
| uploaded_by | bigint | User who uploaded |
| created_at | timestamp | Upload timestamp |

**Relationships:**
- Belongs to User
- Belongs to many Projects
- Has many Entries

### roles
User role definitions.

| Column | Type | Description |
|--------|------|-------------|
| id | int | Primary key |
| name | varchar | Role name (admin/researcher/user) |
| description | varchar | Role description |

**Relationships:**
- Belongs to many Users

## Supporting Tables

### project_user
Many-to-many pivot for project collaborators.

| Column | Type | Description |
|--------|------|-------------|
| project_id | bigint | Foreign key to projects |
| user_id | bigint | Foreign key to users |
| role | varchar | Role in project |
| created_at | timestamp | When added |

### media_project
Many-to-many pivot for project media.

| Column | Type | Description |
|--------|------|-------------|
| media_id | bigint | Foreign key to media |
| project_id | bigint | Foreign key to projects |
| created_at | timestamp | When linked |

### role_user
Many-to-many pivot for user roles.

| Column | Type | Description |
|--------|------|-------------|
| role_id | int | Foreign key to roles |
| user_id | bigint | Foreign key to users |

### sessions
Laravel session storage (when using database driver).

| Column | Type | Description |
|--------|------|-------------|
| id | varchar | Session ID |
| user_id | bigint | Optional user |
| ip_address | varchar | Client IP |
| user_agent | text | Browser info |
| payload | text | Session data |
| last_activity | int | Unix timestamp |

### jobs
Queue jobs for background processing.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| queue | varchar | Queue name |
| payload | longtext | Job data |
| attempts | int | Retry count |
| created_at | int | Unix timestamp |

### failed_jobs
Failed queue jobs for debugging.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| uuid | varchar | Unique identifier |
| connection | text | Queue connection |
| queue | text | Queue name |
| payload | longtext | Job data |
| exception | longtext | Error details |
| failed_at | timestamp | When failed |

## JSON Field Structures

### Project Inputs (projects.inputs)

Defines custom questions for data collection:

```json
[
  {
    "id": "q1",
    "type": "radio",
    "text": "How are you feeling?",
    "required": true,
    "answers": {
      "0": "Very bad",
      "1": "Bad",
      "2": "Neutral",
      "3": "Good",
      "4": "Very good"
    }
  },
  {
    "id": "q2",
    "type": "text",
    "text": "Describe your activities",
    "maxLength": 500
  },
  {
    "id": "q3",
    "type": "checkbox",
    "text": "Select all that apply",
    "answers": {
      "0": "Work",
      "1": "Leisure",
      "2": "Social"
    }
  }
]
```

### Entry Inputs (entries.inputs)

Stores participant responses and metadata:

```json
{
  "answers": {
    "q1": 3,
    "q2": "Working from home today",
    "q3": [0, 2]
  },
  "metadata": {
    "questionnaireId": 1,
    "submittedAt": "2025-01-15T14:30:00Z",
    "duration": 120000,
    "timezone": "Europe/Berlin",
    "deviceInfo": {
      "platform": "iOS",
      "version": "16.5"
    }
  }
}
```

## Indexes

### Performance Indexes

```sql
-- Projects
CREATE INDEX idx_projects_created_by ON projects(created_by);
CREATE INDEX idx_projects_is_locked ON projects(is_locked);

-- Cases
CREATE INDEX idx_cases_project_id ON cases(project_id);
CREATE INDEX idx_cases_user_id ON cases(user_id);
CREATE INDEX idx_cases_status ON cases(status);
CREATE INDEX idx_cases_token ON cases(token);

-- Entries
CREATE INDEX idx_entries_case_id ON entries(case_id);
CREATE INDEX idx_entries_questionnaire_id ON entries(questionnaire_id);
CREATE INDEX idx_entries_submitted_at ON entries(submitted_at);

-- Schedules
CREATE INDEX idx_schedules_project_id ON mart_questionnaire_schedules(project_id);
CREATE INDEX idx_schedules_questionnaire_id ON mart_questionnaire_schedules(questionnaire_id);
CREATE INDEX idx_schedules_type ON mart_questionnaire_schedules(type);

-- Users
CREATE UNIQUE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_api_token ON users(api_token);
```

## Migrations

Migrations are located in `database/migrations/` and follow Laravel naming conventions:

```
YYYY_MM_DD_HHMMSS_description.php
```

Key migrations:
- `create_projects_table.php` - Projects structure
- `create_cases_table.php` - Cases and participants
- `create_entries_table.php` - Data submissions
- `create_mart_questionnaire_schedules_table.php` - ESM scheduling
- `add_performance_indexes.php` - Performance optimizations

## Database Operations

### Common Queries

```php
// Get active cases for a project
$activeCases = Cases::where('project_id', $projectId)
    ->where('status', 'active')
    ->get();

// Get today's entries for a case
$entries = Entry::where('case_id', $caseId)
    ->whereDate('submitted_at', today())
    ->orderBy('submitted_at', 'desc')
    ->get();

// Get project with schedules
$project = Project::with('schedules')
    ->find($projectId);

// Count entries per questionnaire
$stats = Entry::where('case_id', $caseId)
    ->selectRaw('questionnaire_id, COUNT(*) as count')
    ->groupBy('questionnaire_id')
    ->get();
```

### Eloquent Relationships

```php
// Project.php
public function cases() {
    return $this->hasMany(Cases::class);
}

public function schedules() {
    return $this->hasMany(MartQuestionnaireSchedule::class);
}

// Cases.php
public function project() {
    return $this->belongsTo(Project::class);
}

public function entries() {
    return $this->hasMany(Entry::class, 'case_id');
}

// Entry.php
public function case() {
    return $this->belongsTo(Cases::class, 'case_id');
}
```

## Data Integrity

### Constraints

- Foreign keys enforce referential integrity
- Unique constraints on email, tokens
- Check constraints on status enums
- NOT NULL on required fields

### Soft Deletes

Some tables support soft deletes for audit trails:
- Add `deleted_at` timestamp column
- Use Laravel's `SoftDeletes` trait
- Records are hidden but not removed

### Validation Rules

Database-level validation supplements application validation:
- Email format validation
- Enum value constraints
- JSON structure validation
- Date range constraints

## Backup and Maintenance

### Backup Strategy

```bash
# Daily backup
mysqldump -u root -p metag_analyze > backup_$(date +%Y%m%d).sql

# Compressed backup
mysqldump -u root -p metag_analyze | gzip > backup_$(date +%Y%m%d).sql.gz

# Restore from backup
mysql -u root -p metag_analyze < backup_20250115.sql
```

### Maintenance Tasks

```sql
-- Analyze tables for optimization
ANALYZE TABLE projects, cases, entries;

-- Optimize tables
OPTIMIZE TABLE entries;

-- Check for data integrity
SELECT * FROM cases
WHERE project_id NOT IN (SELECT id FROM projects);

-- Clean old sessions
DELETE FROM sessions
WHERE last_activity < UNIX_TIMESTAMP(NOW() - INTERVAL 30 DAY);
```

## Performance Considerations

### Query Optimization

1. Use eager loading to prevent N+1 queries
2. Add indexes on frequently queried columns
3. Paginate large result sets
4. Cache frequently accessed data
5. Use database views for complex queries

### Scaling Strategies

1. **Read Replicas**: Separate read/write operations
2. **Partitioning**: Partition entries table by date
3. **Archiving**: Move old data to archive tables
4. **Caching**: Redis for frequently accessed data
5. **Connection Pooling**: Optimize connection usage