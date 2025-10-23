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

class Dispatcher
{
    public static function dispatch(?string $requestUrl = null)
    {
        $res = Url::matchRequestRoute($requestUrl);
        echo '<pre>';
        print_r(Handler::getFallbacks());
        // print_r($res);
        // $params = $matched['params'] ?? [];

        // $controllerInvoker = function($params) use ($matched) {
        //     $controller = new $matched['controller']();
        //     return call_user_func_array([$controller, $matched['method']], $params);
        // };

        // // Collect before middlewares in order
        // $beforeMiddlewares = array_merge(
        //     $matched['middlewares']['global'],
        //     $matched['middlewares']['group'],
        //     $matched['middlewares']['route']
        // );

        // // Run before middlewares + controller
        // $response = $this->runMiddlewares($beforeMiddlewares, $params, $controllerInvoker);

        // // Now run afterwares in order global -> group -> route
        // foreach (['global', 'group', 'route'] as $level) {
        //     foreach ($matched['afterwares'][$level] ?? [] as $afterware) {
        //         $instance = new $afterware();
        //         if (method_exists($instance, 'handle')) {
        //             $response = $instance->handle($params, fn() => $response);
        //         }
        //     }
        // }

        // return $response;
    }

    protected function runMiddlewares(array $middlewares, array $params, callable $controllerInvoker)
    {
        $next = $controllerInvoker;

        // Reverse order so the first middleware wraps the next
        foreach (array_reverse($middlewares) as $middleware) {
            $next = function($params) use ($middleware, $next) {
                $instance = new $middleware();
                return $instance->handle($params, $next);
            };
        }

        // Execute the first one
        return $next($params);
    }
}
