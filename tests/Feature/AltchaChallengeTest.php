<?php

namespace Tests\Feature;

use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\Hasher\Algorithm;
use GrantHolle\Altcha\Rules\ValidAltcha;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Validator;
use Tests\CreatesApplication;

/**
 * Guards the registration captcha across altcha upgrades: a challenge from
 * /altcha-challenge, once solved, must pass the ValidAltcha rule.
 *
 * Extends the framework TestCase on purpose: no database needed.
 */
class AltchaChallengeTest extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        config(['altcha.hmac_key' => 'test-hmac-key', 'altcha.range_max' => 1000]);
    }

    public function test_a_solved_challenge_passes_the_captcha_rule(): void
    {
        $this->assertTrue($this->captchaRulePasses($this->solvedPayload()));
    }

    public function test_a_payload_with_a_wrong_number_fails_the_captcha_rule(): void
    {
        $this->assertFalse($this->captchaRulePasses($this->solvedPayload(numberOffset: 1)));
    }

    private function solvedPayload(int $numberOffset = 0): string
    {
        $challenge = $this->getJson('/altcha-challenge')->assertOk()->json();
        $solution = app(Altcha::class)->solveChallenge(
            $challenge['challenge'],
            $challenge['salt'],
            Algorithm::SHA256,
            $challenge['maxNumber'],
        );

        return base64_encode(json_encode([
            'algorithm' => $challenge['algorithm'],
            'challenge' => $challenge['challenge'],
            'number' => $solution->number + $numberOffset,
            'salt' => $challenge['salt'],
            'signature' => $challenge['signature'],
        ]));
    }

    private function captchaRulePasses(string $payload): bool
    {
        return Validator::make(['altcha' => $payload], ['altcha' => ['required', new ValidAltcha]])->passes();
    }
}
