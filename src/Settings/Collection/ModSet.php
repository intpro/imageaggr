<?php

namespace Interpro\ImageAggr\Settings\Collection;

use Interpro\ImageAggr\Exception\ImageAggrException;
use Interpro\ImageAggr\Contracts\Settings\Collection\ModSet as ModSetInterface;

class ModSet implements ModSetInterface
{
    private $mods;
    private $mod_names;
    private $position;

    /**
     * @param array $mods
     */
    public function __construct(
        array $mods
    ){
        $this->mods      = $mods;
        $this->mod_names = array_keys($mods);
    }

    /**
     * @param string $mod_name
     * @return \Interpro\ImageAggr\Contracts\Settings\ModSetting
     */
    public function getMod($mod_name)
    {
        if(array_key_exists($mod_name, $this->mods))
        {
            return $this->mods[$mod_name];
        }
        else
        {
            throw new ImageAggrException('Модификатор по имени: '.$mod_name.' не найден в коллекции!');
        }
    }

    function rewind()
    {
        $this->position = 0;
    }

    function current()
    {
        $name = $this->mod_names[$this->position];
        return $this->mods[$name];
    }

    function key()
    {
        return $this->mod_names[$this->position];
    }

    function next()
    {
        ++$this->position;
    }

    function valid()
    {
        return isset($this->mod_names[$this->position]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->mod_names);
    }

}
