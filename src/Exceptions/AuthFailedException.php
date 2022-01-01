<?php

namespace ArieTimmerman\Laravel\AuthChain\Exceptions;

use ArieTimmerman\Laravel\AuthChain\Helper;
use Exception;

class AuthFailedException extends Exception
{
    protected $state;
    protected $module;

    /*
        implement a toJSOn function
    */
    public function render($request)
    {
        //return \response($this->message);
        
        /**
         * TODO: Passive modules should never fail ...
         */
        $successors = $this->state ? Helper::getModulesForState($this->state) : [];

        if ($request->wantsJson()) {
            return response(
                [
                'errors' => [
                    $this->getMessage()
                ]
                ]
            )->setStatusCode(500);
        } else {
            return view(
                'authchain::error', [
                'exception' => $this
                ]
            );
        }
    }

    /**
     * Set the value of state
     *
     * @return self
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }
}
