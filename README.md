# Laravel Sendy

A reusable Laravel package that wraps the [Sendy](https://sendy.co) self-hosted email marketing API. It provides a clean `SendyService` class for subscribing/unsubscribing users, managing list membership, querying subscription statuses and active counts, and retrieving brand lists — all with a built-in production environment guard so no real API calls are ever made during development or testing.

## Installation

### 1. Add the VCS repository

```bash
composer config repositories.laravel-sendy vcs git@github.com:NextMigrant/laravel-sendy.git
```

### 2. Configure GitHub authentication

For Composer to access the private repo, configure a GitHub token:

```bash
# Locally (one-time, global)
composer config --global github-oauth.github.com YOUR_GITHUB_TOKEN

# In your deploy script (before composer install)
composer config github-oauth.github.com YOUR_GITHUB_TOKEN
```

### 3. Require the package

```bash
composer require nextmigrant/laravel-sendy
```

### 4. Publish the config (optional)

```bash
php artisan vendor:publish --tag=sendy-config
```

This creates `config/sendy.php`. You can also set everything via environment variables:

| Variable | Description |
|----------|-------------|
| `SENDY_API_KEY` | Your Sendy installation API key |
| `SENDY_URL` | Base URL of your Sendy installation (e.g. `https://sendy.yourdomain.com`) |
| `SENDY_NEW_USERS_LIST_ID` | List ID for the default "new signups" list |

## Usage

```php
use NextMigrant\Sendy\SendyService;

$sendy = new SendyService;
```

> **Note:** All methods are guarded by `app()->environment('production')`. In non-production environments they return `null` (or `[]` for `getLists`) without making any API calls.

### Subscribe

```php
$response = $sendy->subscribe(
    email: 'john@example.com',
    listId: 'your-list-id',
    firstName: 'John',
    lastName: 'Doe',
);

// With optional full name and custom fields
$response = $sendy->subscribe(
    email: 'john@example.com',
    listId: 'your-list-id',
    firstName: 'John',
    lastName: 'Doe',
    fullName: 'Dr. John Doe',
    options: [
        'country' => 'CA',
        'gdpr' => 'true',
        'Birthday' => '1990-01-15',
    ],
);
```

### Unsubscribe

```php
$response = $sendy->unsubscribe('john@example.com', 'your-list-id');
```

### Delete Subscriber

```php
$response = $sendy->deleteSubscriber('john@example.com', 'your-list-id');
```

### Get Subscription Status

```php
$response = $sendy->getSubscriptionStatus('john@example.com', 'your-list-id');

// $response->message will be one of:
// Subscribed, Unsubscribed, Unconfirmed, Bounced, Soft bounced, Complained
```

### Get Active Subscriber Count

```php
$response = $sendy->getActiveSubscriberCount('your-list-id');

// $response->message contains the count as a string (e.g. "1523")
```

### Get Lists

```php
$lists = $sendy->getLists(brandId: 1);
// Returns: [['id' => 1, 'name' => 'Newsletter'], ...]

// Include hidden lists
$lists = $sendy->getLists(brandId: 1, includeHidden: true);
```

## Response Object

All methods (except `getLists`) return a `SendyResponse` DTO:

```php
use NextMigrant\Sendy\SendyResponse;

$response->success; // bool
$response->message; // string — "1" on success, or an error message
```

The static factory `SendyResponse::fromApiResponse(string $body)` treats `"1"` and `"true"` (case-insensitive) as success.

## Testing

```bash
composer test
```

## License

Proprietary — NextMigrant. All rights reserved.
