<?php
/**
 * Example request
 *
 * {
 *     "username": "piet",
 *     "password": "pass123",
 *  "remember": true  // (remember on browser close)
 * }
 */
namespace ArieTimmerman\Laravel\AuthChain\Types;

use ArieTimmerman\Laravel\AuthChain\Module\ModuleResult;
use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\State;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;

class Start extends AbstractType
{
    public function isPassive()
    {
        return true;
    }

    /**
     * @return ModuleResult
     */
    public function process(Request $request, State $state, ModuleInterface $module)
    {
        return $module->baseResult()->complete()->setPrompted(false)->setRememberAlways(false)->setRememberForSession(false);
    }
}
