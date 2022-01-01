<?php
/**
 * A module is a configured type.
 */

namespace ArieTimmerman\Laravel\AuthChain\Module;

use Illuminate\Http\Request;
use ArieTimmerman\Laravel\AuthChain\Types\Type;
use ArieTimmerman\Laravel\AuthChain\State;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Log;

class ModuleList implements \JsonSerializable, \ArrayAccess, \Countable
{
    public $modules = [];

    public function __construct(array $modules = [])
    {
        $this->modules = $modules;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->modules[] = $value;
        } else {
            $this->modules[$offset] = $value;
        }
    }
    
    public function offsetExists($offset)
    {
        return isset($this->modules[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->modules[$offset]);
    }

    /**
     * @return Module
     */
    public function offsetGet($offset)
    {
        return isset($this->modules[$offset]) ? $this->modules[$offset] : null;
    }

    public function init(Request $request, State $state)
    {
        foreach ($this->modules as $module) {
            /**
 * @var ModuleInterface $module 
*/
            $module->init($request, $state);
        }

        return $this;
    }

    /**
     * Whether none of the modules is required
     */
    public function maySkipAll()
    {
        $allSkippable = true;
        /**
         *
         */
        foreach ($this->modules as $module) {
            /* @var $module \ArieTimmerman\Laravel\AuthChain\Module\ModuleInterface */
            
            if ($module->enabled && !$module->skippable) {
                Log::debug('Module: ' . $module->getIdentifier() . ' is not skippable');
                Log::debug('Remembered: ' . ($module->remembered()?'true':'false'));
                Log::debug('Enabled: ' . ($module->enabled?'true':'false'));
                Log::debug('isSkippable: ' . ($module->skippable?'true':'false'));
                
                $allSkippable = false;
            }
        }

        Log::debug('maySkipAll: ' . ($allSkippable?'true':'false'));
        
        return $allSkippable;
    }

    /**
     * @return bool
     */
    public function contains(ModuleInterface $element)
    {
        $has = false;

        foreach ($this->modules as $module) {
            if ($element->getIdentifier() == $module->getIdentifier()) {
                $has = true;
                break;
            }
        }
        
        return $has;
    }

    public function jsonSerialize()
    {
        // ensure return as a non-associative array
        return array_values($this->modules);
    }

    public function __toString()
    {
        return json_encode(self::jsonSerialize());
    }

    public function getIdentifierList()
    {
        $identifiers = [];

        foreach ($this->modules as $module) {
            /**
 * @var $module ModuleInterface 
*/
            $identifiers[] = $module->getIdentifier();
        }

        return $identifiers;
    }

    public function sort()
    {
        /**
         * Sort by priority
         */
        uasort(
            $this->modules, function ($a, $b) {
                return $a->isHigher($b) ? 1 : -1;
            }
        );
    }


    /**
     * Get the value of modules
     */
    public function getModules()
    {
        return $this->modules;
    }

    public function isEmpty()
    {
        return empty($this->modules);
    }

    public function count()
    {
        return count($this->modules);
    }
}
