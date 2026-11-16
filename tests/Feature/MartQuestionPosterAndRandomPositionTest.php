<?php

namespace Tests\Feature;

use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * config.posterUrl and config.randomPositionWithinQuestionnaire on the
 * questionnaire routes. Config is saved as sent and reaches the app as it is,
 * so both are checked on store and update. The MART connection is rolled back
 * too, so the questionnaires don't stay behind.
 */
class MartQuestionPosterAndRandomPositionTest extends TestCase
{
    private const POSTER_URL = 'https://example.test/poster.jpg';

    private const VIDEO_URL = 'https://example.test/video.mp4';

    protected function connectionsToTransact()
    {
        return ['mysql', 'mart'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->project->update([
            'inputs' => json_encode([['type' => 'mart', 'name' => 'MART Configuration', 'answers' => []]]),
        ]);
        MartProject::firstOrCreate(['main_project_id' => $this->project->id]);
    }

    /** @test */
    public function the_questionnaire_route_saves_a_poster_url()
    {
        $question = $this->storedQuestion(['posterUrl' => self::POSTER_URL]);

        $this->assertSame(self::POSTER_URL, $question->config['posterUrl']);
    }

    /** @test */
    public function the_questionnaire_route_rejects_a_poster_url_that_is_not_a_url()
    {
        $this->storeQuestionnaire(['posterUrl' => 'not-a-valid-url'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    /** @test */
    public function the_questionnaire_route_rejects_a_poster_url_longer_than_2048_characters()
    {
        $this->storeQuestionnaire(['posterUrl' => 'https://example.test/' . str_repeat('a', 2028)])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    /** @test */
    public function the_questionnaire_route_rejects_a_poster_url_that_is_not_a_string()
    {
        $this->storeQuestionnaire(['posterUrl' => ['https://example.test/poster.jpg']])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    /**
     * ConvertEmptyStringsToNull turns an empty field into null, and the API
     * sends no posterUrl for it.
     *
     * @test
     */
    public function the_questionnaire_route_takes_an_empty_poster_url()
    {
        $question = $this->storedQuestion(['posterUrl' => '']);

        $this->assertNull($question->config['posterUrl'] ?? null);
    }

    /** @test */
    public function the_questionnaire_route_saves_a_random_position()
    {
        $question = $this->storedQuestion(['randomPositionWithinQuestionnaire' => true]);

        $this->assertSame(true, $question->config['randomPositionWithinQuestionnaire']);
    }

    /** @test */
    public function the_questionnaire_route_saves_a_random_position_of_false()
    {
        $question = $this->storedQuestion(['randomPositionWithinQuestionnaire' => false]);

        $this->assertSame(false, $question->config['randomPositionWithinQuestionnaire']);
    }

    /**
     * The `boolean` rule takes these, but they would reach the app as they
     * were sent.
     *
     * @test
     *
     * @dataProvider randomPositionsThatAreNotBooleans
     */
    public function the_questionnaire_route_rejects_a_random_position_that_is_not_a_boolean($value)
    {
        $this->storeQuestionnaire(['randomPositionWithinQuestionnaire' => $value])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);
    }

    public static function randomPositionsThatAreNotBooleans(): array
    {
        return [
            'the string "1"' => ['1'],
            'the string "true"' => ['true'],
            'the number 1' => [1],
            'the number 0' => [0],
        ];
    }

    /** @test */
    public function the_questionnaire_route_keeps_the_other_config_keys_next_to_both()
    {
        $config = [
            'options' => ['Yes', 'No'],
            'randomizationGroupId' => 2,
            'itemGroup' => 'intro',
            'randomPositionWithinQuestionnaire' => true,
            'posterUrl' => self::POSTER_URL,
        ];

        $question = $this->storedQuestion($config);

        $this->assertEquals($config, $question->config);
        $this->assertSame(true, $question->config['randomPositionWithinQuestionnaire']);
    }

    /** @test */
    public function the_questions_route_saves_a_poster_url()
    {
        $question = $this->storedQuestion([]);

        $this->updateQuestion($question, ['posterUrl' => self::POSTER_URL])->assertOk();

        $this->assertSame(self::POSTER_URL, $question->fresh()->config['posterUrl']);
    }

    /**
     * The route's own catch also answers 422, so the error key shows the
     * validation refused it.
     *
     * @test
     */
    public function the_questions_route_rejects_a_poster_url_that_is_not_a_url()
    {
        $question = $this->storedQuestion([]);

        $this->updateQuestion($question, ['posterUrl' => 'not-a-valid-url'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);

        $this->assertArrayNotHasKey('posterUrl', $question->fresh()->config);
    }

    /** @test */
    public function the_questions_route_saves_a_random_position()
    {
        $question = $this->storedQuestion([]);

        $this->updateQuestion($question, ['randomPositionWithinQuestionnaire' => true])->assertOk();

        $this->assertSame(true, $question->fresh()->config['randomPositionWithinQuestionnaire']);
    }

    /** @test */
    public function the_questions_route_rejects_a_random_position_that_is_not_a_boolean()
    {
        $question = $this->storedQuestion([]);

        $this->updateQuestion($question, ['randomPositionWithinQuestionnaire' => '1'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['questions.0']);

        $this->assertArrayNotHasKey('randomPositionWithinQuestionnaire', $question->fresh()->config);
    }

    /** @test */
    public function the_questions_route_keeps_the_other_config_keys_next_to_both()
    {
        $question = $this->storedQuestion([]);
        $config = [
            'options' => ['Yes', 'No'],
            'randomizeAnswers' => true,
            'randomPositionWithinQuestionnaire' => true,
            'posterUrl' => self::POSTER_URL,
        ];

        $this->updateQuestion($question, $config)->assertOk();

        $saved = $question->fresh()->config;
        $this->assertEquals($config, $saved);
        $this->assertSame(true, $saved['randomPositionWithinQuestionnaire']);
    }

    /**
     * What the editor sends after the video URL is cleared: video_url null and
     * no posterUrl.
     *
     * @test
     */
    public function the_questions_route_clears_the_video_and_its_poster()
    {
        $question = $this->storedQuestion(['posterUrl' => self::POSTER_URL]);

        $this->actingAs($this->user)
            ->putJson("/questionnaires/{$question->schedule_id}/questions", [
                'questions' => [[
                    'uuid' => $question->uuid,
                    'text' => $question->text,
                    'image_url' => null,
                    'video_url' => null,
                    'type' => 'one choice',
                    'mandatory' => true,
                    'config' => ['options' => ['Yes', 'No']],
                ]],
            ])->assertOk();

        $saved = $question->fresh();
        $this->assertNull($saved->video_url);
        $this->assertArrayNotHasKey('posterUrl', $saved->config);
    }

    /**
     * Posts a questionnaire with one single choice question with a video to
     * the questionnaire route.
     */
    private function storeQuestionnaire(array $config): TestResponse
    {
        return $this->actingAs($this->user)
            ->postJson("/projects/{$this->project->id}/questionnaires", [
                'questionnaire_id' => $this->project->id * 1000 + 503,
                'name' => 'Poster ' . uniqid(),
                'type' => 'single',
                'start_date_time' => ['date' => '2025-01-01', 'time' => '09:00'],
                'show_progress_bar' => true,
                'show_notifications' => false,
                'questions' => [[
                    'text' => 'Watch this video',
                    'video_url' => self::VIDEO_URL,
                    'type' => 'one choice',
                    'mandatory' => true,
                    'config' => ['options' => ['Yes', 'No'], ...$config],
                ]],
            ]);
    }

    private function storedQuestion(array $config): MartQuestion
    {
        $response = $this->storeQuestionnaire($config)->assertOk();

        return MartSchedule::findOrFail($response->json('schedule.id'))->questions()->firstOrFail();
    }

    /**
     * Saves the question with this config through the questions route.
     */
    private function updateQuestion(MartQuestion $question, array $config): TestResponse
    {
        return $this->actingAs($this->user)
            ->putJson("/questionnaires/{$question->schedule_id}/questions", [
                'questions' => [[
                    'uuid' => $question->uuid,
                    'text' => $question->text,
                    'video_url' => self::VIDEO_URL,
                    'type' => 'one choice',
                    'mandatory' => true,
                    'config' => ['options' => ['Yes', 'No'], ...$config],
                ]],
            ]);
    }
}
