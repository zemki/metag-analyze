# Metag-Analyze Documentation

Mobile experience sampling platform for MART data collection.

See [Installation Guide](./INSTALLATION.md) for setup instructions.

See [Architecture Overview](./ARCHITECTURE.md) for system design and structure.

See [MART API](./MART/) for mobile integration documentation.

## Commands

### Development
```bash
npm run dev              # Start Vite dev server
npm run build           # Production build
vendor/bin/pest         # Run tests
```

### Testing
```bash
vendor/bin/pest                    # Run all tests
vendor/bin/pest --filter=TestName  # Run specific test
npm run test:unit                  # Frontend tests
npm run lint:check                 # Check linting
npm run format:check              # Check formatting
```

### Database
```bash
php artisan migrate                 # Run migrations
php artisan migrate:status          # Check migration status
php artisan db:seed                # Seed database
```

### Background Services
```bash
php artisan queue:work              # Queue worker
php artisan reverb:start            # WebSocket server
```

### Scheduled Tasks
Configured in `app/Console/Kernel.php`:
- Queue flush - Daily at midnight
- Notification dispatch - As configured

## Documentation

- [Installation Guide](./INSTALLATION.md) - Setup instructions
- [Architecture Overview](./ARCHITECTURE.md) - Tech stack and design
- [MART API](./MART/) - Mobile API documentation

---

**Laravel**: 11.x | **PHP**: 8.3+ | **Vue**: 3.x | **MySQL**: 8.0+