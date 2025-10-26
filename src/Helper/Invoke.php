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

use RuntimeException;
use Closure;

class Invoke
{
    public static function middleware(array $middlewares, callable|string|array|null|object $controller, array $params = []): ?string
    {
        // Build chain backwards
        $next = array_reduce(array_reverse($middlewares), function ($next, $middleware) use ($params) {
            return function ($params) use ($middleware, $next) {
                // Separate Parameters From Middleware
                $parts = explode('|', $middleware);
                $middleware = $parts[0];
                if (isset($parts[1])) {
                    $args = [];
                    $paramParts = explode(',', $parts[1]);
                    foreach ($paramParts as $paramPart) {
                        [$k,$v] = explode('=', $paramPart);
                        $args[trim($k)] = trim($v);
                    }
                    $params = array_merge($params, $args);
                }
                if (!class_exists($middleware)) {
                    throw new \Exception("Invalid middleware: {$middleware}");
                }
                $obj = new $middleware;

                // Check handle Method Exists
                if (!method_exists($obj, 'handle')) {
                    throw new \Exception("'handle' Method Doesn't Exists in {$middleware}");
                }
                return $obj->handle($next, $params);
            };
        },
        // Final callable (controller)
        function ($params) use ($controller) {
            return self::controller($controller, $params);
        });

        // Execute the full chain
        return $next($params);
    }

    public static function afterware(array $afterwares, ?string $response, array $params = []): ?string
    {
        // Build the chain in normal order (global → group → route)
        $next = array_reduce(
            array_reverse($afterwares), // reverse to preserve execution order
            function ($next, $afterware) use ($params) {
                return function ($response) use ($afterware, $next, $params) {
                    if (!class_exists($afterware)) {
                        throw new \RuntimeException("Invalid Afterware: {$afterware}");
                    }

                    $obj = new $afterware;

                    if (!method_exists($obj, 'terminate')) {
                        throw new \RuntimeException("'terminate' Method Doesn't Exist in {$afterware}");
                    }

                    // Execute the current afterware, passing response and chain
                    return $obj->terminate($response, function ($newResponse) use ($next, $params) {
                        return $next($newResponse);
                    }, $params);
                };
            },
            fn($response) => $response // initial chain returns final response
        );

        return $next($response);
    }

    public static function controller(callable|string|array|null|object $handler, array $args): ?string
    {
        if (is_null($handler)) {
            return null;
        }

        if (is_callable($handler)) {
            // Closure Call
            if ($handler instanceof Closure) {
                return $handler(...$args);
            }
            return call_user_func($handler, ...$args);
        }

        if (is_array($handler)) {
            [$controller, $method] = $handler;
            // Check Controller Exists
            if (!class_exists($controller)) {
                throw new RuntimeException("Controller '{$controller}' Doesn't Exists");
            }
            // Check Method Exists
            if (!method_exists($controller, $method)) {
                throw new RuntimeException("Controller Method '{$method}' Doesn't Exists");
            }
            // Call Controller
            $obj = new $controller();
            return call_user_func([$obj, $method], ...$args);
        }

        if (is_object($handler)) {
            // Check Method Exists
            if (!method_exists($handler, 'index')) {
                throw new RuntimeException("Method 'index' Doesn't Exists in Controller " . get_class($handler));
            }
            // Call Controller
            return call_user_func([$handler, 'index'], ...$args);
        }

        if (is_string($handler)) {
            [$controller, $method] = explode('@', $handler);
            $controller = "Laika\\App\\Controller\\{$controller}";
            // Check Controller Exists
            if (!class_exists($controller)) {
                throw new RuntimeException("Controller '{$controller}' Doesn't Exists");
            }
            // Check Method Exists
            if (!method_exists($controller, $method)) {
                throw new RuntimeException("Method '{$method}' Doesn't Exists in Controller '{$controller}'");
            }
            // Call Controller
            $obj = new $controller();
            return call_user_func([$obj, $method], ...$args);
        }

        throw new RuntimeException("Invalid Controller: " . print_r($handler, true));
    }
}
