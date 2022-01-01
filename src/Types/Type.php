<?php
/**
 * A module is of a certain type
 */

namespace ArieTimmerman\Laravel\AuthChain\Types;

use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\State;
use ArieTimmerman\Laravel\AuthChain\Module\Module;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleResult;
use ArieTimmerman\Laravel\AuthChain\Object\Subject;

interface Type
{
    
    /**
     * Run before execution
     *
     * @return self
     */
    public function init(Request $request, State $state, ModuleInterface $module);

    /**
     * Execute. Returns
     *
     * @return ArieTimmerman\Laravel\AuthChain\Module\ModuleResult
     */
    public function process(Request $request, State $state, ModuleInterface $module);

    /**
     * @return string
     */
    public static function getIdentifier();

    public function getDefaultName();

    public function getConfigValidation();
    public function getPublicConfigKeys();


    public function getDefaultGroup();

    public function isPassive();

    public function isEnabled(?Subject $subject);

    public function remembered();

    public function canAutoRedirect();

    /**
     * @return ModuleResult
     */
    public function getRedirectResponse(Request $request, State $state, ModuleInterface $module);

    public function shouldCreateUser(ModuleInterface $module);
}
