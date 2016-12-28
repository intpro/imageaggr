<?php

namespace Interpro\ImageAggr\Contracts\Operation;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting;

interface SaveOperation
{
    /**
     * @param \Interpro\Core\Contracts\Ref\ARef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param array $user_attrs
     */
    public function execute(ARef $aRef, ImageSetting $imageSetting, array $user_attrs = []);
}
