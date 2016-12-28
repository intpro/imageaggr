<?php

namespace Interpro\ImageAggr\Contracts\Settings\Collection;

use Interpro\Core\Contracts\Collection;
use Interpro\Core\Contracts\Taxonomy\Fields\OwnField;

interface ImageSettingsSet extends Collection
{
    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Fields\OwnField $field
     *
     * @return \Interpro\ImageAggr\Contracts\Settings\ImageSetting
     */
    public function getImage(OwnField $field);
}
