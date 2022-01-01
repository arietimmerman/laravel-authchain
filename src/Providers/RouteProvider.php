<?php
namespace ArieTimmerman\Laravel\AuthChain\Providers;

use Route;

/**
 * Helper class for the URL shortener
 */
class RouteProvider
{
    protected static $prefix = 'authchain';
    protected static $version = 'v2';

    public static function routesWeb(array $options = [])
    {
        $prefix = self::$prefix;

        Route::prefix(self::$prefix)->namespace('ArieTimmerman\Laravel\AuthChain\Http\Controllers')->group(
            function () use ($options, $prefix) {
                Route::prefix(self::$version)->group(
                    function () use ($options) {
                        self::webRoutes($options);
                    }
                );
            }
        );
    }

    public static function routesApi(array $options = [])
    {
        $prefix = self::$prefix;

        Route::prefix(self::$prefix)->namespace('ArieTimmerman\Laravel\AuthChain\Http\Controllers')->group(
            function () use ($options, $prefix) {
                Route::prefix(self::$version)->group(
                    function () use ($options) {
                        self::apiRoutes($options);
                    }
                );
            }
        );
    }

    public static function manageRoutes(array $options = [])
    {
        $prefix = self::$prefix;
        Route::prefix(self::$prefix)->namespace('ArieTimmerman\Laravel\AuthChain\Http\Controllers')->group(
            function () use ($options, $prefix) {
                Route::prefix(self::$version)->group(
                    function () use ($options) {
                        Route::get('/manage/types', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\TypeController@index');

                        Route::get('/manage/modules', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\AuthModuleController@index');

                        Route::get('/manage/modules/{module_id}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\AuthModuleController@get');
                        Route::get('/manage/modules/info/{module_id}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\AuthModuleController@info');

                        Route::delete('/manage/modules/{module_id}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\AuthModuleController@delete');
                        Route::put('/manage/modules/{module_id}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\AuthModuleController@update');

                        Route::post('/manage/modules', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\AuthModuleController@create');

                        Route::get('/manage/chain', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\ChainController@index');
                        Route::post('/manage/chain', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\ChainController@add');
                        Route::delete('/manage/chain/{chain_id}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\ChainController@delete');


                        Route::get('/manage/authlevels', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\AuthLevelController@index');
                        Route::get('/manage/authlevel/{authlevel_id}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\AuthLevelController@get');
                        Route::delete('/manage/authlevel/{authlevel_id}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\AuthLevelController@delete');

                        Route::post('/manage/authlevels', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\AuthLevelController@create');
                        Route::put('/manage/authlevel/{authlevel_id}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage\AuthLevelController@update');
                    }
                );
            }
        );
    }

    private static function webRoutes(array $options = [])
    {
        Route::prefix('p')->group(
            function () {
                Route::get('/redirect/{module}/{state}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\AuthChainController@redirect');

                // web routes
                Route::get('/complete', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\AuthChainController@complete')->name('authchain.complete.get');
                Route::post('/complete', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\AuthChainController@complete')->name('authchain.complete');
            }
        );
    }

    private static function apiRoutes(array $options = [])
    {
        Route::prefix('p')->group(
            function () {

                /**
                 *
                 */
                Route::get('/authresponse/{state}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\AuthChainController@getAuthResponse');

                /**
                 * State inspection endpoint
                 */

                Route::options('/{module}/{state}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\AuthChainController@processOptions');
                Route::get('/{module}/{state}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\AuthChainController@process');
            
                Route::post('/{module}', '\ArieTimmerman\Laravel\AuthChain\Http\Controllers\AuthChainController@process')->name('chainProcessor');

                Route::fallback('\ArieTimmerman\Laravel\AuthChain\Http\Controllers\AuthChainController@notFound');
            }
        );
    }
}
