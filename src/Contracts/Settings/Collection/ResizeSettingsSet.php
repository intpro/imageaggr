<?php

namespace Interpro\ImageAggr\Contracts\Settings\Collection;

use Interpro\Core\Contracts\Collection;

interface ResizeSettingsSet extends Collection
{
    /**
     * @param string $resize_name
     * @return \Interpro\ImageAggr\Contracts\Settings\ResizeSetting
     */
    public function getResize($resize_name);

    /**
     * @return bool
     */
    public function exist($resize_name);
}
