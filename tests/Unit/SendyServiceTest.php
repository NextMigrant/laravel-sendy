<?php

namespace NextMigrant\Sendy\Tests\Unit;

use Illuminate\Support\Facades\Http;
use NextMigrant\Sendy\Facades\Sendy;
use NextMigrant\Sendy\SendyResponse;
use NextMigrant\Sendy\SendyService;
use NextMigrant\Sendy\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SendyServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'sendy.enabled' => true,
            'sendy.api_key' => 'test-api-key',
            'sendy.url' => 'https://sendy.example.com',
        ]);
    }

    // ─── Enabled Guard ───────────────────────────────────────────────

    public function test_returns_early_when_disabled(): void
    {
        config(['sendy.enabled' => false]);

        Http::fake();

        $service = new SendyService;

        $subscribeResult = $service->subscribe('test@example.com', 'list-abc', 'Test', 'User');
        $this->assertNull($subscribeResult);

        $unsubscribeResult = $service->unsubscribe('test@example.com', 'list-abc');
        $this->assertNull($unsubscribeResult);

        $deleteResult = $service->deleteSubscriber('test@example.com', 'list-abc');
        $this->assertNull($deleteResult);

        $statusResult = $service->getSubscriptionStatus('test@example.com', 'list-abc');
        $this->assertNull($statusResult);

        $countResult = $service->getActiveSubscriberCount('list-abc');
        $this->assertNull($countResult);

        $lists = $service->getLists(1);
        $this->assertEmpty($lists);

        Http::assertNothingSent();
    }

    // ─── Subscribe ──────────────────────────────────────────────────

    public function test_subscribes_a_user_successfully(): void
    {
        Http::fake([
            'sendy.example.com/subscribe' => Http::response('1'),
        ]);

        $service = new SendyService;
        $result = $service->subscribe('john@example.com', 'list-abc', 'John', 'Doe');

        $this->assertInstanceOf(SendyResponse::class, $result);
        $this->assertTrue($result->success);
        $this->assertEquals('1', $result->message);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://sendy.example.com/subscribe'
                && $request['email'] === 'john@example.com'
                && $request['list'] === 'list-abc'
                && $request['api_key'] === 'test-api-key'
                && $request['Firstname'] === 'John'
                && $request['Lastname'] === 'Doe'
                && $request['boolean'] === 'true';
        });
    }

    public function test_subscribes_a_user_with_full_name_and_optional_params(): void
    {
        Http::fake([
            'sendy.example.com/subscribe' => Http::response('1'),
        ]);

        $service = new SendyService;
        $result = $service->subscribe('john@example.com', 'list-abc', 'John', 'Doe', 'John Doe', [
            'country' => 'CA',
            'gdpr' => 'true',
            'silent' => 'true',
            'Birthday' => '1990-01-15',
        ]);

        $this->assertTrue($result->success);

        Http::assertSent(function ($request) {
            return $request['name'] === 'John Doe'
                && $request['Firstname'] === 'John'
                && $request['Lastname'] === 'Doe'
                && $request['country'] === 'CA'
                && $request['gdpr'] === 'true'
                && $request['silent'] === 'true'
                && $request['Birthday'] === '1990-01-15';
        });
    }

    public function test_returns_error_when_subscribing_with_already_subscribed_email(): void
    {
        Http::fake([
            'sendy.example.com/subscribe' => Http::response('Already subscribed.'),
        ]);

        $service = new SendyService;
        $result = $service->subscribe('john@example.com', 'list-abc', 'John', 'Doe');

        $this->assertFalse($result->success);
        $this->assertEquals('Already subscribed.', $result->message);
    }

    public function test_returns_error_when_subscribing_with_invalid_email(): void
    {
        Http::fake([
            'sendy.example.com/subscribe' => Http::response('Invalid email address.'),
        ]);

        $service = new SendyService;
        $result = $service->subscribe('not-an-email', 'list-abc', 'Bad', 'User');

        $this->assertFalse($result->success);
        $this->assertEquals('Invalid email address.', $result->message);
    }

    // ─── Unsubscribe ────────────────────────────────────────────────

    public function test_unsubscribes_a_user_successfully(): void
    {
        Http::fake([
            'sendy.example.com/unsubscribe' => Http::response('1'),
        ]);

        $service = new SendyService;
        $result = $service->unsubscribe('john@example.com', 'list-abc');

        $this->assertTrue($result->success);
        $this->assertEquals('1', $result->message);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://sendy.example.com/unsubscribe'
                && $request['email'] === 'john@example.com'
                && $request['list'] === 'list-abc'
                && $request['boolean'] === 'true';
        });
    }

    public function test_returns_error_when_unsubscribing_non_existent_email(): void
    {
        Http::fake([
            'sendy.example.com/unsubscribe' => Http::response('Email does not exist.'),
        ]);

        $service = new SendyService;
        $result = $service->unsubscribe('nobody@example.com', 'list-abc');

        $this->assertFalse($result->success);
        $this->assertEquals('Email does not exist.', $result->message);
    }

    // ─── Delete Subscriber ──────────────────────────────────────────

    public function test_deletes_a_subscriber_successfully(): void
    {
        Http::fake([
            'sendy.example.com/api/subscribers/delete.php' => Http::response('1'),
        ]);

        $service = new SendyService;
        $result = $service->deleteSubscriber('john@example.com', 'list-abc');

        $this->assertTrue($result->success);
        $this->assertEquals('1', $result->message);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://sendy.example.com/api/subscribers/delete.php'
                && $request['email'] === 'john@example.com'
                && $request['list_id'] === 'list-abc'
                && $request['api_key'] === 'test-api-key';
        });
    }

    public function test_returns_error_when_deleting_non_existent_subscriber(): void
    {
        Http::fake([
            'sendy.example.com/api/subscribers/delete.php' => Http::response('Subscriber does not exist'),
        ]);

        $service = new SendyService;
        $result = $service->deleteSubscriber('nobody@example.com', 'list-abc');

        $this->assertFalse($result->success);
        $this->assertEquals('Subscriber does not exist', $result->message);
    }

    // ─── Subscription Status ────────────────────────────────────────

    #[DataProvider('validSubscriptionStatusesProvider')]
    public function test_returns_subscription_status_for_valid_status(string $status): void
    {
        Http::fake([
            'sendy.example.com/api/subscribers/subscription-status.php' => Http::response($status),
        ]);

        $service = new SendyService;
        $result = $service->getSubscriptionStatus('john@example.com', 'list-abc');

        $this->assertTrue($result->success);
        $this->assertEquals($status, $result->message);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function validSubscriptionStatusesProvider(): array
    {
        return [
            'Subscribed' => ['Subscribed'],
            'Unsubscribed' => ['Unsubscribed'],
            'Unconfirmed' => ['Unconfirmed'],
            'Bounced' => ['Bounced'],
            'Soft bounced' => ['Soft bounced'],
            'Complained' => ['Complained'],
        ];
    }

    public function test_returns_error_for_invalid_subscription_status(): void
    {
        Http::fake([
            'sendy.example.com/api/subscribers/subscription-status.php' => Http::response('Email does not exist in list'),
        ]);

        $service = new SendyService;
        $result = $service->getSubscriptionStatus('nobody@example.com', 'list-abc');

        $this->assertFalse($result->success);
        $this->assertEquals('Email does not exist in list', $result->message);
    }

    // ─── Active Subscriber Count ────────────────────────────────────

    public function test_returns_active_subscriber_count(): void
    {
        Http::fake([
            'sendy.example.com/api/subscribers/active-subscriber-count.php' => Http::response('1523'),
        ]);

        $service = new SendyService;
        $result = $service->getActiveSubscriberCount('list-abc');

        $this->assertTrue($result->success);
        $this->assertEquals('1523', $result->message);

        Http::assertSent(function ($request) {
            return $request['api_key'] === 'test-api-key'
                && $request['list_id'] === 'list-abc';
        });
    }

    public function test_returns_error_for_invalid_list_on_subscriber_count(): void
    {
        Http::fake([
            'sendy.example.com/api/subscribers/active-subscriber-count.php' => Http::response('List does not exist'),
        ]);

        $service = new SendyService;
        $result = $service->getActiveSubscriberCount('bad-list');

        $this->assertFalse($result->success);
        $this->assertEquals('List does not exist', $result->message);
    }

    // ─── Get Lists ──────────────────────────────────────────────────

    public function test_returns_lists_for_a_brand(): void
    {
        $lists = [
            ['id' => 1, 'name' => 'Newsletter'],
            ['id' => 2, 'name' => 'Updates'],
        ];

        Http::fake([
            'sendy.example.com/api/lists/get-lists.php' => Http::response($lists),
        ]);

        $service = new SendyService;
        $result = $service->getLists(1);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Newsletter', $result[0]['name']);

        Http::assertSent(function ($request) {
            return $request['brand_id'] == 1
                && $request['include_hidden'] === 'no';
        });
    }

    public function test_includes_hidden_lists_when_requested(): void
    {
        Http::fake([
            'sendy.example.com/api/lists/get-lists.php' => Http::response([]),
        ]);

        $service = new SendyService;
        $service->getLists(1, includeHidden: true);

        Http::assertSent(function ($request) {
            return $request['include_hidden'] === 'yes';
        });
    }

    // ─── SendyResponse ──────────────────────────────────────────────

    public function test_parses_true_as_success(): void
    {
        $response = SendyResponse::fromApiResponse('true');

        $this->assertTrue($response->success);
        $this->assertEquals('true', $response->message);
    }

    public function test_parses_1_as_success(): void
    {
        $response = SendyResponse::fromApiResponse('1');

        $this->assertTrue($response->success);
        $this->assertEquals('1', $response->message);
    }

    public function test_parses_error_text_as_failure(): void
    {
        $response = SendyResponse::fromApiResponse('Invalid API key');

        $this->assertFalse($response->success);
        $this->assertEquals('Invalid API key', $response->message);
    }

    // ─── Facade ──────────────────────────────────────────────────────

    public function test_facade_resolves_to_sendy_service(): void
    {
        $this->assertInstanceOf(SendyService::class, Sendy::getFacadeRoot());
    }

    public function test_facade_resolves_as_singleton(): void
    {
        $first = Sendy::getFacadeRoot();
        $second = Sendy::getFacadeRoot();

        $this->assertSame($first, $second);
    }

    public function test_facade_can_call_subscribe(): void
    {
        Http::fake([
            'sendy.example.com/subscribe' => Http::response('1'),
        ]);

        $result = Sendy::subscribe('john@example.com', 'list-abc', 'John', 'Doe');

        $this->assertInstanceOf(SendyResponse::class, $result);
        $this->assertTrue($result->success);
    }
}
