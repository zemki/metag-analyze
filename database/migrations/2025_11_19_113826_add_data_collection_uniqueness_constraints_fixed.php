<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds database constraints to ensure:
     * - Only one iOS data collection question per project
     * - Only one Android data collection question per project
     * - Only one success page per project
     *
     * Uses triggers for MySQL since partial unique indexes are not supported.
     * Fixed version that uses mart_questionnaire_id instead of schedule_id.
     */
    public function up(): void
    {
        $connection = Schema::connection('mart')->getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            // Drop existing triggers first if they exist
            DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_insert_ios_data_collection_question');
            DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_update_ios_data_collection_question');
            DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_insert_android_data_collection_question');
            DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_update_android_data_collection_question');
            DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_insert_success_page');
            DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_update_success_page');

            // Trigger to enforce only one iOS data collection question per project
            DB::connection('mart')->unprepared('
                CREATE TRIGGER before_insert_ios_data_collection_question
                BEFORE INSERT ON mart_questions
                FOR EACH ROW
                BEGIN
                    IF NEW.is_ios_data_collection = TRUE THEN
                        UPDATE mart_questions mq
                        INNER JOIN mart_schedules ms ON mq.mart_schedule_id = ms.id
                        SET mq.is_ios_data_collection = FALSE
                        WHERE ms.mart_project_id = (
                            SELECT mart_project_id FROM mart_schedules WHERE id = NEW.mart_schedule_id
                        )
                        AND mq.is_ios_data_collection = TRUE;
                    END IF;
                END
            ');

            DB::connection('mart')->unprepared('
                CREATE TRIGGER before_update_ios_data_collection_question
                BEFORE UPDATE ON mart_questions
                FOR EACH ROW
                BEGIN
                    IF NEW.is_ios_data_collection = TRUE AND (OLD.is_ios_data_collection = FALSE OR OLD.is_ios_data_collection IS NULL) THEN
                        UPDATE mart_questions mq
                        INNER JOIN mart_schedules ms ON mq.mart_schedule_id = ms.id
                        SET mq.is_ios_data_collection = FALSE
                        WHERE ms.mart_project_id = (
                            SELECT mart_project_id FROM mart_schedules WHERE id = NEW.mart_schedule_id
                        )
                        AND mq.uuid != NEW.uuid
                        AND mq.is_ios_data_collection = TRUE;
                    END IF;
                END
            ');

            // Trigger to enforce only one Android data collection question per project
            DB::connection('mart')->unprepared('
                CREATE TRIGGER before_insert_android_data_collection_question
                BEFORE INSERT ON mart_questions
                FOR EACH ROW
                BEGIN
                    IF NEW.is_android_data_collection = TRUE THEN
                        UPDATE mart_questions mq
                        INNER JOIN mart_schedules ms ON mq.mart_schedule_id = ms.id
                        SET mq.is_android_data_collection = FALSE
                        WHERE ms.mart_project_id = (
                            SELECT mart_project_id FROM mart_schedules WHERE id = NEW.mart_schedule_id
                        )
                        AND mq.is_android_data_collection = TRUE;
                    END IF;
                END
            ');

            DB::connection('mart')->unprepared('
                CREATE TRIGGER before_update_android_data_collection_question
                BEFORE UPDATE ON mart_questions
                FOR EACH ROW
                BEGIN
                    IF NEW.is_android_data_collection = TRUE AND (OLD.is_android_data_collection = FALSE OR OLD.is_android_data_collection IS NULL) THEN
                        UPDATE mart_questions mq
                        INNER JOIN mart_schedules ms ON mq.mart_schedule_id = ms.id
                        SET mq.is_android_data_collection = FALSE
                        WHERE ms.mart_project_id = (
                            SELECT mart_project_id FROM mart_schedules WHERE id = NEW.mart_schedule_id
                        )
                        AND mq.uuid != NEW.uuid
                        AND mq.is_android_data_collection = TRUE;
                    END IF;
                END
            ');

            // Trigger to enforce only one success page per project
            DB::connection('mart')->unprepared('
                CREATE TRIGGER before_insert_success_page
                BEFORE INSERT ON mart_pages
                FOR EACH ROW
                BEGIN
                    IF NEW.is_success_page = TRUE THEN
                        UPDATE mart_pages
                        SET is_success_page = FALSE
                        WHERE mart_project_id = NEW.mart_project_id
                        AND is_success_page = TRUE;
                    END IF;
                END
            ');

            DB::connection('mart')->unprepared('
                CREATE TRIGGER before_update_success_page
                BEFORE UPDATE ON mart_pages
                FOR EACH ROW
                BEGIN
                    IF NEW.is_success_page = TRUE AND (OLD.is_success_page = FALSE OR OLD.is_success_page IS NULL) THEN
                        UPDATE mart_pages
                        SET is_success_page = FALSE
                        WHERE mart_project_id = NEW.mart_project_id
                        AND id != NEW.id
                        AND is_success_page = TRUE;
                    END IF;
                END
            ');
        }

        // For other database drivers (PostgreSQL, SQLite), application logic will handle uniqueness
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::connection('mart')->getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_insert_ios_data_collection_question');
            DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_update_ios_data_collection_question');
            DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_insert_android_data_collection_question');
            DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_update_android_data_collection_question');
            DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_insert_success_page');
            DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_update_success_page');
        }
    }
};
