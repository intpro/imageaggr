<?php

namespace Interpro\ImageAggr\Settings;

use Interpro\ImageAggr\Contracts\Settings\Collection\ModSet as ModSetInterface;
use Interpro\ImageAggr\Contracts\Settings\Enum\GenVariant;
use Interpro\ImageAggr\Contracts\Settings\ResizeSetting as ResizeSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\CropSetting as CropSettingInterface;

class CropSetting extends GenerationSetting implements CropSettingInterface
{
    protected $man;
    protected $target;
    protected $x = 0;
    protected $y = 0;

    public function __construct($name, $width, $height, ResizeSettingInterface $man, ResizeSettingInterface $target, ModSetInterface $modSet)
    {
        parent::__construct($name, $width, $height, $modSet);

        $this->man    = $man;
        $this->target = $target;
        $this->variant = GenVariant::CROP;
    }

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\ResizeSetting
     */
    public function getMan()
    {
        return $this->man;
    }

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\ResizeSetting
     */
    public function getTarget()
    {
        return $this->target;
    }

}
