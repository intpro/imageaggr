<?php

namespace Interpro\ImageAggr\Collections;

use Interpro\Core\Contracts\Taxonomy\Fields\OwnField;
use Interpro\Core\Taxonomy\Collections\NamedCollection;
use Interpro\ImageAggr\Creation\CapGenerator;
use Interpro\ImageAggr\Exception\ImageAggrException;
use Interpro\ImageAggr\Items\CropItem;
use Interpro\ImageAggr\Items\ImageItem;

class CropsCollection extends NamedCollection
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
     * @param string $field_name
     *
     * @return \Interpro\ImageAggr\Items\CropItem
     */
    public function getItem($crop_name)
    {
        if($this->exist($crop_name))
        {
            $this->getByName($crop_name);
        }

        if($this->original)
        {
            $resizeItem = $this->capGenerator->createResize($this->imageField, $crop_name, $this->original);

            $this->addItem($resizeItem);
        }

        return $this->getByName($crop_name);
    }

    protected function notFoundAction($name)
    {
        throw new ImageAggrException('Кроп по имени '.$name.' не найден!');
    }

    /**
     * @param \Interpro\ImageAggr\Items\CropItem $item
     *
     * @return void
     */
    public function addItem(CropItem $item)
    {
        $this->addByName($item);
    }

}
