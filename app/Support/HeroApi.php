<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use UnexpectedValueException;
use Throwable;

class HeroApi
{
    public static function url(): string
    {
        return preg_replace('~(?:/api)+$~', '', rtrim((string) config('services.backend_api.url'), '/')).'/api/hero-settings';
    }

    public static function payload(mixed $data): array
    {
        if (!is_array($data) || array_is_list($data)) {
            throw new UnexpectedValueException('Invalid hero payload');
        }
        return HeroSettings::validate($data);
    }

    public static function read(): array
    {
        $response = Http::acceptJson()->timeout(3)->connectTimeout(3)->get(self::url());
        if (!$response->successful()) {
            Log::warning('hero_read_failed', ['category' => 'http', 'status' => $response->status()]);
            throw new UnexpectedValueException('Hero unavailable');
        }
        try {
            return self::payload($response->json());
        } catch (Throwable $error) {
            Log::warning('hero_read_failed', ['category' => 'invalid']);
            throw new UnexpectedValueException('Invalid hero payload');
        }
    }

    public static function publicRead(): array
    {
        try {
            return self::read();
        } catch (ConnectionException) {
            Log::warning('hero_read_failed', ['category' => 'timeout']);
        } catch (Throwable) {
        }
        return HeroSettings::DEFAULTS;
    }
}
