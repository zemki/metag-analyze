# MART API Documentation

Mobile API for Experience Sampling Method (ESM) studies with the Metag mobile app.

## Quick Reference

- **Base URL**: `https://metag-analyze.test/mart-api`
- **Authentication**: Bearer Token
- **Format**: JSON

## Documentation

- **[API Reference](./API.md)** - Complete endpoint documentation
- **[Testing Guide](./TESTING.md)** - Setup and testing instructions

## Quick Start

```bash
# Get project structure
curl -X GET "https://metag-analyze.test/mart-api/projects/1/structure" \
  -H "Authorization: Bearer mart_test_token_2025"

# Submit entry
curl -X POST "https://metag-analyze.test/mart-api/cases/5/submit" \
  -H "Authorization: Bearer mart_test_token_2025" \
  -H "Content-Type: application/json" \
  -d '{"projectId": 1, "questionnaireId": 1, ...}'
```

## Key Features

- Real-time questionnaire delivery
- Flexible scheduling (repeating, single, event-based)
- Multiple question types (radio, checkbox, text, number)
- Offline support with sync
- Device statistics tracking

## Important Format Requirements

- **Dates**: `DD.MM.YYYY` (e.g., "31.03.2025")
- **Times**: 24-hour `HH:MM` (e.g., "14:30")
- **Response Keys**: `questionnaires` NOT `questionSheets`
- **ID Fields**: `questionnaireId` NOT `sheetId`