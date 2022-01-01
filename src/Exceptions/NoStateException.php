<?php

namespace ArieTimmerman\Laravel\AuthChain\Exceptions;

use ArieTimmerman\Laravel\AuthChain\Helper;
use Exception;

class NoStateException extends Exception
{
    protected $state;
    protected $module;

    /*
        implement a toJSOn function
    */
    public function render($request)
    {
        if ($request->wantsJson()) {
            return response(
                [
                'errors' => [
                    $this->getMessage()
                ]
                ]
            )->setStatusCode(404);
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
