<?php

namespace Interpro\ImageAggr\Settings;

use Interpro\ImageAggr\Contracts\Settings\Collection\CropSettingsSet as CropSettingsSetInterface;
use Interpro\ImageAggr\Contracts\Settings\Collection\ResizeSettingsSet as ResizeSettingsSetInterface;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting as ImageSettingInterface;

class ImageSetting implements ImageSettingInterface
{
    protected $entity_name;
    protected $name;
    protected $resizes;
    protected $crops;
    protected $color;
    protected $width;
    protected $height;

    public function __construct($entity_name, $name, ResizeSettingsSetInterface $resizeSettingsSet, CropSettingsSetInterface $cropSettingsSet, $color = '#808080', $width = 400, $height = 400)
    {
        $this->entity_name = $entity_name;
        $this->name        = $name;
        $this->resizes     = $resizeSettingsSet;
        $this->crops       = $cropSettingsSet;
        $this->color       = $color;
        $this->width       = $width;
        $this->height      = $height;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entity_name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\Collection\ResizeSettingsSet
     */
    public function getResizes()
    {
        return $this->resizes;
    }

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\Collection\CropSettingsSet
     */
    public function getCrops()
    {
        return $this->crops;
    }

    /**
     * @param string $resize_name
     *
     * @return \Interpro\ImageAggrTypes\Concept\Settings\ResizeSetting
     */
    public function getResize($resize_name)
    {
        return $this->resizes->getResize($resize_name);
    }

    /**
     * @param string $crop_name
     *
     * @return \Interpro\ImageAggrTypes\Concept\Settings\CropSetting
     */
    public function getCrop($crop_name)
    {
        return $this->crops->getCrop($crop_name);
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

}
