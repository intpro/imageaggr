<?php

namespace Interpro\ImageAggr\Settings;

use Interpro\ImageAggr\Contracts\Settings\Collection\ModSet as ModSetInterface;
use Interpro\ImageAggr\Contracts\Settings\Enum\GenVariant;
use Interpro\ImageAggr\Contracts\Settings\ResizeSetting as ResizeSettingInterface;

class ResizeSetting extends GenerationSetting implements ResizeSettingInterface
{
    private $when_upload;

    public function __construct($name, $width, $height, ModSetInterface $modSet, $when_upload = false)
    {
        parent::__construct($name, $width, $height, $modSet);

        $this->variant = GenVariant::RESIZE;
        $this->when_upload = $when_upload;
    }

    /**
     * @return bool
     */
    public function whenUpload()
    {
        return $this->when_upload;
    }
}
