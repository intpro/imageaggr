<?php

namespace Interpro\ImageAggr\Creation;

use Interpro\Core\Contracts\Taxonomy\Fields\OwnField;
use Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet;
use Interpro\ImageAggr\Exception\ImageAggrException;
use Interpro\ImageAggr\Items\ImageItem;

class CapGenerator
{
    private $imageItemFactory;
    private $settingsSet;

    public function __construct(ImageSettingsSet $settingsSet)
    {
        $this->imageItemFactory = null;
        $this->settingsSet = $settingsSet;
    }

    public function setFactory(ImageItemFactory $imageItemFactory)
    {
        $this->imageItemFactory = $imageItemFactory;
    }

    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Fields\OwnField $field
     * @param string $resize_name
     * @param \Interpro\ImageAggr\Items\ImageItem $original
     *
     * @return \Interpro\ImageAggr\Items\ResizeItem
     */
    public function createResize(OwnField $field, $resize_name, ImageItem $original)
    {
        if(!$this->imageItemFactory)
        {
            throw new ImageAggrException('Для генератора заглушек не установлена фабрика элементов!');
        }

        $type = $field->getFieldType();
        $subs = $type->getSubs('original');
        $resizeType = $subs->getSub('resize');

        $resizeItem  = $this->imageItemFactory->createResize($resizeType, ['name' => $resize_name], true);

        $resizeItem->getRef('original')->setItem($original);

        return $resizeItem;
    }

    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Fields\OwnField $field
     * @param string $crop_name
     * @param \Interpro\ImageAggr\Items\ImageItem $original
     *
     * @return \Interpro\ImageAggr\Items\CropItem
     */
    public function createCrop(OwnField $field, $crop_name, ImageItem $original)
    {
        if(!$this->imageItemFactory)
        {
            throw new ImageAggrException('Для генератора заглушек не установлена фабрика элементов!');
        }

        $type = $field->getFieldType();
        $subs = $type->getSubs('original');
        $cropType = $subs->getSub('crop');

        $cropItem  = $this->imageItemFactory->createCrop($cropType, ['name' => $crop_name], true);

        $cropSetting = $this->settingsSet->getImage($field)->getCrop($crop_name);

        $cropItem->getRef('original')->setItem($original);

        $man_name = $cropSetting->getMan()->getName();
        $target_name = $cropSetting->getTarget()->getName();

        $cropItem->getRef('man')->setItem($original->getResize($man_name));
        $cropItem->getRef('target')->setItem($original->getResize($target_name));

        return $cropItem;
    }

    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Fields\OwnField $field
     *
     * @return \Interpro\ImageAggr\Items\ImageItem
     */
    public function createImage(OwnField $field)
    {
        if(!$this->imageItemFactory)
        {
            throw new ImageAggrException('Для генератора заглушек не установлена фабрика элементов!');
        }

        $type = $field->getFieldType();

        $subs = $type->getSubs('original');
        $resizeType = $subs->getSub('resize');
        $cropType = $subs->getSub('crop');

        $name = $field->getName();

        $item  = $this->imageItemFactory->createImage($field, ['name' => $name], true);

        //Ресайзы
        $resizesSet = $this->settingsSet->getImage($field)->getResizes();

        foreach($resizesSet as $resize_name => $resizeSetting)
        {
            $resizeItem  = $this->imageItemFactory->createResize($resizeType, ['name' => $resize_name], true);

            $resizeItem->getRef('original')->setItem($item);

            $item->addResize($resizeItem);
        }

        //Кропы
        $cropsSet = $this->settingsSet->getImage($field)->getCrops();

        foreach($cropsSet as $crop_name => $cropSetting)
        {
            $cropItem  = $this->imageItemFactory->createCrop($cropType, ['name' => $crop_name], true);

            $cropItem->getRef('original')->setItem($item);

            $man_name = $cropSetting->getMan()->getName();
            $target_name = $cropSetting->getTarget()->getName();

            $cropItem->getRef('man')->setItem($item->getResize($man_name));
            $cropItem->getRef('target')->setItem($item->getResize($target_name));

            $item->addCrop($cropItem);
        }

        return $item;
    }

}
