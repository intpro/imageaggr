<?php

namespace Interpro\ImageAggr\Fields;

use Interpro\Extractor\Contracts\Fields\RefField as RefFieldInterface;
use Interpro\Core\Contracts\Taxonomy\Fields\RefField as RefFieldMeta;
use Interpro\ImageAggr\Items\ImageAggrItem;
use Interpro\ImageAggr\Items\ImageItem;

class OriginalRefField implements RefFieldInterface
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
     * @return \Interpro\ImageAggr\Items\ImageItem
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param \Interpro\ImageAggr\Items\ImageAggrItem $owner
     * @param \Interpro\Core\Contracts\Taxonomy\Fields\RefField $field
     *
     * @return void
     */
    public function __construct(ImageAggrItem $owner, RefFieldMeta $field)
    {
        $this->name  = $field->getName();
        $this->owner = $owner;
        $this->field = $field;
    }

    /**
     * @param \Interpro\ImageAggr\Items\ImageItem $image
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
     * @param \Interpro\ImageAggr\Items\ImageItem
     *
     * @return void
     */
    public function setItem(ImageItem $imageItem)
    {
        $this->item = $imageItem;
    }
}
