# Metag-Analyze Documentation

Welcome to the Metag-Analyze documentation. This guide will help you understand, install, and contribute to the project.

## 📚 Documentation Structure

### Getting Started
- **[Installation Guide](./INSTALLATION.md)** - Complete setup instructions including dependencies, Firebase, and ALTCHA configuration
- **[Architecture Overview](./ARCHITECTURE.md)** - System design, technology stack, and component structure
- **[Troubleshooting](./TROUBLESHOOTING.md)** - Common issues and solutions
- **[Deployment Guide](./DEPLOYMENT.md)** - Production deployment instructions

### API Documentation
- **[MART API](./API/MART_API.md)** - Mobile API endpoints, authentication, testing, and integration guide

### Reference Documentation
- **[Database Schema](./REFERENCE/DATABASE.md)** - Complete database structure, relationships, and queries
- **[Configuration](./REFERENCE/CONFIGURATION.md)** - All environment variables explained with examples
- **[Entity Field Changes](./REFERENCE/ENTITY_FIELD_CHANGES.md)** - Historical API migration guide

## 🚀 Quick Start

1. **Clone and Install**
   ```bash
   git clone https://github.com/your-org/metag-analyze.git
   cd metag-analyze
   composer install
   npm install
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   # Edit .env with your settings
   ```

3. **Setup Database**
   ```bash
   mysql -u root -p -e "CREATE DATABASE metag_analyze"
   php artisan migrate
   ```

4. **Build and Run**
   ```bash
   npm run build
   php artisan serve
   ```

For detailed instructions, see the [Installation Guide](./INSTALLATION.md).

## 🏗️ Project Overview

Metag-Analyze is a mobile experience sampling platform that enables researchers to:
- Create and manage ESM (Experience Sampling Method) studies
- Configure questionnaire schedules (single or repeating)
- Collect data from mobile participants via the MART API
- Analyze and export collected data

### Technology Stack
- **Backend**: Laravel 11, PHP 8.3+
- **Frontend**: Vue 3, Vite, Tailwind CSS
- **Database**: MySQL 8.0+
- **Mobile API**: RESTful JSON (MART specification)
- **Push Notifications**: Firebase Cloud Messaging

### Historical Context
This project was initially developed in 2018 using Laravel 8 and has been progressively upgraded over the years. As a result, you may notice:
- Some architectural patterns that differ from a fresh Laravel 11 installation
- Models located directly in `app/` rather than `app/Models/`
- Mix of older and newer coding patterns
- Legacy database structure that has evolved over time
- Custom implementations that predate some Laravel features

The codebase is stable and production-tested but reflects its evolution through multiple Laravel versions.

## 📖 Documentation Guide

### For Developers

#### First Time Setup
1. Read [Installation Guide](./INSTALLATION.md) for environment setup
2. Review [Architecture Overview](./ARCHITECTURE.md) to understand the system
3. Check [Configuration Reference](./REFERENCE/CONFIGURATION.md) for environment variables

#### Working with the API
- [MART API Documentation](./API/MART_API.md) - Complete API reference with examples
- Test endpoints using the provided curl commands
- Use bearer token authentication

#### Database Operations
- [Database Schema](./REFERENCE/DATABASE.md) - Table structures and relationships
- Migrations in `database/migrations/`
- Seeders for test data

### For Researchers

#### Creating Studies
1. Set up a project with custom questions
2. Configure questionnaire schedules
3. Create cases for participants
4. Share mobile app access tokens

#### Data Collection
- Participants use mobile app with MART API
- Real-time data submission
- Automatic scheduling and notifications

## 🧪 Testing

Run the test suite:
```bash
# All tests
vendor/bin/pest

# Specific test
vendor/bin/pest --filter=MartApiTest

# Frontend tests
npm run test:unit
```

## 🔧 Development Commands

### Backend
```bash
php artisan migrate        # Run migrations
php artisan queue:work     # Start queue worker
php artisan reverb:start   # Start WebSocket server
php artisan tinker         # Interactive console
```

### Frontend
```bash
npm run dev               # Development build with hot reload
npm run build             # Production build
npm run lint:check        # Check code style
npm run format:check      # Check formatting
```

## 📁 Project Structure

```
metag-analyze/
├── app/                  # Laravel application
├── database/            # Migrations and seeders
├── resources/           # Frontend assets
│   └── js/             # Vue.js components
├── routes/             # API and web routes
├── storage/            # Uploads and logs
├── tests/              # Test suites
└── DOCS/               # This documentation
```

## 🚨 Important Notes

### Security
- Never commit `.env` files or credentials
- Firebase service account JSON must be kept secure
- Generate unique ALTCHA HMAC keys for each environment
- Use HTTPS in production

### Environment-Specific Settings
- Development: Debug enabled, sync queue
- Staging: Redis cache, database sessions
- Production: Debug disabled, Redis queue, OpCache enabled

### API Versioning
- Current mobile API: `/mart-api/`
- Legacy API: `/api/` (v1)
- New features: `/api/v2/`

## 🤝 Contributing

1. Read the documentation thoroughly
2. Follow Laravel and Vue.js best practices
3. Write tests for new features
4. Update documentation for API changes
5. Use conventional commit messages

## 📞 Support

For issues or questions:
1. Check the relevant documentation section
2. Review error logs in `storage/logs/`
3. Test with provided examples
4. Create an issue on GitHub

## 📝 Documentation Maintenance

When updating the system:
- Update API documentation for endpoint changes
- Document new environment variables
- Update database schema for migrations
- Add examples for new features
- Keep installation guide current

---

**Last Updated**: January 2025
**Laravel Version**: 11.x
**PHP Version**: 8.3+
**API Version**: MART v2