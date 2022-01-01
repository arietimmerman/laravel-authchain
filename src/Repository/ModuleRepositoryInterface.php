<?php
/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace ArieTimmerman\Laravel\AuthChain\Repository;

use ArieTimmerman\Laravel\AuthChain\Config\Config;
use ArieTimmerman\Laravel\AuthChain\Module\Module;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;

use ArieTimmerman\Laravel\AuthChain\Types\Type;

interface ModuleRepositoryInterface
{
    public function all();

    /**
     * @return ModuleInterface
     */
    public function get($id);

    public function info($id);

    /**
     * @return ModuleInterface
     */
    public function add($name, Type $type);

    public function delete(ModuleInterface $module);

    public function save(ModuleInterface $module);
}
