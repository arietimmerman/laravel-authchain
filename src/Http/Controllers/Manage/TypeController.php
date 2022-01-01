<?php

namespace ArieTimmerman\Laravel\AuthChain\Http\Controllers\Manage;

use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\Http\Controllers\Controller;
use ArieTimmerman\Laravel\AuthChain\AuthChain;

class TypeController extends Controller
{

    /**
     * Used to return authentication module types a user can add.
     *
     * The special modules 'start' and 'consent' cannot be managed.
     */
    public function index()
    {
        $result = []; //array_diff(array_keys(AuthChain::$typeMap),['consent','start']);

        foreach (AuthChain::$typeMap as $key=>$value) {
            if (!in_array($key, ['consent','start'])) {
                $object = new $value;
                $result[$key] = $object->getDefaultName();
            }
        }

        return $result;
    }
}
