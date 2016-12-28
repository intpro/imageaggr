<?php

namespace Interpro\ImageAggr\Db;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Settings\CropSetting as CropSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting as ImageSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\ResizeSetting as ResizeSettingInterface;
use Interpro\ImageAggr\Contracts\Db\ImageAggrDbAgent as ImageAggrDbAgentInterface;

class TestImageAggrDbAgent implements ImageAggrDbAgentInterface
{
    private $images = [];
    private $resizes = [];
    private $crops = [];

    public function setImages($images = [])
    {
        $this->images = $images;
    }

    public function setResizes($resizes = [])
    {
        $this->resizes = $resizes;
    }

    public function setCrops($crops = [])
    {
        $this->crops = $crops;
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     *
     * @return array
     */
    public function getCropsCoordinates(ARef $aRef, ImageSettingInterface $imageSetting)
    {
        $owner_name = $aRef->getType()->getName();
        $image_name = $imageSetting->getName();
        $owner_id = $aRef->getId();

        $crops = [];

        foreach($this->crops as $crop_array)
        {
            if($crop_array['entity_name'] === $owner_name and $crop_array['image_name'] === $image_name and $crop_array['entity_id'] === $owner_id)
            {
                $crops[$crop_array['name']] = ['x' => $crop_array['x'], 'y' => $crop_array['y']];
            }
        }

        return $crops;
    }

    /**
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     */
    public function ownerExist(ARef $aRef)
    {
        return true;
    }

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     *
     * @return bool
     */
    public function imageExist($owner_name, $owner_id, $image_name)
    {

        foreach($this->images as $image_array)
        {
            if($image_array['name'] === $image_name and $image_array['entity_name'] === $owner_name and $image_array['entity_id'] === $owner_id)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     * @param string $crop_name
     *
     * @return bool
     */
    public function cropExist($owner_name, $owner_id, $image_name, $crop_name)
    {
        foreach($this->crops as $crop_array)
        {
            if($crop_array['name'] === $crop_name and $crop_array['entity_name'] === $owner_name and $crop_array['image_name'] === $image_name and $crop_array['entity_id'] === $owner_id)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     * @param string $resize_name
     *
     * @return bool
     */
    public function resizeExist($owner_name, $owner_id, $image_name, $resize_name)
    {
        foreach($this->resizes as $res_array)
        {
            if($res_array['name'] === $resize_name and $res_array['entity_name'] === $owner_name and $res_array['image_name'] === $image_name and $res_array['entity_id'] === $owner_id)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     * @param string $attr_name
     * @param mixed $value
     *
     * @return bool
     */
    public function imageAttrEq($owner_name, $owner_id, $image_name, $attr_name, $value)
    {
        foreach($this->images as $image_array)
        {
            if($image_array['name'] === $image_name and $image_array['entity_name'] === $owner_name and $image_array['entity_id'] === $owner_id)
            {
                if(array_key_exists($attr_name, $image_array))
                {
                    return ($image_array[$attr_name] === $value);
                }
            }
        }

        return false;
    }

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     * @param string $resize_name
     * @param string $attr_name
     * @param mixed $value
     *
     * @return bool
     */
    public function resizeAttrEq($owner_name, $owner_id, $image_name, $resize_name, $attr_name, $value)
    {
        foreach($this->resizes as $res_array)
        {
            if($res_array['name'] === $resize_name and $res_array['entity_name'] === $owner_name and $res_array['image_name'] === $image_name and $res_array['entity_id'] === $owner_id)
            {
                if(array_key_exists($attr_name, $res_array))
                {
                    return ($res_array[$attr_name] === $value);
                }
            }
        }

        return false;
    }

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     * @param string $crop_name
     * @param string $attr_name
     * @param mixed $value
     *
     * @return bool
     */
    public function cropAttrEq($owner_name, $owner_id, $image_name, $crop_name, $attr_name, $value)
    {
        foreach($this->crops as $crop_array)
        {
            if($crop_array['name'] === $crop_name and $crop_array['entity_name'] === $owner_name and $crop_array['image_name'] === $image_name and $crop_array['entity_id'] === $owner_id)
            {
                if(array_key_exists($attr_name, $crop_array))
                {
                    return ($crop_array[$attr_name] === $value);
                }
            }
        }

        return false;
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param array $attrs
     *
     * @return void
     */
    public function imageToDb(ARef $aRef, ImageSettingInterface $imageSetting, $attrs = [], $init = false)
    {
        $owner_name = $aRef->getType()->getName();
        $image_name = $imageSetting->getName();
        $owner_id = $aRef->getId();

        $image_array = null;

        foreach($this->images as & $value_array)
        {
            if($value_array['name'] === $image_name and $value_array['entity_name'] === $owner_name and $value_array['entity_id'] === $owner_id)
            {
                $image_array = & $value_array;
            }
        }

        if($image_array !== null)
        {
            $image_array['cache_index']++;

            if(array_key_exists('alt', $attrs))
            {
                $image_array['alt'] = $attrs['alt'];
            }

            if(array_key_exists('link', $attrs))
            {
                $image_array['link'] = $attrs['link'];
            }

            if(array_key_exists('width', $attrs))
            {
                $image_array['width'] = $attrs['width'];
            }

            if(array_key_exists('height', $attrs))
            {
                $image_array['height'] = $attrs['height'];
            }
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\ResizeSetting $resizeSetting
     * @param array $attrs
     *
     * @return array
     */
    public function resizeToDb(ARef $aRef, ImageSettingInterface $imageSetting, ResizeSettingInterface $resizeSetting, $attrs = [], $init = false)
    {
        $owner_name = $aRef->getType()->getName();
        $image_name = $imageSetting->getName();
        $resize_name = $resizeSetting->getName();
        $owner_id = $aRef->getId();

        $resize_array = null;

        foreach($this->resizes as & $value_array)
        {
            if($value_array['name'] === $resize_name and $value_array['image_name'] === $image_name and $value_array['entity_name'] === $owner_name and $value_array['entity_id'] === $owner_id)
            {
                $resize_array = & $value_array;
            }
        }

        if($resize_array !== null)
        {
            $resize_array['cache_index']++;

            if(array_key_exists('alt', $attrs))
            {
                $resize_array['alt'] = $attrs['alt'];
            }

            if(array_key_exists('link', $attrs))
            {
                $resize_array['link'] = $attrs['link'];
            }

            if(array_key_exists('width', $attrs))
            {
                $resize_array['width'] = $attrs['width'];
            }

            if(array_key_exists('height', $attrs))
            {
                $resize_array['height'] = $attrs['height'];
            }
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\CropSetting $cropSetting
     * @param array $attrs
     *
     * @return void
     */
    public function cropToDb(ARef $aRef, ImageSettingInterface $imageSetting, CropSettingInterface $cropSetting, $attrs = [], $init = false)
    {
        $owner_name = $aRef->getType()->getName();
        $image_name = $imageSetting->getName();
        $crop_name = $cropSetting->getName();
        $owner_id = $aRef->getId();

        $crop_array = null;

        foreach($this->crops as & $value_array)
        {
            if($value_array['name'] === $crop_name and $value_array['image_name'] === $image_name and $value_array['entity_name'] === $owner_name and $value_array['entity_id'] === $owner_id)
            {
                $crop_array = & $value_array;
            }
        }

        if($crop_array !== null)
        {
            $crop_array['cache_index']++;

            if(array_key_exists('alt', $attrs))
            {
                $crop_array['alt'] = $attrs['alt'];
            }

            if(array_key_exists('link', $attrs))
            {
                $crop_array['link'] = $attrs['link'];
            }

            if(array_key_exists('width', $attrs))
            {
                $crop_array['width'] = $attrs['width'];
            }

            if(array_key_exists('height', $attrs))
            {
                $crop_array['height'] = $attrs['height'];
            }

            if(array_key_exists('x', $attrs))
            {
                $crop_array['x'] = $attrs['x'];
            }

            if(array_key_exists('y', $attrs))
            {
                $crop_array['y'] = $attrs['y'];
            }

            $crop_array['man_name'] = $cropSetting->getMan()->getName();
            $crop_array['target_name'] = $cropSetting->getTarget()->getName();
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     *
     * @return void
     */
    public function deleteImage(ARef $aRef, ImageSettingInterface $imageSetting)
    {
        $id = $aRef->getId();
        $entity = $imageSetting->getEntityName();
        $name = $imageSetting->getName();

        foreach($this->images as $key => $image_array)
        {
            if($image_array['name'] === $name and $image_array['entity_name'] === $entity and $image_array['entity_id'] === $id)
            {
                unset($this->images[$key]);

                break;
            }
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\ResizeSetting $resizeSetting
     *
     * @return void
     */
    public function deleteResize(ARef $aRef, ImageSettingInterface $imageSetting, ResizeSettingInterface $resizeSetting)
    {
        $id = $aRef->getId();
        $entity = $imageSetting->getEntityName();
        $name = $imageSetting->getName();
        $res_name = $resizeSetting->getName();

        foreach($this->resizes as $key => $res_array)
        {
            if($res_array['name'] === $res_name and $res_array['entity_name'] === $entity and $res_array['image_name'] === $name and $res_array['entity_id'] === $id)
            {
                unset($this->resizes[$key]);

                break;
            }
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\CropSetting $cropSetting
     *
     * @return void
     */
    public function deleteCrop(ARef $aRef, ImageSettingInterface $imageSetting, CropSettingInterface $cropSetting)
    {
        $id = $aRef->getId();
        $entity = $imageSetting->getEntityName();
        $name = $imageSetting->getName();
        $crop_name = $cropSetting->getName();

        foreach($this->crops as $key => $crop_array)
        {
            if($crop_array['name'] === $crop_name and $crop_array['entity_name'] === $entity and $crop_array['image_name'] === $name and $crop_array['entity_id'] === $id)
            {
                unset($this->crops[$key]);

                break;
            }
        }
    }

}
