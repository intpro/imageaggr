<?php

namespace Interpro\ImageAggr\Contracts\Settings\Collection;

use Interpro\Core\Contracts\Collection;

interface CropSettingsSet extends Collection
{
    /**
     * @param string $crop_name
     * @return \Interpro\ImageAggr\Contracts\Settings\CropSetting
     */
    public function getCrop($crop_name);
}
