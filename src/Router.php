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

namespace Laika\Router;

class Router
{
    public static function get(string $uri, callable|string|array|null|object $controller = null, string|array $middlewares = []): self
    {
        Helper\Handler::register('get', $uri, $controller, $middlewares);
        return new self();
    }

    public static function post(string $uri, callable|string|array|null|object $controller = null, string|array $middlewares = []): self
    {
        Helper\Handler::register('post', $uri, $controller, $middlewares);
        return new self();
    }

    public static function put(string $uri, callable|string|array|null|object $controller = null, string|array $middlewares = []): self
    {
        Helper\Handler::register('put', $uri, $controller, $middlewares);
        return new self();
    }

    public static function patch(string $uri, callable|string|array|null|object $controller = null, string|array $middlewares = []): self
    {
        Helper\Handler::register('patch', $uri, $controller, $middlewares);
        return new self();
    }

    public static function options(string $uri, callable|string|array|null|object $controller = null, string|array $middlewares = []): self
    {
        Helper\Handler::register('options', $uri, $controller, $middlewares);
        return new self();
    }

    public static function group(string $prefix, callable $handler, string|array $middlewares = [], string|array $afterwares = [])
    {
        Helper\Handler::registerGroup($prefix, $handler, $middlewares, $afterwares);
        return new self();
    }

    public static function middleware(string|array $middlewares): self
    {
        Helper\Handler::middlewareRegister($middlewares);
        return new self();
    }

    public static function afterware(string|array $afterware): self
    {
        Helper\Handler::afterwareRegister($afterware);
        return new self();
    }

    public static function globalMiddleware(string|array $middlewares): void
    {
        Helper\Handler::globalMiddlewareRegister($middlewares);
        return;
    }

    public static function globalAfterware(string|array $afterwares): void
    {
        Helper\Handler::globalAfterwareRegister($afterwares);
        return;
    }


    public static function dispatch(?string $requestUrl = null)
    {
        // $uri = str_replace('php', '', trim($_SERVER['REQUEST_URI'], '/')) ?: '/';
        Helper\Dispatcher::dispatch($requestUrl);
        // $uri = $uri ?? $_SERVER['REQUEST_URI'];
        // return Helper\Handler::getRoutes();
    }

    public static function fallback(callable|string|array|null|object $callable = null, string $group = '/'): void
    {
        Helper\Handler::registerFallback($callable, $group);
    }

    public function name(string $name): self
    {
        Helper\Handler::name($name);
        return new self();
    }

    public static function url(string $name, array $param = [])
    {
        return Helper\Handler::namedUrl($name, $param);
    }
}
