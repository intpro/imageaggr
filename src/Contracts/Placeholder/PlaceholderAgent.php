<?php

namespace Interpro\ImageAggr\Contracts\Placeholder;

use Interpro\ImageAggr\Contracts\Settings\CropSetting as CropSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\GenerationSetting as GenerationSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\ResizeSetting as ResizeSettingInterface;

interface PlaceholderAgent
{
    /**
     * @param int $width
     * @param int $height
     * @param string $color
     * @return string
     */
    public function getPh($width, $height, $color = '#808080');

    /**
     * @param \Interpro\ImageAggr\Contracts\Settings\ResizeSetting $resize
     * @param string $color
     * @return string
     */
    public function getResizePh(ResizeSettingInterface $resize, $color = '#808080');

    /**
     * @param \Interpro\ImageAggr\Contracts\Settings\CropSetting $crop
     * @param string $color
     * @return string
     */
    public function getCropPh(CropSettingInterface $crop, $color = '#808080');

    /**
     * @param \Interpro\ImageAggr\Contracts\Settings\GenerationSetting $gen
     * @param string $gen_variant
     * @param string $color
     * @return string
     */
    public function getGenPh(GenerationSettingInterface $gen, $color = '#808080');
}
