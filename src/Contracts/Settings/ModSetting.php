<?php

namespace Interpro\ImageAggr\Contracts\Settings;

interface ModSetting
{
    /**
     * @return \Interpro\Core\Contracts\Taxonomy\Fields\Field
     */
    public function getImageField();

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\GenerationSetting
     */
    public function getGen();

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\Enum\ModVariant
     */
    public function getModVariant();

    /**
     * @return string
     */
    public function getPosition();

    /**
     * @return int
     */
    public function getX();

    /**
     * @return int
     */
    public function getY();
}
