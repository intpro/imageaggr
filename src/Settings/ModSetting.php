<?php

namespace Interpro\ImageAggr\Settings;

use Interpro\Core\Contracts\Taxonomy\Fields\Field;
use Interpro\ImageAggr\Contracts\Settings\GenerationSetting as GenerationSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\ModSetting as ModSettingInterface;

class ModSetting implements ModSettingInterface
{
    protected $modVar;
    protected $image;
    protected $position;
    protected $x;
    protected $y;

    /**
     * @param GenerationSetting $gen
     * @param string $modVar
     * @param string $position
     * @param int $x
     * @param int $y
     */
    public function __construct(GenerationSettingInterface $gen, Field $image, $modVar, $position, $x = 0, $y = 0)
    {
        $this->gen    = $gen;
        $this->image  = $image;
        $this->modVar = $modVar;
        $this->position = $position;
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * @return \Interpro\Core\Contracts\Taxonomy\Fields\Field
     */
    public function getImageField()
    {
        return $this->image;
    }

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\GenerationSetting
     */
    public function getGen()
    {
        return $this->gen;
    }

    /**
     * @return \Interpro\ImageAggr\Contracts\Settings\Enum\ModVariant
     */
    public function getModVariant()
    {
        return $this->modVar;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param string $req_name
     *
     * @return mixed
     */
    public function __get($req_name)
    {
        if($req_name === 'gen')
        {
            return $this->gen;
        }
        elseif($req_name === 'image')
        {
            return $this->image;
        }
        elseif($req_name === 'modvar')
        {
            return $this->modVar;
        }
        elseif($req_name === 'position')
        {
            return $this->position;
        }
        elseif($req_name === 'x')
        {
            return $this->x;
        }
        elseif($req_name === 'y')
        {
            return $this->y;
        }
        else
        {
            return null;
        }
    }

}
