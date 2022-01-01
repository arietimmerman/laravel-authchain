<?php
/**
 * An authentication chain is a connected set of authentication modules, structued in a directed graph.
 */

namespace ArieTimmerman\Laravel\AuthChain\Repository;

use ArieTimmerman\Laravel\AuthChain\Config\Config;
use ArieTimmerman\Laravel\AuthChain\Module\Module;
use ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface;
use ArieTimmerman\Laravel\AuthChain\Types\Type;

class ModuleRepository implements ModuleRepositoryInterface
{
    protected $modules = null;

    protected static $seen = false;

    public function __construct()
    {
        if (self::$seen) {
            debug_print_backtrace();
            exit;
        }
        self::$seen = true;
    }

    public function all()
    {
        if ($this->modules == null) {
            $moduleConfigs = Config::getInstance()->get('authchain.modules');

            $this->modules = [];

            foreach ($moduleConfigs as $config) {
                $this->modules[] = Module::withTypeAndConfig(new $config['type'], $config)->withConfig();
            }
        }

        return $this->modules;
    }

    /**
     * @return ModuleInterface
     */
    public function get($id)
    {
        $result = null;

        foreach ($this->getModules() as $module) {
            if ($module->getIdentifier() == $id) {
                $result = $module->withConfig();
            }
        }

        return $result;
    }


    public function info($id)
    {
        $result = null;

        foreach ($this->getModules() as $module) {
            if ($module->getIdentifier() == $id) {
                $result = $module;
            }
        }

        return $result->getInfo();
    }

    /**
     * @return ModuleInterface
     */
    public function add($name, Type $type)
    {
        throw new ApiException('Operation not supported');
    }

    public function delete(ModuleInterface $module)
    {
        throw new ApiException('Operation not supported');
    }

    public function save(ModuleInterface $module)
    {
        throw new ApiException('Operation not supported');
    }
}
