<?php

/**
 * Laravel service provider for registering the routes and publishing the configuration.
 */

namespace ArieTimmerman\Laravel\AuthChain\Providers;

use App\Session\OIDCSession;
use ArieTimmerman\Laravel\AuthChain\Exceptions\AuthFailedException;
use ArieTimmerman\Laravel\AuthChain\Helper;
use ArieTimmerman\Laravel\AuthChain\AuthChain;
use ArieTimmerman\Laravel\AuthChain\Repository\ModuleRepository;
use ArieTimmerman\Laravel\AuthChain\Repository\ChainRepository;
use ArieTimmerman\Laravel\AuthChain\Repository\UserRepository;
use ArieTimmerman\Laravel\AuthChain\Repository\LinkRepository;
use ArieTimmerman\Laravel\AuthChain\Repository\SubjectRepository;
use ArieTimmerman\Laravel\AuthChain\Http\CompleteProcessor;
use ArieTimmerman\Laravel\AuthChain\Exceptions\NoStateException;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot(\Illuminate\Routing\Router $router)
    {
        $this->publishes(
            [
                __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'authchain.php' => config_path('authchain.php'),
            ]
        );

        $this->publishes(
            [
                __DIR__ . '/../../database/migrations/' => database_path('migrations')
            ],
            'migrations'
        );


        // Do not load migrations for now ...
        // $this->loadMigrationsFrom(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations');

        $this->loadRoutesFrom(__DIR__ . '/../../routes/routes.php');

        $this->loadViewsFrom(__DIR__ . '/../../views/', 'authchain');

        if ($this->app->runningInConsole()) {
            $this->commands(
                []
            );
        }

        $this->app->bind(
            'ArieTimmerman\Laravel\AuthChain\State',
            function ($app) {
                $state = Helper::getStateFromSession(request()->header('X-StateId'));

                /**
                 * Can occur in case (1) the login screen stays open very - very ! - long and (2) the state has expired in between.
                 */
                if ($state == null) {
                    throw new NoStateException('This state is no longer valid.');
                }

                return $state;
            }
        );

        $this->app->bindIf(
            'ArieTimmerman\Laravel\AuthChain\Repository\ModuleRepositoryInterface',
            ModuleRepository::class
        );

        $this->app->bind(
            \ArieTimmerman\Passport\OIDC\Session::class,
            OIDCSession::class
        );
        
        $this->app->bindIf('ArieTimmerman\Laravel\AuthChain\Repository\ChainRepositoryInterface', ChainRepository::class);

        $this->app->bindIf('ArieTimmerman\Laravel\AuthChain\Repository\UserRepositoryInterface', UserRepository::class);
        $this->app->bindIf('ArieTimmerman\Laravel\AuthChain\Repository\LinkRepositoryInterface', LinkRepository::class);
        $this->app->bindIf('ArieTimmerman\Laravel\AuthChain\Repository\SubjectRepositoryInterface', SubjectRepository::class);

        $this->app->bindIf('ArieTimmerman\Laravel\AuthChain\Http\CompleteProcessorInterface', CompleteProcessor::class);

        $this->app->singleton('ArieTimmerman\Laravel\AuthChain\AuthChain', AuthChain::class);

        AuthChain::addType('\ArieTimmerman\Laravel\AuthChain\Types\Password');
        AuthChain::addType('\ArieTimmerman\Laravel\AuthChain\Types\Consent');
        AuthChain::addType('\ArieTimmerman\Laravel\AuthChain\Types\Start');

        /**
         * The module the users chooses to use for authentication
         */
        $router->bind(
            'module',
            function ($moduleId, $route) {
                $module = \resolve('ArieTimmerman\Laravel\AuthChain\Repository\ModuleRepositoryInterface')->get($moduleId);

                if ($module == null) {
                    throw new AuthFailedException('Unknown module: ' . $moduleId);
                }

                return $module;
            }
        );

        /**
         * The state as originally created by us.
         * Uses to verify the client is allowed to initiate authentication attempts.
         */
        $router->bind(
            'state',
            function ($state, $route) {
                $state = Helper::getStateFromSession($state);

                if ($state == null) {
                    return response(null, 408);
                }

                // Save it as an instance so it's used for constructors etc
                $this->app->instance('ArieTimmerman\Laravel\AuthChain\State', $state);

                return $state;
            }
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'authchain.php',
            'authchain'
        );
    }
}
