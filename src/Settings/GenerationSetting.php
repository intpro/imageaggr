<?php

namespace Interpro\ImageAggr\Settings;

use Interpro\ImageAggr\Contracts\Settings\Collection\ModSet as ModSetInterface;
use Interpro\ImageAggr\Contracts\Settings\GenerationSetting as GenerationSettingInterface;

abstract class GenerationSetting implements GenerationSettingInterface
{
    protected $name;
    protected $width;
    protected $height;
    protected $mods;
    protected $variant;

    public function __construct($name, $width, $height, ModSetInterface $modSet, $color = '#808080')
    {
        $this->name   = $name;
        $this->width  = $width;
        $this->height = $height;
        $this->mods   = $modSet;
        $this->color  = $color;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\Collection\ModSet
     */
    public function getMods()
    {
        return $this->mods;
    }

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\ModSetting
     */
    public function getMod($mod_name)
    {
        return $this->mods->getMod($mod_name);
    }

    /**
     * @return string
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * @return int
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $req_name
     *
     * @return mixed
     */
    public function __get($req_name)
    {
        if($req_name === 'name')
        {
            return $this->name;
        }
        elseif($req_name === 'width')
        {
            return $this->width;
        }
        elseif($req_name === 'height')
        {
            return $this->height;
        }
        elseif($req_name === 'variant')
        {
            return $this->variant;
        }
        elseif($req_name === 'color')
        {
            return $this->color;
        }
        else
        {
            return null;
        }
    }

}
