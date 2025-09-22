# System Architecture

## Overview

Metag-Analyze is a mobile experience sampling platform built with Laravel 11 and Vue 3. It enables researchers to conduct ESM (Experience Sampling Method) studies through mobile data collection with the MART app integration.

## Technology Stack

### Backend
- **Framework**: Laravel 11
- **PHP Version**: 8.3+
- **Database**: MySQL 8.0+
- **Cache/Queue**: Redis
- **WebSockets**: Laravel Reverb
- **Push Notifications**: Firebase Cloud Messaging (FCM)

### Frontend
- **Framework**: Vue 3 with Composition API
- **Build Tool**: Vite
- **CSS Framework**: Tailwind CSS
- **State Management**: Vuex 4
- **Charts**: Highcharts, Chart.js

### Mobile Integration
- **API**: RESTful JSON API
- **Authentication**: Bearer Token
- **Protocol**: MART API specification

## Directory Structure

```
metag-analyze/
├── app/                    # Laravel application
│   ├── Console/           # Artisan commands
│   ├── Exceptions/        # Exception handlers
│   ├── Http/
│   │   ├── Controllers/   # Request handlers
│   │   ├── Middleware/    # HTTP middleware
│   │   └── Resources/     # API resource transformers
│   ├── Models/            # Eloquent models (Note: directly in app/)
│   ├── Notifications/     # Notification classes
│   └── Providers/         # Service providers
│
├── config/                # Configuration files
├── database/
│   ├── migrations/        # Database migrations
│   ├── seeds/            # Database seeders
│   └── factories/        # Model factories
│
├── public/               # Public assets
├── resources/
│   ├── js/              # Vue.js application
│   │   ├── components/  # Reusable Vue components
│   │   └── store/      # Vuex store modules
│   ├── sass/           # SCSS stylesheets
│   └── views/          # Blade templates
│
├── routes/              # Application routes
│   ├── web.php         # Web routes
│   ├── api.php         # API v1 routes
│   └── mart_api.php    # MART mobile API routes
│
├── storage/            # Generated files & uploads
└── tests/              # Test suites
    ├── Feature/        # Feature tests
    └── Unit/          # Unit tests
```

## Core Components

### Models (Eloquent ORM)

Located directly in `app/` directory:

- **Project.php** - Research projects
- **Cases.php** - Participants/cases within projects
- **Entry.php** - Data entries from participants
- **User.php** - System users and researchers
- **Media.php** - Uploaded files and media
- **MartQuestionnaireSchedule.php** - ESM scheduling

### Controllers

Key controllers in `app/Http/Controllers/`:

- **ProjectController** - Project CRUD operations
- **ProjectCasesController** - Case management within projects
- **MartApiController** - Mobile API endpoints
- **AdminController** - Administrative functions
- **UserController** - User management

### API Resources

Transform models for API responses (`app/Http/Resources/Mart/`):

- **MartStructureResource** - Complete project structure
- **QuestionSheetResource** - Questionnaire formatting
- **ScaleResource** - Question type definitions
- **ProjectOptionsResource** - Project metadata

## Request Flow

### Web Application Flow
```
Browser → Routes (web.php) → Controller → Model → Database
                     ↓                      ↓
                    View ← ─ ─ ─ ─ ─ Response Data
```

### Mobile API Flow
```
Mobile App → Routes (mart_api.php) → MartApiController
                          ↓
                    Validation
                          ↓
                 Model Operations
                          ↓
                  API Resources
                          ↓
                   JSON Response
```

## Authentication & Authorization

### Web Authentication
- Session-based authentication
- Laravel's built-in auth scaffolding
- Role-based access control (admin, researcher, user)

### API Authentication
- Token-based authentication (Bearer tokens)
- Tokens stored in `api_token` field
- Middleware: `auth:api`

### Roles & Permissions
- **Admin**: Full system access
- **Researcher**: Project creation and management
- **User**: Case participation and data entry

## Database Design

### Core Tables

1. **projects** - Research studies
   - Custom input fields (JSON)
   - Study configuration
   - Duration settings

