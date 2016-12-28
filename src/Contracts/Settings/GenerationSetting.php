<?php

namespace Interpro\ImageAggr\Contracts\Settings;

interface GenerationSetting
{
    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\Collection\ModSet
     */
    public function getMods();

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\ModSetting
     */
    public function getMod($mod_name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return int
     */
    public function getWidth();

    /**
     * @return int
     */
    public function getHeight();

    /**
     * @return string
     */
    public function getVariant();

    /**
     * @return string
     */
    public function getColor();
}
