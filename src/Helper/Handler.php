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

class Handler
{
    private static string $group = '';

    private static array $routes = [];

    private static array $onlyRoutes = [];

    private static string $lastMethod;

    private static string $lastUri;

    private static array $namedRoutes = [];

    private static array $fallbacks = [];

    private static array $globalMiddlewares = [];
    private static array $globalAfterwares = [];
    private static array $groupMiddlewares = [];
    private static array $groupAfterwares = [];
    private static array $afterwares = [];

    public static function register(string $method, string $uri, callable|string|array|null|object $controller, string|array $middlewares = []): void
    {
        // Capitalize Method
        self::$lastMethod = strtoupper($method);
        // Create Uri
        self::$lastUri = self::$group . Url::normalize($uri);
        self::$onlyRoutes[self::$lastMethod][] = self::$lastUri;
        // Set Route
        self::$routes[self::$lastMethod][self::$lastUri]['controller'] = $controller;
        // Set Middlewares
        self::$routes[self::$lastMethod][self::$lastUri]['middlewares'] = [
            'global'    =>  self::$globalMiddlewares,
            'group'     =>  self::$groupMiddlewares,
            'route'     =>  (array) $middlewares
        ];
        // Set Afterwares
        self::$routes[self::$lastMethod][self::$lastUri]['afterwares'] = [
            'global'    =>  self::$globalAfterwares,
            'group'     =>  self::$groupAfterwares,
            'route'     =>  self::$afterwares
        ];
        // Reset Afterwares
        self::$afterwares = [];
        return;
    }

    public static function registerGroup(string $prefix, callable $callback, string|array $middlewares, string|array $afterwares): void
    {
        // push normalized prefix fragment onto stack (ensures leading slash, no trailing)
        self::$group = Url::normalize($prefix);

        self::$groupMiddlewares = (array) $middlewares;
        self::$groupAfterwares = (array) $afterwares;

        // call user callback (allows Http::get() calls inside)
        $callback();

        // Reset Group, Middleware & Afterwares
        self::$group = '';
        self::$groupMiddlewares = [];
        self::$groupAfterwares = [];
        return;
    }

    public static function registerFallback(callable|string|array|null|object $callable = null, ?string $group = null): void
    {
        $group = Url::normalizeFallbackKey($group);
        self::$fallbacks[$group] = $callable;
        return;
    }

    public static function globalMiddlewareRegister(string|array $middlewares): void
    {
        self::$globalMiddlewares = array_merge(
            self::$globalMiddlewares,
            (array) $middlewares
        );
        return;
    }

    public static function globalAfterwareRegister(string|array $afterwares): void
    {
        self::$globalAfterwares = array_merge(
            self::$globalAfterwares,
            (array) $afterwares
        );
        return;
    }

    public static function middlewareRegister(string|array $middlewares): void
    {
        self::$routes[self::$lastMethod][self::$lastUri]['middlewares']['route'] = array_merge(
            self::$routes[self::$lastMethod][self::$lastUri]['middlewares']['route'],
            (array) $middlewares
        );
        return;
    }

    public static function afterwareRegister(string|array $afterwares): void
    {
        self::$routes[self::$lastMethod][self::$lastUri]['afterwares']['route'] = array_merge(
            self::$routes[self::$lastMethod][self::$lastUri]['afterwares']['route'],
            (array) $afterwares
        );
        return;
    }

    public static function getRoutes(?string $method = null): array
    {
        if ($method == null) {
            return self::$routes;
        }
        $method = strtoupper($method);
        return self::$routes[$method] ?? [];
    }

    public static function getOnlyRoutes(?string $method = null): array
    {
        if ($method == null) {
            return self::$onlyRoutes;
        }
        $method = strtoupper($method);
        return self::$onlyRoutes[$method] ?? [];
    }

    public static function getNamedRoutes(): array
    {
        return self::$namedRoutes;
    }

    public static function getFallbacks(): array
    {
        return self::$fallbacks;
    }

    public static function name(string $name): void
    {
        self::$routes[self::$lastMethod][self::$lastUri]['name'] = $name;
        self::$namedRoutes[$name] = [
            'method'    =>  self::$lastMethod,
            'uri'       =>  self::$lastUri
        ];
        return;
    }

    public static function namedUrl(string $name, array $params = []): string
    {
        $namedRoutes = self::getNamedRoutes();
        if (!array_key_exists($name, $namedRoutes)) {
            return '';
        }

        $uri = $namedRoutes[$name]['uri'];

        // Replace {param} placeholders
        foreach ($params as $key => $value) {
            $uri = preg_replace('/\{' . $key . '(:[^}]*)?\}/', (string) $value, $uri);
        }

        // Remove unreplaced params
        $uri = preg_replace('/\{[^}]+\}/', '', $uri);

        return Url::normalize($uri);
    }
}
