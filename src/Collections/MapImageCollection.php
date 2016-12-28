<?php

namespace Interpro\ImageAggr\Collections;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\Extractor\Contracts\Collections\MapBCollection;
use Interpro\Extractor\Contracts\Items\AggrOwnItem;
use Interpro\ImageAggr\Creation\CapGenerator;
use Interpro\ImageAggr\Exception\ImageAggrException;

class MapImageCollection implements MapBCollection
{
    private $items = [];
    private $capGenerator;

    public function __construct(CapGenerator $capGenerator)
    {
        $this->capGenerator = $capGenerator;
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return 'imageaggr';
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     * @param string $field_name
     *
     * @return \Interpro\Extractor\Contracts\Items\AggrOwnItem
     */
    public function getItem(ARef $ref, $field_name)
    {
        $ownerType = $ref->getType();

        if(!$ownerType->fieldExist($field_name))
        {
            throw new ImageAggrException('Обращение к несуществующей картинке '.$field_name.' в хозяине типа '.$ownerType->getName().'!');
        }

        $ownField = $ownerType->getField($field_name);

        if($ownField->getFieldTypeName() !== 'image')
        {
            throw new ImageAggrException('Обращение к несуществующей картинке '.$field_name.' в хозяине типа '.$ownerType->getName().'!');
        }

        $type_name = $ownerType->getName();
        $key = $field_name.'_'.$ref->getId();

        if(!array_key_exists($type_name, $this->items))
        {
            $this->items[$type_name] = [];
        }

        if(!array_key_exists($key, $this->items[$type_name]))
        {
            $item = $this->capGenerator->createImage($ownField);

            $this->items[$type_name][$key] = $item;
        }

        return $this->items[$type_name][$key];
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     * @param string $field_name
     * @param \Interpro\Extractor\Contracts\Items\AggrOwnItem $item
     *
     * @return void
     */
    public function addItem(ARef $ref, $field_name, AggrOwnItem $item)
    {
        $ownerType = $ref->getType();
        $type_name = $ownerType->getName();
        $key = $field_name.'_'.$ref->getId();

        if(!array_key_exists($type_name, $this->items))
        {
            $this->items[$type_name] = [];
        }

        $this->items[$type_name][$key] = $item;
    }

}
