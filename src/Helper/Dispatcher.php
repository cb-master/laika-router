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

use Closure;
use RuntimeException;

class Dispatcher
{
    public static function dispatch(?string $requestUrl = null)
    {
        $requestUrl = Url::request($requestUrl);
        $res = Url::matchRequestRoute($requestUrl);

        $params = $res['params'];

        // Execute Fallback For Invalid Route
        if ($res['route'] === null) {

            // 404 Response
            http_response_code(404);

            $fallbacks = Handler::getFallbacks();

            foreach (array_reverse($fallbacks) as $key => $callable){
                if (str_starts_with(Url::normalizeFallbackKey($requestUrl), $key)) {
                    return Invoke::controller($callable, $params);
                }
            }
            /*---- Execute Fallback ----*/
            return _404::show();
        }

        $routes = Handler::getRoutes(Url::method());
        $route = $routes[$res['route']];
        
        // $invoker = self::invoke($route['controller'], $params);

        // Collect before middlewares in order
        $middlewares = array_merge(
            $route['middlewares']['global'],
            $route['middlewares']['group'],
            $route['middlewares']['route']
        );

        // Run Middlewares -> Controller
        $response = Invoke::middleware($middlewares, $route['controller'], $params);

        // Run Afterware
        $afterwares = array_merge(
            $route['afterwares']['global'],
            $route['afterwares']['group'],
            $route['afterwares']['route']
        );

        echo empty($afterwares) ? $response : Invoke::afterware($afterwares, $response, $params);
    }
}
