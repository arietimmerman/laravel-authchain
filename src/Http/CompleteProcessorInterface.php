<?php

namespace ArieTimmerman\Laravel\AuthChain\Http;

use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\State;
use Illuminate\Contracts\Auth\Authenticatable;

interface CompleteProcessorInterface
{
    public function onFinish(Request $request, State $state, Authenticatable $subject);

    public function onCancel(Request $request, ?State $state);
}
