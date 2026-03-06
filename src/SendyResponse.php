<?php

namespace NextMigrant\Sendy;

class SendyResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
    ) {}

    /**
     * Parse a plain-text Sendy API response into a SendyResponse.
     *
     * Sendy returns "1" or "true" for success, or a plain-text error message.
     * For the active subscriber count endpoint, a numeric string indicates success.
     */
    public static function fromApiResponse(string $body): self
    {
        $body = trim($body);

        $isSuccess = $body === '1' || strtolower($body) === 'true';

        return new self(
            success: $isSuccess,
            message: $body,
        );
    }
}
