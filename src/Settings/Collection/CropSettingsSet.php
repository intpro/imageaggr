<?php

namespace Interpro\ImageAggr\Settings\Collection;

use Interpro\Core\Enum\OddEven;
use Interpro\Core\Iterator\FieldIterator;
use Interpro\Core\Iterator\OddEvenIterator;
use Interpro\ImageAggr\Exception\ImageAggrException;
use Interpro\ImageAggr\Contracts\Settings\Collection\CropSettingsSet as CropSettingsSetInterface;

class CropSettingsSet implements CropSettingsSetInterface
{
    private $crops;
    private $crop_names;
    private $position;

    /**
     * @param array $crops
     */
    public function __construct(
        array $crops
    ){
        $this->crops      = $crops;
        $this->crop_names = array_keys($crops);
    }

    /**
     * @param string $crop_name
     * @return \Interpro\ImageAggr\Contracts\Settings\CropSetting
     */
    public function getCrop($crop_name)
    {
        if(array_key_exists($crop_name, $this->crops))
        {
            return $this->crops[$crop_name];
        }
        else
        {
            throw new ImageAggrException('Кроп по имени: '.$crop_name.' не найден в настройках!');
        }
    }

    function rewind()
    {
        $this->position = 0;
    }

    function current()
    {
        $name = $this->crop_names[$this->position];
        return $this->crops[$name];
    }

    function key()
    {
        return $this->crop_names[$this->position];
    }

    function next()
    {
        ++$this->position;
    }

    function valid()
    {
        return isset($this->crop_names[$this->position]);
    }

    /**
     * @return bool
     */
    public function exist($crop_name)
    {
        return array_key_exists($crop_name, $this->crops);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->crop_names);
    }

    public function sortBy($path, $sort = 'ASC')
    {
        return new FieldIterator($this, $path, $sort);
    }

    public function odd()
    {
        return new OddEvenIterator($this->crops, OddEven::ODD);
    }

    public function even()
    {
        return new OddEvenIterator($this->crops, OddEven::EVEN);
    }
}
