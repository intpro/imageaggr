<?php

namespace Interpro\ImageAggr\Contracts\Operation;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Settings\CropSetting;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting;

interface CropOperation
{
    /**
     * @param \Interpro\Core\Contracts\Ref\ARef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\CropSetting $cropSetting
     * @param array $attrs
     */
    public function execute(ARef $aRef, ImageSetting $imageSetting, CropSetting $cropSetting, array $attrs = []);
}
