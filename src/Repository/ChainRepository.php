<?php
/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace ArieTimmerman\Laravel\AuthChain\Repository;

use ArieTimmerman\Laravel\AuthChain\Config\Config;
use ArieTimmerman\Laravel\AuthChain\Module\Module;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;
use ArieTimmerman\Laravel\AuthChain\Types\Type;
use ArieTimmerman\Laravel\AuthChain\Module\Chain;
use ArieTimmerman\Laravel\AuthChain\Module\ChainInterface;
use ArieTimmerman\Laravel\AuthChain\Exceptions\ApiException;

class ChainRepository implements ChainRepositoryInterface
{

    /**
     * @return ChainInterface[]
     */
    public function all()
    {
        $chain = Config::getInstance()->get('authchain.chain');

        foreach ($chain as $c) {
            yield new Chain($c['from'], $c['to']);
        }
    }

    public function get($id)
    {
        throw ApiException('Operation not supported');
    }

    public function add($from, $to)
    {
        throw ApiException('Operation not supported');
    }

    /**
     *
     */
    public function delete(ChainInterface $chain)
    {
        throw ApiException('Operation not supported');
    }
}
