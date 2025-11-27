# MART Data Mapping

How MART API types map to database storage.

## ProjectOptions

| API Field | Storage Location | Notes |
|-----------|------------------|-------|
| `projectId` | `projects.id` | Main DB |
| `projectName` | `projects.name` | Main DB |
| `startDateAndTime` | `projects.inputs` JSON | In `martConfig.projectOptions` |
| `endDateAndTime` | `projects.inputs` JSON | In `martConfig.projectOptions` |
| `collectAndroidStats` | `projects.inputs` JSON | In `martConfig.projectOptions` |
| `collectDeviceInfos` | `projects.inputs` JSON | In `martConfig.projectOptions` |
| `initialHoursOfAndroidStats` | `projects.inputs` JSON | In `martConfig.projectOptions` |
| `overlapAndroidStatsHours` | `projects.inputs` JSON | In `martConfig.projectOptions` |
| `pages` | `mart_pages.id` array | MART DB, derived |
| `pagesToShowInMenu` | `mart_pages` where `show_in_menu=true` | MART DB |
| `pagesToShowOnFirstAppStart` | `mart_pages` where `show_on_first_app_start=true` | MART DB |
| `successPage` | `mart_pages` where `page_type='success'` | MART DB |
| `androidStatsPermissionPage` | `mart_pages` where `page_type='android_stats_permission'` | MART DB |
| `iOSNotificationPermissionPage` | `mart_pages` where `page_type='ios_notification_permission'` | MART DB |
| `androidNotificationPermissionPage` | `mart_pages` where `page_type='android_notification_permission'` | MART DB |
| `iOSDataDonationQuestionnaire` | `mart_schedules` where `is_ios_data_donation=true` | MART DB |
| `androidDataDonationQuestionnaire` | `mart_schedules` where `is_android_data_donation=true` | MART DB |

## Questionnaire (Schedule)

| API Field | Database Column | Table |
|-----------|-----------------|-------|
| `questionnaireId` | `mart_schedules.questionnaire_id` | MART DB |
| `type` | `mart_schedules.type` | `single` or `repeating` |
| `startDateAndTime` | `mart_schedules.timing_config->start_date_time` | JSON |
| `endDateAndTime` | `mart_schedules.timing_config->end_date_time` | JSON |
| `showProgressBar` | `mart_schedules.notification_config->show_progress_bar` | JSON |
| `showNotifications` | `mart_schedules.notification_config->show_notifications` | JSON |
| `notificationText` | `mart_schedules.notification_config->notification_text` | JSON |
| `minBreakBetweenQuestionnaire` | `mart_schedules.timing_config->min_break` | JSON, minutes |
| `dailyIntervalDuration` | `mart_schedules.timing_config->interval_duration` | JSON, hours |
| `maxDailySubmits` | `mart_schedules.timing_config->max_daily_submits` | JSON |
| `dailyStartTime` | `mart_schedules.timing_config->daily_start_time` | JSON |
| `dailyEndTime` | `mart_schedules.timing_config->daily_end_time` | JSON |
| `questAvailableAt` | `mart_schedules.timing_config->availability_mode` | JSON |

### Dynamic Start Date (per-participant)

| Config Field | Database Column | Notes |
|--------------|-----------------|-------|
| `start_on_first_login` | `mart_schedules.timing_config->start_on_first_login` | Boolean, single questionnaires only |
| `start_hours_after_login` | `mart_schedules.timing_config->start_hours_after_login` | Integer (0-168), delay after login |

**Note:** These are backend-only fields. Mobile receives concrete calculated dates, not the configuration.

### Dynamic Dates (per-participant)

| API Field | Database Column | Table |
|-----------|-----------------|-------|
| Per-case start date | `mart_case_schedules.timing_overrides->start_date_time` | MART DB |
| Per-case end date | `mart_case_schedules.timing_overrides->end_date_time` | MART DB |

## QuestionnaireItem (Question)

| API Field | Database Column | Table |
|-----------|-----------------|-------|
| `itemId` | `mart_questions.uuid` | MART DB, UUID |
| `text` | `mart_questions.text` | |
| `scaleId` | Derived from `type` + `config` | See Scale mapping |
| `imageUrl` | `mart_questions.config->image_url` | JSON |
| `videoUrl` | `mart_questions.config->video_url` | JSON |
| `options.randomizationGroupId` | `mart_questions.config->randomization_group_id` | JSON |
| `options.randomizeAnswers` | `mart_questions.config->randomize_answers` | JSON |
| `options.noValueAllowed` | `!mart_questions.is_mandatory` | Inverse |
| `options.itemGroup` | `mart_questions.config->item_group` | JSON |

## Scale (Question Type Config)

| API Type | Database `type` | Config Fields |
|----------|-----------------|---------------|
| `number` | `number` | `minValue`, `maxValue`, `maxDigits` |
| `text` | `text` | `textOptions.type` |
| `textarea` | `textarea` | - |
| `radio` | `one choice` | `options[]` in config |
| `checkbox` | `multiple choice` | `options[]` in config |
| `radioWithText` | `one choice` | `options[]` + `includeOtherOption: true` |
| `checkboxWithText` | `multiple choice` | `options[]` + `includeOtherOption: true` |
| `range` | `range` | `minValue`, `maxValue`, `steps`, `defaultValue` |
| `rangeValues` | `range values` | `rangeValueOptions[]` |
| `photoUpload` | `photo` | `photoUploadOptions` |
| `audioUpload` | `audio` | `audioUploadOptions` |
| `videoUpload` | `video` | `videoUploadOptions` |

## Page

| API Field | Database Column | Table |
|-----------|-----------------|-------|
| `id` | `mart_pages.id` | MART DB |
| `pageId` | `mart_pages.id` | Same as `id` |
| `name` | `mart_pages.name` | |
| `content` | `mart_pages.content` | HTML |
| `options.buttonText` | `mart_pages.button_text` | |

## Submit (Entry)

| API Field | Database Column | Table |
|-----------|-----------------|-------|
| `questionnaireId` | `mart_entries.questionnaire_id` | MART DB |
| `projectId` | Via `mart_entries.schedule_id` → `mart_schedules.mart_project_id` | |
| `participantId` | `mart_entries.participant_id` | From `cases.name` |
| `userId` | `mart_entries.user_id` | Email |
| `questionnaireStarted` | `mart_entries.started_at` | Timestamp |
| `questionnaireDuration` | `mart_entries.duration_ms` | Milliseconds |
| `answers` | `mart_answers.answer_value` | Per question UUID |
| `timestamp` | `mart_entries.timestamp` | Unix timestamp |
| `timezone` | `mart_entries.timezone` | |

## DeviceInfo

| API Field | Database Column | Table |
|-----------|-----------------|-------|
| `participantId` | `mart_device_info.participant_id` | MART DB |
| `userId` | `mart_device_info.user_id` | Email |
| `os` | `mart_device_info.os` | `android`/`ios` |
| `osVersion` | `mart_device_info.os_version` | |
| `model` | `mart_device_info.model` | |
| `manufacturer` | `mart_device_info.manufacturer` | |

## Stats

| API Field | Database Column | Table |
|-----------|-----------------|-------|
| `participantId` | `mart_stats.participant_id` | MART DB |
| `userId` | `mart_stats.user_id` | Email |
| `androidUsageStats` | `mart_stats.android_usage_stats` | JSON |
| `androidEventStats` | `mart_stats.android_event_stats` | JSON |
| `timestamp` | `mart_stats.timestamp` | Unix timestamp |
| `timezone` | `mart_stats.timezone` | |
