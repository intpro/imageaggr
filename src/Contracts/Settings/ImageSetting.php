<?php

namespace Interpro\ImageAggr\Contracts\Settings;

interface ImageSetting
{
    /**
     * @return string
     */
    public function getEntityName();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\Collection\ResizeSettingsSet
     */
    public function getResizes();

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\Collection\CropSettingsSet
     */
    public function getCrops();

    /**
     * @param string $resize_name
     *
     * @return \Interpro\ImageAggr\Contracts\Settings\ResizeSetting
     */
    public function getResize($resize_name);

    /**
     * @param string $crop_name
     *
     * @return \Interpro\ImageAggr\Contracts\Settings\CropSetting
     */
    public function getCrop($crop_name);

    /**
     * @return bool
     */
    public function resizeExist($resize_name);

    /**
     * @return bool
     */
    public function cropExist($crop_name);

    /**
     * @return string
     */
    public function getColor();

    /**
     * @return int
     */
    public function getWidth();

    /**
     * @return int
     */
    public function getHeight();

}
