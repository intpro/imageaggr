<?php

namespace Interpro\ImageAggr\Contracts\Settings;

interface GenSettingsSetFactory
{
    /**
     * @param array $resize_names
     *
     * @return \Interpro\ImageAggr\Contracts\Settings\Collection\ResizeSettingsSet
     */
    public function createResizeSettingsSet($resize_names = []);

    /**
     * @param array $crop_names
     *
     * @return \Interpro\ImageAggr\Contracts\Settings\Collection\CropSettingsSet
     */
    public function createCropSettingsSet($crop_names = []);
}
