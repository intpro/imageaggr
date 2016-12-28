<?php

namespace Interpro\ImageAggr\Operation;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Operation\CropOperation as CropOperationInterface;
use Interpro\ImageAggr\Contracts\Settings\CropSetting;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting;

class CropOperation extends Operation implements CropOperationInterface
{
    /**
     * @param \Interpro\Core\Contracts\Ref\ARef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\CropSetting $cropSetting
     *
     * @return void
     */
    public function execute(ARef $aRef, ImageSetting $imageSetting, CropSetting $cropSetting, array $attrs = [])
    {
        $this->checkOwner($aRef);

        $this->makeCrop($aRef, $imageSetting, $cropSetting, $attrs);
    }
}
