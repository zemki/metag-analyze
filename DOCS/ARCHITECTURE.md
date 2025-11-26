# Architecture

## Technology Stack

### Backend
- **Laravel 11.x** - PHP framework
- **PHP 8.3+** - Server-side language
- **MySQL 8.0** - Primary database (dual-database architecture)

### Frontend
- **Vue.js 3** - JavaScript framework (Composition API)
- **Inertia.js** - SPA without API
- **Tailwind CSS 4** - Utility-first styling
- **Vite** - Build tool

### Infrastructure
- **Laravel Herd/Valet** - Local development
- **Redis** - Caching and queues
- **Laravel Reverb** - WebSocket server

## Dual-Database Architecture

The system uses two separate MySQL databases:

| Database | Purpose | Models |
|----------|---------|--------|
| Main (`mysql`) | Projects, Users, Cases, Entries metadata | Project, User, Cases, Entry, Media |
| MART (`mart`) | MART-specific data | MartProject, MartSchedule, MartQuestion, MartEntry, MartAnswer, MartPage |

### Cross-Database Linking
- `projects.id` → `mart_projects.main_project_id`
- `entries.mart_entry_id` → `mart_entries.id`
- `cases.id` → `mart_case_schedules.case_id`

## Core Components

### Project System
- Standard projects: Media diary data collection
- MART projects: Mobile experience sampling with questionnaires

### MART System
- Multiple questionnaire schedules (single/repeating)
- Question versioning with UUID tracking
- Dynamic date calculation per participant
- Device info and stats collection

### Authentication
- Web: Laravel session-based
- MART API: Bearer token + refresh token flow

## Key Files

| Component | Location |
|-----------|----------|
| MART API | `app/Http/Controllers/MartApiController.php` |
| Project Management | `app/Http/Controllers/ProjectController.php` |
| MART Models | `app/Mart/` |
| API Routes | `routes/mart_api.php` |
| Frontend Pages | `resources/js/Pages/` |
