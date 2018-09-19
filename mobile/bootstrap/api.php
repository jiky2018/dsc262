<?php

require_once dirname(__DIR__) . '/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Setting Environment
|--------------------------------------------------------------------------
|
*/

if (in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', '192.168.10.1'])) {
    putenv("APP_ENV=local");
    putenv("APP_DEBUG=true");
} else {
    putenv("APP_ENV=production");
    putenv("APP_DEBUG=false");
}
putenv("APP_TIMEZONE=Asia/Shanghai");

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//    App\Http\Middleware\ExampleMiddleware::class
// ]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(Dingo\Api\Provider\LumenServiceProvider::class);

app('Dingo\Api\Transformer\Factory')->setAdapter(function ($app) {
    return new Dingo\Api\Transformer\Adapter\Fractal(new League\Fractal\Manager, 'include', ',');
});

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->group(['namespace' => 'App\Api\Controllers'], function ($app) {
    require __DIR__ . '/../routes/api.php';
});

/*
|--------------------------------------------------------------------------
| 加载全局常量
|--------------------------------------------------------------------------
*/

require __DIR__ . '/../app/Support/constant.php';

/*
|--------------------------------------------------------------------------
| Load The Application Helpers
|--------------------------------------------------------------------------
|
*/

require __DIR__ . '/../app/Api/Support/helpers.php';

return $app;
