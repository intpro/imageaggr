<?php

namespace Interpro\ImageAggr\Fields;

use Interpro\Extractor\Contracts\Fields\RefField as RefFieldInterface;
use Interpro\Core\Contracts\Taxonomy\Fields\RefField as RefFieldMeta;
use Interpro\ImageAggr\Items\CropItem;
use Interpro\ImageAggr\Items\ResizeItem;

class ResizeRefField implements RefFieldInterface
{
    private $field;
    private $name;
    private $owner;
    private $item = null;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Interpro\ImageAggr\Items\CropItem
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param \Interpro\ImageAggr\Items\CropItem $owner
     * @param \Interpro\Core\Contracts\Taxonomy\Fields\RefField $field
     *
     * @return void
     */
    public function __construct(CropItem $owner, RefFieldMeta $field)
    {
        $this->name  = $field->getName();
        $this->owner = $owner;
        $this->field = $field;
    }

    /**
     * @return \Interpro\ImageAggr\Items\ResizeItem
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return \Interpro\Core\Contracts\Taxonomy\Fields\RefField
     */
    public function getFieldMeta()
    {
        return $this->field;
    }

    /**
     * @param \Interpro\ImageAggr\Items\ResizeItem
     *
     * @return void
     */
    public function setItem(ResizeItem $resizeItem)
    {
        $this->item = $resizeItem;
    }
}
