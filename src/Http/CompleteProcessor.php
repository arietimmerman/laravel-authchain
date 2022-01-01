<?php

namespace ArieTimmerman\Laravel\AuthChain\Http;

use ArieTimmerman\Laravel\AuthChain\Http\CompleteProcessorInterface;
use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\Helper;
use ArieTimmerman\Laravel\AuthChain\State;

class CompleteProcessor implements CompleteProcessorInterface
{
    public function onFinish(Request $request, State $state, Authenticatable $subject)
    {
        Helper::deleteState($state);
        
        return redirect($state->onFinishUrl);
    }

    public function onCancel(Request $request, ?State $state)
    {
        return redirect($state->onCancelUrl);
    }
}