2. **cases** - Study participants
   - Links to projects
   - Assignment to users
   - Status tracking

3. **entries** - Collected data
   - Timestamps
   - Response data (JSON)
   - Media references

4. **mart_questionnaire_schedules** - ESM scheduling
   - Repeating/single questionnaires
   - Time windows
   - Notification settings

### Relationships
```
Project → has many → Cases → has many → Entries
   ↓                    ↓
has many            belongs to
   ↓                    ↓
Schedules             User
```

## Frontend Architecture

### Vue 3 Application Structure

```
resources/js/
├── app.js              # Main entry point
├── components/         # Reusable components
│   ├── forms/         # Form components
│   ├── charts/        # Data visualizations
│   └── ui/           # UI elements
├── store/            # Vuex modules
└── bootstrap.js     # Initial setup
```

### Component Communication
- Props for parent-to-child data
- Events for child-to-parent communication
- Vuex for global state management

### Key Components
- **ProjectOverview** - Project dashboard
- **CaseManager** - Case listing and management
- **DataEntry** - Entry forms and submission
- **ChartComponents** - Data visualization

## Background Processing

### Queue System
- Default: `sync` (immediate processing)
- Production: Redis queue with workers
- Jobs processed by `php artisan queue:work`

### Scheduled Tasks
- Notification sending
- Data cleanup
- Report generation
- Configured in `app/Console/Kernel.php`

## File Storage

### Storage Structure
```
storage/
├── app/           # Application files
│   ├── public/   # Publicly accessible files
│   └── uploads/  # User uploads
├── logs/         # Application logs
└── framework/    # Cache, sessions, views
```

### Media Handling
- Files uploaded to `storage/app/uploads/`
- Public access via symbolic link
- Database references in `media` table

## API Versioning

### Version Strategy
- v1: Legacy endpoints (`/api/`)
- v2: Current mobile API (`/api/v2/`)
- MART: Specialized mobile API (`/mart-api/`)

### Version Control
- `API_V2_CUTOFF_DATE` environment variable
- `FORCE_API_V2` flag for testing
- Backward compatibility maintained

## Security Measures

### Protection Mechanisms
- CSRF protection for web routes
- Rate limiting on API endpoints
- ALTCHA spam protection
- IP blocking capability
- SQL injection prevention via Eloquent
- XSS protection in Blade templates

### Environment Security
- Sensitive data in `.env` file
- Never commit `.env` or credentials
- Use different keys per environment

## Testing Strategy

### Test Structure
```
tests/
├── Feature/           # Integration tests
│   ├── MartApiTest.php
│   ├── ProjectControllerTest.php
│   └── Auth/
└── Unit/             # Unit tests
    └── UserTest.php
```

### Running Tests
```bash
vendor/bin/pest         # Run all tests
vendor/bin/pest --filter TestName  # Specific test
```

## Deployment Considerations

### Server Requirements
- PHP 8.3+ with extensions
- MySQL 8.0+
- Redis for queues/cache
- Supervisor for queue workers
- SSL certificate for HTTPS

### Optimization
```bash
php artisan config:cache   # Cache configuration
php artisan route:cache    # Cache routes
php artisan view:cache     # Cache views
npm run build             # Production assets
```

## Development Workflow

### Local Development
1. Use Laravel Herd or Valet for serving
2. Run `npm run dev` for asset watching
3. Use `.env.testing` for test database
4. Enable debug mode in `.env`

### Code Style
- PSR-12 for PHP code
- Vue style guide for components
- Run `npm run lint:check` for validation

## Monitoring & Debugging

### Logging
- Daily rotating logs in `storage/logs/`
- Configurable log channels
- Laravel Telescope for debugging (dev only)

### Performance
- Database query optimization with indexes
- Eager loading to prevent N+1 queries
- Cache frequently accessed data
- CDN for static assets (production)

## Future Considerations

### Scalability
- Horizontal scaling with load balancer
- Database replication for read/write splitting
- Queue distribution across workers
- Microservices for heavy processing

### Planned Improvements
- GraphQL API implementation
- Real-time collaboration features
- Advanced analytics dashboard
- Mobile app direct integration