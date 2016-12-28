<?php

namespace Interpro\ImageAggr\Contracts\Operation;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting;

interface SameOperation
{
    /**
     * @param \Interpro\Core\Contracts\Ref\ARef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     */
    public function execute(ARef $aRef, ImageSetting $imageSetting);
}
