<?php

namespace Interpro\ImageAggr\Contracts\Settings;

interface CropSetting extends GenerationSetting
{
    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\ResizeSetting
     */
    public function getMan();

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\ResizeSetting
     */
    public function getTarget();
}
