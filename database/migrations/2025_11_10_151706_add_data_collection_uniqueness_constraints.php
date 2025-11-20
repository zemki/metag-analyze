<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDataCollectionUniquenessConstraints extends Migration
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
     *
     * @return void
     */
    public function up()
    {
        $connection = Schema::connection('mart')->getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            // Try to create triggers, but gracefully handle permission errors
            // In environments without SUPER privilege, application logic will enforce constraints
            try {
                // Trigger to enforce only one iOS data collection question per project
                DB::connection('mart')->unprepared('
                CREATE TRIGGER before_insert_ios_data_collection_question
                BEFORE INSERT ON mart_questions
                FOR EACH ROW
                BEGIN
                    IF NEW.is_ios_data_collection = TRUE THEN
                        UPDATE mart_questions mq
                        INNER JOIN mart_schedules ms ON mq.schedule_id = ms.id
                        SET mq.is_ios_data_collection = FALSE
                        WHERE ms.mart_project_id = (
                            SELECT mart_project_id FROM mart_schedules WHERE id = NEW.schedule_id
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
                        INNER JOIN mart_schedules ms ON mq.schedule_id = ms.id
                        SET mq.is_ios_data_collection = FALSE
                        WHERE ms.mart_project_id = (
                            SELECT mart_project_id FROM mart_schedules WHERE id = NEW.schedule_id
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
                        INNER JOIN mart_schedules ms ON mq.schedule_id = ms.id
                        SET mq.is_android_data_collection = FALSE
                        WHERE ms.mart_project_id = (
                            SELECT mart_project_id FROM mart_schedules WHERE id = NEW.schedule_id
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
                        INNER JOIN mart_schedules ms ON mq.schedule_id = ms.id
                        SET mq.is_android_data_collection = FALSE
                        WHERE ms.mart_project_id = (
                            SELECT mart_project_id FROM mart_schedules WHERE id = NEW.schedule_id
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
            } catch (\Illuminate\Database\QueryException $e) {
                // Check if error is due to insufficient privileges
                if (strpos($e->getMessage(), 'You do not have the SUPER privilege') !== false ||
                    strpos($e->getMessage(), 'log_bin_trust_function_creators') !== false) {
                    \Log::warning('Skipping trigger creation - insufficient database privileges (SUPER required). Application logic will enforce uniqueness constraints.');
                    \Log::warning('To enable triggers, ask your database administrator to run: SET GLOBAL log_bin_trust_function_creators = 1;');
                } else {
                    // Re-throw if it's a different error
                    throw $e;
                }
            }
        }

        // For other database drivers (PostgreSQL, SQLite), application logic will handle uniqueness
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = Schema::connection('mart')->getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            // DROP TRIGGER IF EXISTS should be safe even without SUPER privilege
            try {
                DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_insert_ios_data_collection_question');
                DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_update_ios_data_collection_question');
                DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_insert_android_data_collection_question');
                DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_update_android_data_collection_question');
                DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_insert_success_page');
                DB::connection('mart')->unprepared('DROP TRIGGER IF EXISTS before_update_success_page');
            } catch (\Exception $e) {
                // Silently continue if triggers don't exist or can't be dropped
                \Log::info('Triggers may not exist or cannot be dropped: ' . $e->getMessage());
            }
        }
    }
}
