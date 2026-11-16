<?php

namespace Tests\Feature;

use App\Cases;
use App\User;
use Tests\TestCase;

class MartDeviceTokenTest extends TestCase
{
    protected string $rawToken = 'device_token_test_1234567890abcdef';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user->forceFill([
            'api_token' => hash('sha256', $this->rawToken),
            'deviceID' => null,
        ])->save();

        $this->case->update(['name' => 'P_DEVICE1']);
    }

    private function deviceInfoPayload(array $overrides = []): array
    {
        return array_merge([
            'projectId' => $this->project->id,
            'userId' => $this->user->email,
            'participantId' => $this->case->name,
            'os' => 'android',
            'osVersion' => '14',
            'model' => 'Pixel 7',
            'manufacturer' => 'Google',
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ], $overrides);
    }

    /** @test */
    public function it_registers_a_device_token()
    {
        $response = $this->withToken($this->rawToken)
            ->postJson('/api/mobile/device-token', ['token' => 'fcm-token-a']);

        $response->assertOk()->assertJson(['success' => true, 'changed' => true, 'registered_devices' => 1]);
        $this->assertSame(['fcm-token-a'], $this->user->fresh()->deviceID);
    }

    /** @test */
    public function it_registers_a_device_token_through_the_old_url()
    {
        $this->withToken($this->rawToken)
            ->postJson('/mart-api/device-token', ['token' => 'fcm-token-a'])
            ->assertOk();

        $this->assertSame(['fcm-token-a'], $this->user->fresh()->deviceID);
    }

    /** @test */
    public function it_unregisters_a_device_token()
    {
        $this->user->forceFill(['deviceID' => ['fcm-token-a', 'fcm-token-b']])->save();

        $this->withToken($this->rawToken)
            ->postJson('/api/mobile/device-token', ['token' => 'fcm-token-a', 'action' => 'unregister'])
            ->assertOk()
            ->assertJson(['changed' => true, 'registered_devices' => 1]);

        $this->assertSame(['fcm-token-b'], $this->user->fresh()->deviceID);
    }

    /** @test */
    public function it_keeps_only_the_newest_ten_tokens()
    {
        $this->user->forceFill(['deviceID' => array_map(fn ($i) => "old-{$i}", range(1, 10))])->save();

        $this->withToken($this->rawToken)
            ->postJson('/api/mobile/device-token', ['token' => 'newest'])
            ->assertOk();

        $tokens = $this->user->fresh()->deviceID;
        $this->assertCount(User::MAX_DEVICE_TOKENS, $tokens);
        $this->assertNotContains('old-1', $tokens);
        $this->assertSame('newest', end($tokens));
    }

    /** @test */
    public function it_requires_a_bearer_token()
    {
        $this->postJson('/api/mobile/device-token', ['token' => 'fcm-token-a'])
            ->assertUnauthorized();
    }

    /** @test */
    public function device_infos_stores_the_fcm_token_on_the_token_holder()
    {
        $this->withToken($this->rawToken)
            ->postJson('/api/mobile/device-infos', $this->deviceInfoPayload(['fcmToken' => 'fcm-from-device-infos']))
            ->assertOk()
            ->assertJson(['push_token_registered' => true]);

        $this->assertSame(['fcm-from-device-infos'], $this->user->fresh()->deviceID);
    }

    /** @test */
    public function device_infos_rejects_another_users_email()
    {
        $other = User::factory()->create(['deviceID' => null]);
        $otherCase = Cases::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $other->id,
            'name' => 'P_OTHER1',
        ]);

        $this->withToken($this->rawToken)
            ->postJson('/api/mobile/device-infos', $this->deviceInfoPayload([
                'userId' => $other->email,
                'participantId' => $otherCase->name,
                'fcmToken' => 'attacker-token',
            ]))
            ->assertForbidden();

        $this->assertEmpty($other->fresh()->deviceID);
    }
}
