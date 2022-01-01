<?php

namespace ArieTimmerman\Laravel\AuthChain;

use Illuminate\Contracts\Auth\Authenticatable;
use ArieTimmerman\Laravel\AuthChain\Events\Authenticated;
use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\Events\LoggedOut;

class Session
{
    public static function getRemembered($request)
    {
        return resolve(RememberStorage::class)->getRememberedModuleResults($request);
    }

    /**
     * Called via 'web' middleware. Access to session/cookies possible
     */
    public static function login(Authenticatable $subject, State $state)
    {
        resolve(RememberStorage::class)->saveModuleResults($subject, $state);

        event(new Authenticated($state));
    }

    public static function logout(Request $request)
    {
        resolve(RememberStorage::class)->clearModuleResults($request);

        event(new LoggedOut());
    }

    protected static function encrypt($s)
    {
        return $s ? Crypt::encryptString($s) : null;
    }

    public static function decrypt($s)
    {
        return $s ? Crypt::decryptString($s) : null;
    }

    public static function user()
    {
        
        //TODO: implement
    }
}
