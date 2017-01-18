<?php

namespace Interpro\ImageAggr\Collections;

use Interpro\Core\Contracts\Taxonomy\Fields\OwnField;
use Interpro\Core\Taxonomy\Collections\NamedCollection;
use Interpro\ImageAggr\Creation\CapGenerator;
use Interpro\ImageAggr\Exception\ImageAggrException;
use Interpro\ImageAggr\Items\ImageItem;
use Interpro\ImageAggr\Items\ResizeItem;

class ResizesCollection extends NamedCollection
{
    private $capGenerator;
    private $imageField;
    private $original = null;

    public function __construct(OwnField $field, CapGenerator $capGenerator)
    {
        $this->capGenerator = $capGenerator;
        $this->imageField = $field;
    }

    public function setOriginal(ImageItem $original)
    {
        $this->original = $original;
    }

    /**
     * @param string $resize_name
     *
     * @return \Interpro\ImageAggr\Items\ResizeItem
     */
    public function getItem($resize_name)
    {
        if($this->exist($resize_name))
        {
            return $this->getByName($resize_name);
        }

        if($this->original)
        {
            $resizeItem = $this->capGenerator->createResize($this->imageField, $resize_name, $this->original);

            $this->addItem($resizeItem);
        }

        return $this->getByName($resize_name);
    }

    protected function notFoundAction($name)
    {
        throw new ImageAggrException('Ресайз по имени '.$name.' не найден!');
    }

    /**
     * @param \Interpro\ImageAggr\Items\ResizeItem $item
     *
     * @return void
     */
    public function addItem(ResizeItem $item)
    {
        $this->addByName($item);
    }

}
