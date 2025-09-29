# MART API Testing Guide

## Setup Test Environment

### 1. Create API Token

```php
php artisan tinker

// Create or update mobile API user
$token = "mart_test_token_2025";
$hashedToken = hash('sha256', $token);

$user = App\User::where('email', 'mobile@metag.test')->first();
if (!$user) {
    $user = App\User::create([
        'email' => 'mobile@metag.test',
        'password' => Hash::make(Str::random(32)),
        'api_token' => $hashedToken,
        'token_expires_at' => now()->addYears(1)
    ]);
}
```

### 2. Create Test Project

```php
// Using Laravel Tinker
$project = App\Project::create([
    'name' => 'MART Test Project',
    'description' => 'Testing MART API',
    'created_by' => 1,
    'inputs' => [
        [
            'type' => 'mart',
            'schedules' => [
                'repeating' => [
                    [
                        'questionnaireId' => 1,
                        'dailyIntervalDuration' => 4,
                        'maxDailySubmits' => 6
                    ]
                ]
            ]
        ]
    ]
]);
```

### 3. Create Test Case

```php
$case = App\Cases::create([
    'name' => 'Test Participant',
    'project_id' => $project->id,
    'status' => 'active',
    'duration' => '14 days'
]);
```

## Testing Commands

### Basic Test Flow

```bash
# 1. Get structure
PROJECT_ID=1
curl -X GET "https://metag-analyze.test/mart-api/projects/$PROJECT_ID/structure" \
  -H "Authorization: Bearer mart_test_token_2025" \
  | json_pp

# 2. Submit entry
CASE_ID=5
curl -X POST "https://metag-analyze.test/mart-api/cases/$CASE_ID/submit" \
  -H "Authorization: Bearer mart_test_token_2025" \
  -H "Content-Type: application/json" \
  -d '{
    "projectId": '$PROJECT_ID',
    "questionnaireId": 1,
    "userId": "test@example.com",
    "participantId": "test-001",
    "sheetId": 1,
    "questionnaireStarted": '$(date +%s000)',
    "questionnaireDuration": 120000,
    "answers": {"1": 3},
    "timezone": "Europe/Berlin",
    "timestamp": '$(date +%s000)'
  }'
```

### Test Different Question Types

```bash
# Radio/Scale Question
curl -X POST "https://metag-analyze.test/mart-api/cases/5/submit" \
  -H "Authorization: Bearer mart_test_token_2025" \
  -H "Content-Type: application/json" \
  -d '{
    "projectId": 1,
    "questionnaireId": 1,
    "userId": "test@example.com",
    "answers": {"1": 3}
  }'

# Multiple Choice
curl -X POST "https://metag-analyze.test/mart-api/cases/5/submit" \
  -H "Authorization: Bearer mart_test_token_2025" \
  -H "Content-Type: application/json" \
  -d '{
    "projectId": 1,
    "questionnaireId": 1,
    "userId": "test@example.com",
    "answers": {"2": [0, 2, 5]}
  }'

# Text Input
curl -X POST "https://metag-analyze.test/mart-api/cases/5/submit" \
  -H "Authorization: Bearer mart_test_token_2025" \
  -H "Content-Type: application/json" \
  -d '{
    "projectId": 1,
    "questionnaireId": 1,
    "userId": "test@example.com",
    "answers": {"3": "This is my text response"}
  }'
```

## Automated Testing

### PHPUnit Test

```php
// tests/Feature/MartApiTest.php
public function test_mart_api_structure()
{
    $response = $this->withHeaders([
        'Authorization' => 'Bearer mart_test_token_2025',
    ])->getJson('/mart-api/projects/1/structure');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'projectOptions',
                'questionnaires',
                'scales'
            ]
        ]);
}

public function test_mart_api_submit()
{
    $response = $this->withHeaders([
        'Authorization' => 'Bearer mart_test_token_2025',
    ])->postJson('/mart-api/cases/5/submit', [
        'projectId' => 1,
        'questionnaireId' => 1,
        'userId' => 'test@example.com',
        'answers' => ['1' => 3]
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true]);
}
```

### Run Tests

```bash
# Run all MART tests
vendor/bin/pest --filter=Mart

# Run specific test
vendor/bin/pest tests/Feature/MartApiTest.php
```

## Debugging

### Check Laravel Logs

```bash
# View recent logs
tail -n 100 storage/logs/laravel.log

# Follow logs in real-time
tail -f storage/logs/laravel.log
```

### Database Queries

```sql
-- Check submitted entries
SELECT * FROM entries WHERE case_id = 5 ORDER BY created_at DESC;

-- Check case status
SELECT * FROM cases WHERE id = 5;

-- Check project configuration
SELECT * FROM projects WHERE id = 1;
```

### Common Issues

**Token Not Working**
- Verify token hash: `hash('sha256', 'your_token')`
- Check token expiration date
- Ensure user has correct permissions

**Case Not Accepting Submissions**
- Check case status is 'active'
- Verify case hasn't exceeded duration
- Ensure project is active

**Invalid Response Format**
- Use correct date format: DD.MM.YYYY
- Use `questionnaireId` not `sheetId`
- Include all required fields

## Tools

### Postman Collection

Import this collection for easy testing:

```json
{
  "info": {
    "name": "MART API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "auth": {
    "type": "bearer",
    "bearer": [{
      "key": "token",
      "value": "mart_test_token_2025"
    }]
  },
  "item": [
    {
      "name": "Get Structure",
      "request": {
        "method": "GET",
        "url": "{{baseUrl}}/mart-api/projects/1/structure"
      }
    },
    {
      "name": "Submit Entry",
      "request": {
        "method": "POST",
        "url": "{{baseUrl}}/mart-api/cases/5/submit",
        "body": {
          "mode": "raw",
          "raw": "{\n  \"projectId\": 1,\n  \"questionnaireId\": 1\n}"
        }
      }
    }
  ],
  "variable": [{
    "key": "baseUrl",
    "value": "https://metag-analyze.test"
  }]
}
```

### VS Code REST Client

Create `.http` file:

```http
### Get Structure
GET https://metag-analyze.test/mart-api/projects/1/structure
Authorization: Bearer mart_test_token_2025

### Submit Entry
POST https://metag-analyze.test/mart-api/cases/5/submit
Authorization: Bearer mart_test_token_2025
Content-Type: application/json

{
  "projectId": 1,
  "questionnaireId": 1,
  "userId": "test@example.com",
  "answers": {"1": 3}
}
```