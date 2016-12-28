<?php

namespace Interpro\ImageAggr\Contracts\Settings;

interface ImageSettingsSetFactory
{
    /**
     * @param $owner_name
     *
     * @return \Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet
     */
    public function create($owner_name = 'all');
}
