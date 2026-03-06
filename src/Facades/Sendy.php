<?php

namespace NextMigrant\Sendy\Facades;

use Illuminate\Support\Facades\Facade;
use NextMigrant\Sendy\SendyResponse;
use NextMigrant\Sendy\SendyService;

/**
 * @method static ?SendyResponse subscribe(string $email, string $listId, string $firstName, string $lastName, ?string $fullName = null, array $options = [])
 * @method static ?SendyResponse unsubscribe(string $email, string $listId)
 * @method static ?SendyResponse deleteSubscriber(string $email, string $listId)
 * @method static ?SendyResponse getSubscriptionStatus(string $email, string $listId)
 * @method static ?SendyResponse getActiveSubscriberCount(string $listId)
 * @method static array getLists(int $brandId, bool $includeHidden = false)
 *
 * @see SendyService
 */
class Sendy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SendyService::class;
    }
}
