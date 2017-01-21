<?php

namespace Interpro\ImageAggr\Settings\Collection;

use Interpro\Core\Contracts\Taxonomy\Fields\OwnField;
use Interpro\Core\Iterator\FieldIterator;
use Interpro\ImageAggr\Exception\ImageAggrException;
use Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet as ImageSettingsSetInterface;

class ImageSettingsSet implements ImageSettingsSetInterface
{
    private $images;
    private $image_names;
    private $position;

    /**
     * @param array $images
     */
    public function __construct(
        array $images
    ){
        $this->images      = $images;
        $this->image_names = array_keys($images);
    }

    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Fields\OwnField $field
     *
     * @return \Interpro\ImageAggr\Contracts\Settings\ImageSetting
     */
    public function getImage(OwnField $field)
    {
        $key = $field->getOwnerTypeName().'.'.$field->getName();

        if(array_key_exists($key, $this->images))
        {
            return $this->images[$key];
        }
        else
        {
            throw new ImageAggrException('Картинка по имени: '.$key.' не найдена в коллекции!');
        }
    }

    function rewind()
    {
        $this->position = 0;
    }

    function current()
    {
        $name = $this->image_names[$this->position];
        return $this->images[$name];
    }

    function key()
    {
        return $this->image_names[$this->position];
    }

    function next()
    {
        ++$this->position;
    }

    function valid()
    {
        return isset($this->image_names[$this->position]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->image_names);
    }

    public function sortBy($path, $sort = 'ASC')
    {
        return new FieldIterator($this, $path, $sort);
    }

}
