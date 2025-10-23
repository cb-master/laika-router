<?php

/**
 * Laika Database Model
 * Author: Showket Ahmed
 * Email: riyadhtayf@gmail.com
 * License: MIT
 * This file is part of the Laika PHP MVC Framework.
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Laika\Router\Helper;

class Url
{
    public static function normalize(string $uri): string
    {
        return '/' . trim($uri, '/');
    }

    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function matchRequestRoute(string $requestUrl): array
    {
        // Get Routes by Request Method
        $routes = Handler::getOnlyRoutes(self::method()) ?? null;
        if ($routes === null) {
            return [
                'route'     =>  null,
                'params'    =>  []
            ];
        }

        // Normalize Url
        $requestUrl = self::normalize($requestUrl);
        // Convert route placeholders to regex patterns
        foreach ($routes as $route) {
            $pattern = preg_replace_callback(
                '#\{(\w+)(?::([^/]+))?\}#',
                function ($matches) {
                    $name = $matches[1];
                    $regex = isset($matches[2]) ? $matches[2] : '[^/]+';
                    return '(?P<' . $name . '>' . $regex . ')';
                },
                $route
            );
            // Add regex anchors
            $pattern = '#^' . $pattern . '$#';

            // Try to match
            if (preg_match($pattern, $requestUrl, $matches)) {
                // Filter only named captures
                return [
                    'route'     =>  $route,
                    'params'    =>  array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY)
                ];
            }
        }

        return [
            'route'     =>  null,
            'params'    =>  []
        ];
    }

    public static function request(?string $requestUrl = null): string
    {
        if (!$requestUrl) {
            $requestUrl = ($_SERVER['REQUEST_URI'] ?? '/');
        }

        return self::normalize($requestUrl);
    }
}
