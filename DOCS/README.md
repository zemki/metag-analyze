# Metag Analyze Documentation

Research platform for mobile experience sampling and media diaries.

- [Installation Guide](./INSTALLATION.md) - Setup instructions
- [Architecture Overview](./ARCHITECTURE.md) - Tech stack and design
- [Admin Guide](./ADMIN_GUIDE.md) - Admin panel usage
- [MART Documentation](./MART/) - Mobile app API

## Commands

### Development
```bash
vendor/bin/pest              # Run tests
npm run build                # Production build (user runs npm run dev)
./vendor/bin/pint            # Format PHP code
```

### Database
```bash
php artisan migrate                    # Run migrations
php artisan migrate:status             # Check status
php artisan migrate:fresh-all --seed   # Reset both databases (local only)
```

### Artisan
```bash
php artisan queue:work       # Process queue jobs
php artisan reverb:start     # WebSocket server
```

## Testing

```bash
vendor/bin/pest                        # All tests
vendor/bin/pest --filter=TestName      # Specific test
vendor/bin/pest tests/Feature/         # Feature tests only
```

---

**Laravel**: 11.x | **PHP**: 8.3+ | **Vue**: 3 | **Tailwind**: 4
