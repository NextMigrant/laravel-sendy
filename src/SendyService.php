<?php

namespace NextMigrant\Sendy;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class SendyService
{
    private ?string $apiKey;

    private ?string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('sendy.api_key');
        $this->baseUrl = config('sendy.url')
            ? rtrim(config('sendy.url'), '/')
            : null;
    }

    /**
     * Subscribe a user to a list.
     *
     * @param  array<string, mixed>  $options  Optional parameters: name, country, ipaddress, referrer, gdpr, silent, and any custom fields.
     *
     * @throws ConnectionException
     */
    public function subscribe(string $email, string $listId, string $firstName, string $lastName, ?string $fullName = null, array $options = []): ?SendyResponse
    {
        if (! app()->environment('production')) {
            return null;
        }

        $payload = array_merge($options, [
            'api_key' => $this->apiKey,
            'email' => $email,
            'list' => $listId,
            'Firstname' => $firstName,
            'Lastname' => $lastName,
            'boolean' => 'true',
        ]);

        $payload['name'] = $firstName.' '.$lastName;

        if ($fullName !== null) {
            $payload['name'] = $fullName;
        }

        $response = Http::asForm()->post("{$this->baseUrl}/subscribe", $payload);

        return SendyResponse::fromApiResponse($response->body());
    }

    /**
     * Unsubscribe a user from a list.
     *
     * @throws ConnectionException
     */
    public function unsubscribe(string $email, string $listId): ?SendyResponse
    {
        if (! app()->environment('production')) {
            return null;
        }

        $response = Http::asForm()->post("{$this->baseUrl}/unsubscribe", [
            'email' => $email,
            'list' => $listId,
            'boolean' => 'true',
        ]);

        return SendyResponse::fromApiResponse($response->body());
    }

    /**
     * Delete a subscriber from a list.
     *
     * @throws ConnectionException
     */
    public function deleteSubscriber(string $email, string $listId): ?SendyResponse
    {
        if (! app()->environment('production')) {
            return null;
        }

        $response = Http::asForm()->post("{$this->baseUrl}/api/subscribers/delete.php", [
            'api_key' => $this->apiKey,
            'list_id' => $listId,
            'email' => $email,
        ]);

        return SendyResponse::fromApiResponse($response->body());
    }

    /**
     * Get the subscription status of an email in a list.
     *
     * Possible success values: Subscribed, Unsubscribed, Unconfirmed, Bounced, Soft bounced, Complained.
     *
     * @throws ConnectionException
     */
    public function getSubscriptionStatus(string $email, string $listId): ?SendyResponse
    {
        if (! app()->environment('production')) {
            return null;
        }

        $response = Http::asForm()->post("{$this->baseUrl}/api/subscribers/subscription-status.php", [
            'api_key' => $this->apiKey,
            'email' => $email,
            'list_id' => $listId,
        ]);

        $body = trim($response->body());

        $validStatuses = ['Subscribed', 'Unsubscribed', 'Unconfirmed', 'Bounced', 'Soft bounced', 'Complained'];

        return new SendyResponse(
            success: in_array($body, $validStatuses),
            message: $body,
        );
    }

    /**
     * Get the total active subscriber count for a list.
     *
     * @throws ConnectionException
     */
    public function getActiveSubscriberCount(string $listId): ?SendyResponse
    {
        if (! app()->environment('production')) {
            return null;
        }

        $response = Http::asForm()->post("{$this->baseUrl}/api/subscribers/active-subscriber-count.php", [
            'api_key' => $this->apiKey,
            'list_id' => $listId,
        ]);

        $body = trim($response->body());

        return new SendyResponse(
            success: is_numeric($body),
            message: $body,
        );
    }

    /**
     * Get all lists for a brand.
     *
     * @return array<int, array{id: int, name: string}>
     *
     * @throws ConnectionException
     */
    public function getLists(int $brandId, bool $includeHidden = false): array
    {
        if (! app()->environment('production')) {
            return [];
        }

        $response = Http::asForm()->post("{$this->baseUrl}/api/lists/get-lists.php", [
            'api_key' => $this->apiKey,
            'brand_id' => $brandId,
            'include_hidden' => $includeHidden ? 'yes' : 'no',
        ]);

        return $response->json() ?? [];
    }
}
