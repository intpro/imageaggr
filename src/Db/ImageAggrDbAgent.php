<?php

namespace Interpro\ImageAggr\Db;

use Interpro\Core\Contracts\Mediator\RefConsistMediator;
use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Settings\CropSetting as CropSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting as ImageSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\ResizeSetting as ResizeSettingInterface;
use Interpro\ImageAggr\Model\Crop;
use Interpro\ImageAggr\Model\Image;
use Interpro\ImageAggr\Model\Resize;
use Interpro\ImageAggr\Contracts\Db\ImageAggrDbAgent as ImageAggrDbAgentInterface;

class ImageAggrDbAgent implements ImageAggrDbAgentInterface
{
    private $refConsistMediator;
    /**
     * @return void
     */
    public function __construct(RefConsistMediator $refConsistMediator)
    {
        $this->refConsistMediator = $refConsistMediator;
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     */
    public function ownerExist(ARef $aRef)
    {
        return $this->refConsistMediator->exist($aRef);
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

        $collection = Crop::where('entity_name', '=', $owner_name)
            ->where('entity_id', '=', $owner_id)
            ->where('image_name', '=', $image_name)
            ->get();

        $crops_array = [];

        foreach($collection as $crop)
        {
            $crops_array[$crop->name] = ['x' => $crop->x, 'y'=> $crop->y];
        }

        return $crops_array;
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

        $collection = Image::where('entity_name', '=', $owner_name)->where('entity_id', '=', $owner_id)->where('name', '=', $image_name)->get();

        $cache_index = 0;

        $image = null;

        //Удалим лишние записи по этому ключу, если есть (на всякий случай, т. к. записи в БД уникальны по авто инк. id)
        foreach($collection as $curr_image)
        {
            if($image === null)
            {
                $cache_index = $curr_image->cache_index;
                $image = $curr_image;

                if($init)
                {
                    break;
                }
            }
            else
            {
                $image->delete();
            }
        }

        $db = !$init;

        if($image === null)
        {
            $image = new Image;
            $image->alt = '';
            $image->link = '';
            $image->width = 400;
            $image->height = 400;
            $db = true;
        }

        $cache_index++;

        if($db)
        {
            //Если реквизита нет переданных для записи или не собираемся записывать в базу(инициализация), то взять для построения item'а реквизит из базы
            if(!array_key_exists('alt', $attrs))
            {
                $attrs['alt'] = $image->alt;
            }

            if(!array_key_exists('link', $attrs))
            {
                $attrs['link'] = $image->link;
            }

            if(!array_key_exists('width', $attrs))
            {
                $attrs['width'] = $image->width;
            }

            if(!array_key_exists('height', $attrs))
            {
                $attrs['height'] = $image->height;
            }

            $image->cache_index  = $cache_index;
            $image->entity_name  = $owner_name;
            $image->entity_id    = $owner_id;
            $image->name         = $image_name;
            $image->alt          = $attrs['alt'];
            $image->link         = $attrs['link'];
            $image->width        = $attrs['width'];
            $image->height       = $attrs['height'];

            $image->save();
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\ResizeSetting $resizeSetting
     * @param array $attrs
     *
     * @return void
     */
    public function resizeToDb(ARef $aRef, ImageSettingInterface $imageSetting, ResizeSettingInterface $resizeSetting, $attrs = [], $init = false)
    {
        $owner_name = $aRef->getType()->getName();
        $image_name = $imageSetting->getName();
        $resize_name = $resizeSetting->getName();
        $owner_id = $aRef->getId();

        $collection = Resize::where('entity_name', '=', $owner_name)
            ->where('entity_id', '=', $owner_id)
            ->where('image_name', '=', $image_name)
            ->where('name', '=', $resize_name)
            ->get();

        $cache_index = 0;

        $resize = null;

        //Удалим все записи по этому ключу (на всякий случай, т. к. записи в БД уникальны по авто инк. id)
        foreach($collection as $curr_resize)
        {
            if($resize === null)
            {
                $cache_index = $curr_resize->cache_index;
                $resize = $curr_resize;

                if($init)
                {
                    break;
                }
            }
            else
            {
                $curr_resize->delete();
            }
        }

        $db = !$init;

        if($resize === null)
        {
            $resize = new Resize;
            $resize->alt = '';
            $resize->link = '';
            $resize->width = 400;
            $resize->height = 400;
            $db = true;
        }

        $cache_index++;

        if($db)
        {
            if(!array_key_exists('alt', $attrs))
            {
                $attrs['alt'] = $resize->alt;
            }

            if(!array_key_exists('link', $attrs))
            {
                $attrs['link'] = $resize->link;
            }

            if(!array_key_exists('width', $attrs))
            {
                $attrs['width'] = $resize->width;
            }

            if(!array_key_exists('height', $attrs))
            {
                $attrs['height'] = $resize->height;
            }

            $resize->cache_index = $cache_index;
            $resize->entity_name = $owner_name;
            $resize->entity_id   = $owner_id;
            $resize->image_name  = $image_name;
            $resize->name        = $resize_name;
            $resize->alt         = $attrs['alt'];
            $resize->link        = $attrs['link'];
            $resize->width       = $attrs['width'];
            $resize->height      = $attrs['height'];

            $resize->save();
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

        $collection = Crop::where('entity_name', '=', $owner_name)
            ->where('entity_id', '=', $owner_id)
            ->where('image_name', '=', $image_name)
            ->where('name', '=', $crop_name)
            ->get();

        $cache_index = 0;

        $crop = null;

        //Удалим лишние записи по этому ключу, если есть (на всякий случай, т. к. записи в БД уникальны по авто инк. id)
        foreach($collection as $curr_crop)
        {
            if($crop === null)
            {
                $cache_index = $curr_crop->cache_index;
                $crop = $curr_crop;

                if($init)
                {
                    break;
                }
            }
            else
            {
                $crop->delete();
            }
        }

        $db = !$init;

        if($crop === null)
        {
            $crop = new Crop;
            $crop->alt = '';
            $crop->link = '';
            $crop->width = 400;
            $crop->height = 400;
            $crop->x = 0;
            $crop->y = 0;
            $db = true;
        }

        $cache_index++;

        if($db)
        {
            if(!array_key_exists('alt', $attrs))
            {
                $attrs['alt'] = $crop->alt;
            }

            if(!array_key_exists('link', $attrs))
            {
                $attrs['link'] = $crop->link;
            }

            if(!array_key_exists('width', $attrs))
            {
                $attrs['width'] = $crop->width;
            }

            if(!array_key_exists('height', $attrs))
            {
                $attrs['height'] = $crop->height;
            }

            if(!array_key_exists('x', $attrs))
            {
                $attrs['x'] = $crop->x;
            }

            if(!array_key_exists('y', $attrs))
            {
                $attrs['y'] = $crop->y;
            }

            $crop->name        = $crop_name;
            $crop->entity_name = $owner_name;
            $crop->entity_id   = $owner_id;
            $crop->image_name  = $image_name;
            $crop->alt         = $attrs['alt'];
            $crop->link        = $attrs['link'];
            $crop->man_name    = $cropSetting->getMan()->getName();
            $crop->target_name = $cropSetting->getTarget()->getName();
            $crop->cache_index = $cache_index;

            $crop->x = $attrs['x'];
            $crop->y = $attrs['y'];

            //Кроп всегда задан постоянными размерами
            $crop->width  = $attrs['width'];
            $crop->height = $attrs['height'];

            $crop->save();
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
        $owner_name = $aRef->getType()->getName();
        $image_name = $imageSetting->getName();
        $owner_id = $aRef->getId();

        //Удалим все записи по этому ключу (на всякий случай, т. к. записи в БД уникальны по авто инк. id)
        Image::where('entity_name', '=', $owner_name)->where('entity_id', '=', $owner_id)->where('name', '=', $image_name)->delete();
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
        $owner_name = $aRef->getType()->getName();
        $image_name = $imageSetting->getName();
        $resize_name = $resizeSetting->getName();
        $owner_id = $aRef->getId();

        Resize::where('entity_name', '=', $owner_name)
            ->where('entity_id', '=', $owner_id)
            ->where('image_name', '=', $image_name)
            ->where('name', '=', $resize_name)
            ->delete();
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
        $owner_name = $aRef->getType()->getName();
        $image_name = $imageSetting->getName();
        $crop_name = $cropSetting->getName();
        $owner_id = $aRef->getId();

        Crop::where('entity_name', '=', $owner_name)
            ->where('entity_id', '=', $owner_id)
            ->where('image_name', '=', $image_name)
            ->where('name', '=', $crop_name)
            ->delete();
    }

}
