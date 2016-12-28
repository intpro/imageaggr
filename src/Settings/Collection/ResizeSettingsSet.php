<?php

namespace Interpro\ImageAggr\Settings\Collection;

use Interpro\ImageAggr\Exception\ImageAggrException;
use Interpro\ImageAggr\Contracts\Settings\Collection\ResizeSettingsSet as ResizeSettingsSetInterface;

class ResizeSettingsSet implements ResizeSettingsSetInterface
{
    private $resizes;
    private $resize_names;
    private $position;

    /**
     * @param array $resizes
     */
    public function __construct(
        array $resizes
    ){
        $this->resizes      = $resizes;
        $this->resize_names = array_keys($resizes);
    }

    /**
     * @param string $resize_name
     * @return \Interpro\ImageAggr\Contracts\Settings\ResizeSetting
     */
    public function getResize($resize_name)
    {
        if(array_key_exists($resize_name, $this->resizes))
        {
            return $this->resizes[$resize_name];
        }
        else
        {
            throw new ImageAggrException('Ресайз по имени: '.$resize_name.' не найден в настройках!');
        }
    }

    function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return \Interpro\ImageAggr\Settings\ResizeSetting
     */
    function current()
    {
        $name = $this->resize_names[$this->position];
        return $this->resizes[$name];
    }

    function key()
    {
        return $this->resize_names[$this->position];
    }

    function next()
    {
        ++$this->position;
    }

    function valid()
    {
        return isset($this->resize_names[$this->position]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->resize_names);
    }
}
