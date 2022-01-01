<?php

namespace ArieTimmerman\Laravel\AuthChain\Module;

use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\Types\Type;
use ArieTimmerman\Laravel\AuthChain\State;
use ArieTimmerman\Laravel\AuthChain\AuthLevel;
use Illuminate\Foundation\Console\PackageDiscoverCommand;
use ArieTimmerman\Laravel\AuthChain\Exceptions\AuthFailedException;

interface ModuleInterface
{
    public function getIdentifier();

    /**
     * @return Type
     */
    public function getTypeObject();
    
    public function init(Request $request, State $state);

    /**
     * @return AuthLevel[]
     */
    public function getLevels();

    /**
     * @param $levels AuthLevel[]
     */
    public function setLevels(array $levels);

    public function syncLevels(array $levels);

    //optional
    public function remembered();

    //optional
    public function isPassive();

    /**
     * Configuration information 
     */
    public function getInfo();

    /**
     * @return ModuleResult
     */
    public function baseResult();
    
    /**
     * @return ModuleResult
     */
    public function process(Request $request, State $state);
}
