# MetaG-Analyze Data Structure

## Overview

MetaG-Analyze is a Laravel-based research platform that follows a hierarchical data structure designed for academic and media research studies. The system allows researchers to create projects, manage cases (participants), and collect entries (data points) with flexible custom fields.

## Core Entities

### 1. Projects
**Table:** `projects`  
**Model:** `app/Project.php`

Projects are the top-level organizational unit representing a research study.

#### Key Fields:
- `id`: Primary key
- `name`: Project title (max 200 chars)
- `description`: Brief description (max 250 chars)
- `inputs`: JSON field containing custom questions/fields for data collection
- `created_by`: Reference to the user who created the project
- `is_locked`: Boolean flag to prevent modifications
- `entity_name`: Custom name for entities (replaces "media")
- `use_entity`: Boolean to enable/disable entity usage

#### Relationships:
- **Has many** Cases
- **Has many** Media/Entities (through pivot table)
- **Belongs to many** Users (invited collaborators)

#### Special Features:
- Custom input fields are defined at the project level and inherited by all cases
- Input types include: text, scale, one choice, multiple choice, audio recording
- Projects can be locked to prevent structural changes once data collection begins

### 2. Cases
**Table:** `cases`  
**Model:** `app/Cases.php`

Cases represent individual participants or data collection units within a project.

#### Key Fields:
- `id`: Primary key
- `name`: Case identifier (max 200 chars)
- `duration`: String encoding the data collection period
- `project_id`: Foreign key to projects
- `user_id`: Optional reference to the user assigned to this case
- `case_file_key`: Unique key for file management

#### Relationships:
- **Belongs to** Project
- **Has many** Entries
- **Belongs to** User (optional)
- **Has many** Files
- **Has many** Stats
- **Has many** Notifications

#### Duration Format:
The duration field uses a special encoding:
- `value:X|firstDay:YYYY-MM-DD|lastDay:YYYY-MM-DD|`
- Backend cases have `value:0` (data entry only through admin interface)
- Regular cases have positive values representing hours

### 3. Entries
**Table:** `entries`  
**Model:** `app/Entry.php`

Entries are individual data points collected within a case.

#### Key Fields:
- `id`: Primary key
- `begin`: Start timestamp (stored as string)
- `end`: End timestamp (stored as string)
- `inputs`: JSON field containing responses to project questions
- `case_id`: Foreign key to cases
- `media_id`: Foreign key to media/entities (nullable as of 2025)

#### Relationships:
- **Belongs to** Case
- **Belongs to** Media/Entity (optional)

#### Entity Support:
- The system now supports flexible "entities" instead of just "media"
- `media_id` column is maintained for backward compatibility
- `entity()` relationship provides modern naming convention

### 4. Media/Entities
**Table:** `media`  
**Model:** `app/Media.php`

Represents any type of entity being studied (media content, products, concepts, etc.).

#### Key Fields:
- `id`: Primary key
- `name`: Entity identifier
- Additional fields vary based on entity type

#### Relationships:
- **Belongs to many** Projects
- **Has many** Entries

## Data Flow

1. **Project Creation**
   - Researcher creates a project with custom input fields
   - Defines entity type (if using entities)
   - Sets project parameters

2. **Case Setup**
   - Cases are created within the project
   - Each case can be assigned to a user
   - Duration is set for data collection period

3. **Data Collection**
   - Entries are created within cases
   - Each entry records:
     - Timestamps (begin/end)
     - Responses to project inputs
     - Optional entity reference
   - Mobile app or web interface for data entry

4. **Analysis**
   - Data can be exported
   - Visualizations show patterns across cases
   - Statistical analysis on aggregated data

## Key Design Principles

### Flexibility
- Custom input fields allow adaptation to any research design
- Entity naming can be customized per project
- Support for both time-based and event-based data collection

### Hierarchy
- Clear parent-child relationships maintain data integrity
- Cascading deletes ensure no orphaned data
- Permissions inherit down the hierarchy

### Scalability
- JSON fields for dynamic data without schema changes
- Efficient querying through proper indexing
- Support for large-scale studies with many participants

### Data Integrity
- Foreign key constraints maintain referential integrity
- Soft deletes available for audit trails
- Validation at model level prevents invalid data

## API Support

The system provides two API versions:
- **V1**: Original API with media-centric naming
- **V2**: Modern API with entity support and improved error handling

Both APIs maintain backward compatibility while V2 offers enhanced features for newer implementations.