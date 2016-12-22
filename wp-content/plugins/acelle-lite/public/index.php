<?php

if(!extension_loaded('mbstring')) {
    echo "ERROR: The requested PHP extension mbstring is missing from your system.";
} elseif (!(file_exists('../storage/app') && is_dir('../storage/app') && (is_writable('../storage/app')))) {
    echo "ERROR: The directory [/storage/app] must be writable by the web server.";
} elseif (!(file_exists('../storage/framework') && is_dir('../storage/framework') && (is_writable('../storage/framework')))) {
    echo "ERROR: The directory [/storage/framework] must be writable by the web server.";
} elseif (!(file_exists('../storage/logs') && is_dir('../storage/logs') && (is_writable('../storage/logs')))) {
    echo "ERROR: The directory [/storage/logs] must be writable by the web server.";
} elseif (!(file_exists('../bootstrap/cache') && is_dir('../bootstrap/cache') && (is_writable('../bootstrap/cache')))) {
    echo "ERROR: The directory [/bootstrap/cache] must be writable by the web server.";
} else {

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/

require __DIR__.'/../bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);

}
