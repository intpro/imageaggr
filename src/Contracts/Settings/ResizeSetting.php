<?php

namespace Interpro\ImageAggr\Contracts\Settings;

interface ResizeSetting extends GenerationSetting
{
    /**
     * @return bool
     */
    public function whenUpload();
}
