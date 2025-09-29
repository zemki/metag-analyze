# Architecture

## Technology Stack

### Backend
- **Laravel 11** - PHP web framework
- **PHP 8.3+** - Server-side language
- **MySQL 8.0+** - Primary database
- **Redis** - Cache and queue backend
- **Laravel Reverb** - WebSocket server
- **Firebase FCM** - Push notifications

### Frontend
- **Vue 3** - JavaScript framework with Composition API
- **Vite** - Build tool and dev server
- **Tailwind CSS** - Utility-first styling
- **Vuex 4** - State management
- **Highcharts/Chart.js** - Data visualization

### Mobile Integration
- **MART API** - RESTful JSON API
- **Bearer Token** - Authentication
- **Firebase** - Push notifications

## System Design

### Core Components

**Projects**
- Research studies with custom questions
- ESM scheduling configuration
- Duration and participant management

**Cases**
- Study participants or data units
- Token-based mobile authentication
- Status tracking (pending/active/completed)

**Entries**
- Participant responses with timestamps
- JSON data storage for flexibility
- Media attachment support

**Schedules**
- Repeating/single questionnaires
- Time window configuration
- Notification settings

### Data Flow

```
Mobile App → MART API → Validation → Database → Response
     ↑                                    ↓
     └──────────── Push Notifications ←──┘
```

## Database Schema

### Core Entities

- **Projects**: Research studies with custom questions and ESM scheduling
- **Cases**: Study participants with token-based authentication
- **Entries**: Participant responses with timestamps and metadata
- **Schedules**: Questionnaire timing and notification configuration

### Relationships

```
Project → has many → Cases → has many → Entries
   ↓                    ↓
has many            belongs to
   ↓                    ↓
Schedules             User
```

### JSON Structures

**Project Questions** (projects.inputs):
```json
[{
  "id": "q1",
  "type": "radio",
  "text": "How are you feeling?",
  "required": true,
  "options": [
    {"value": 0, "text": "Very bad"},
    {"value": 1, "text": "Bad"},
    {"value": 2, "text": "Neutral"},
    {"value": 3, "text": "Good"},
    {"value": 4, "text": "Very good"}
  ]
}]
```

**Entry Data** (entries.inputs):
```json
{
  "answers": {
    "q1": 3,
    "q2": "Working from home"
  },
  "metadata": {
    "questionnaireId": 1,
    "submittedAt": "2025-01-15T14:30:00Z",
    "duration": 120000,
    "timezone": "Europe/Berlin"
  }
}
```

### Key Indexes

- `cases.project_id` - Project queries
- `cases.token` - Mobile authentication
- `entries.case_id` - Participant data
- `entries.submitted_at` - Time-based queries
- `users.api_token` - API authentication

## API Architecture

### Endpoints

**Web Application**
- Session-based authentication
- Server-side rendering with Blade
- Vue components for interactivity

**MART Mobile API**
- Token authentication
- JSON request/response
- Versioned endpoints (`/mart-api/`)

### Authentication

**Web Users**
- Email/password login
- Session cookies
- CSRF protection

**Mobile Clients**
- Bearer token in headers
- Token per case/participant
- Expiration handling

## Background Processing

### Queue System
- Redis-backed queues
- Supervisor process management
- Job retry mechanisms

### Scheduled Tasks
- Notification dispatch
- Data cleanup
- Configured in `app/Console/Kernel.php`

*Note: Report generation is handled on-demand via the web interface, not as a scheduled task.*

## Security

### Protection Layers
- CSRF tokens for web forms
- Rate limiting on APIs
- ALTCHA spam protection
- IP blocking capability
- SQL injection prevention (Eloquent)
- XSS protection (Blade escaping)

### Environment Security
- Sensitive data in `.env`
- Firebase credentials separate
- Different keys per environment

## Performance Optimization

### Database
- Eager loading relationships
- Query result caching
- Indexed foreign keys
- Paginated results

### Application
- Config/route/view caching
- OpCache for PHP
- CDN for static assets
- Redis for session/cache

### Scaling Strategies
- Horizontal scaling ready
- Queue distribution
- Read/write splitting capability
- Microservice boundaries defined

## Development Workflow

### Local Setup
- Laravel Herd/Valet for serving
- Hot reload with Vite
- Separate test database
- Debug mode enabled

### Testing
```bash
vendor/bin/pest           # Run all tests
vendor/bin/pest --filter  # Specific test
npm run test:unit        # Frontend tests
```

### Code Quality
```bash
npm run build           # Production build
npm run lint:check      # Code linting
npm run format:check    # Format validation
```

## Monitoring

### Logging
- Daily rotating logs
- Configurable channels
- Error tracking ready

### Performance Metrics
- Query performance logging
- API response times
- Queue processing stats

## Key Design Decisions

### Models in app/
Legacy structure maintained for compatibility - models reside directly in `app/` rather than `app/Models/`.

### JSON for Flexibility
Project inputs and entry data use JSON columns for schema flexibility without migrations.

### Token per Case
Each case gets its own token rather than user-based auth, enabling participant-specific access.

### Vue 3 + Inertia
Modern reactive UI while maintaining Laravel's routing and controller structure.