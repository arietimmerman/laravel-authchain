<?php

namespace ArieTimmerman\Laravel\AuthChain\Types;

use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\State;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;

class NullType extends AbstractType
{
    public function process(Request $request, State $state, ModuleInterface $module)
    {
        return null;
    }
}
