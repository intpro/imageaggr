<?php

namespace Interpro\ImageAggr\Contracts\Settings\Collection;

use Interpro\Core\Contracts\Collection;

interface ModSet extends Collection
{
    /**
     * @param string $mod_name
     * @return \Interpro\ImageAggr\Contracts\Settings\ModSetting
     */
    public function getMod($mod_name);
}
