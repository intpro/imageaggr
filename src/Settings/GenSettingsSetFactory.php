<?php

namespace Interpro\ImageAggr\Settings;

use Interpro\Core\Contracts\Taxonomy\Types\BlockType;
use Interpro\ImageAggr\Exception\ImageAggrException;
use Interpro\ImageAggr\Contracts\Settings\Enum\GenVariant;
use Interpro\ImageAggr\Contracts\Settings\Enum\ModVariant;
use Interpro\ImageAggr\Contracts\Settings\Enum\Position;
use Interpro\ImageAggr\Contracts\Settings\GenSettingsSetFactory as GenSettingsSetFactoryInterface;
use Interpro\ImageAggr\Settings\Collection\CropSettingsSet;
use Interpro\ImageAggr\Settings\Collection\ModSet;
use Interpro\ImageAggr\Settings\Collection\ResizeSettingsSet;

class GenSettingsSetFactory implements GenSettingsSetFactoryInterface
{
    private $crop_config;
    private $resize_config;
    private $resizes;
    private $crops;
    private $modImagesType;
    private $positions;

    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Types\BlockType $modImagesType
     * @param array $resize_config
     * @param array $crop_config
     * @return void
     */
    public function __construct(BlockType $modImagesType, array $resize_config, array $crop_config)
    {
        $this->resize_config = $resize_config;
        $this->crop_config   = $crop_config;
        $this->resizes       = [];
        $this->crops         = [];
        $this->modImagesType = $modImagesType;
        $this->positions     = [
            Position::CENTER,
            Position::TOP,
            Position::TOP_LEFT,
            Position::TOP_RIGHT,
            Position::BOTTOM,
            Position::BOTTOM_LEFT,
            Position::BOTTOM_RIGHT,
            Position::LEFT,
            Position::RIGHT
        ];
    }

    private function createMods(array $attrs, $from_mod_section = false)
    {
        $modSettings = [];

        if(array_key_exists('mods', $attrs))
        {
            if($from_mod_section)
            {
                throw new ImageAggrException('Нельзя модифицировать картинки, предназначенные для модификации!');
            }

            $mods = $attrs['mods'];

            if(!is_array($mods))
            {
                throw new ImageAggrException('Модификаторы (mods) должны быть заданы массивом, передано '.gettype($mods).'!');
            }

            foreach($mods as $mod_name => $params)
            {
                if(!is_string($mod_name))
                {
                    throw new ImageAggrException('Имя модификатора должно быть задано строкой!');
                }

                if($mod_name !== ModVariant::MASK and $mod_name !== ModVariant::WMARK)
                {
                    throw new ImageAggrException('Имя модификатора должно быть '.ModVariant::MASK.' или '.ModVariant::WMARK.', передано '.$mod_name.'!');
                }

                if(!is_array($params))
                {
                    throw new ImageAggrException('Параметры модификации должны быть заданы массивом передано '.gettype($params).'!');
                }

                if(array_key_exists('image_key', $params))
                {
                    $image_key = $params['image_key'];
                }
                else
                {
                    throw new ImageAggrException('В параметрах модификации должен присутствовать ключ модификации картинки (image_key)!');
                }

                $position = Position::CENTER;

                $x = 0;
                $y = 0;

                if(array_key_exists('position', $params))
                {
                    if(in_array($params['position'], $this->positions))
                    {
                        $position = $params['position'];
                    }
                    else
                    {
                        throw new ImageAggrException('Позиция в параметрах модификации должна задаваться одним из следующих строковых значений '.implode(', ', $this->positions).', а передано '.$params['position'].'!');
                    }
                }

                if(array_key_exists('x', $params))
                {
                    if(is_int($params['x']) and $params['x']>=0)
                    {
                        $x = $params['x'];
                    }
                    else
                    {
                        throw new ImageAggrException('Координата (x) в параметрах модификации должна задаваться целым положительным числом, передано '.$params['x'].'!');
                    }
                }

                if(array_key_exists('y', $params))
                {
                    if(is_int($params['y']) and $params['y']>=0)
                    {
                        $y = $params['y'];
                    }
                    else
                    {
                        throw new ImageAggrException('Координата (y) в параметрах модификации должна задаваться целым положительным числом, передано '.$params['y'].'!');
                    }
                }

                $gen_path = explode('.', $image_key);

                if(count($gen_path) !== 3)
                {
                    throw new ImageAggrException('Картинка модификатора должна быть задана строкой имя_картинки.генерация.имя_генерации, передано '.$image_key.'!');
                }

                //Проверяем, есть ли такая картинка в сервис блоке
                $field = $this->modImagesType->getField($gen_path[0]);
                $fieldType = $field->getFieldType();

                if($fieldType->getName() !== 'image')
                {
                    throw new ImageAggrException('Картинка модификатора должна ссылаться на тип image, передано ('.$image_key.') '.$fieldType->getName().'!');
                }

                $gen_var = $gen_path[1];

                if($gen_var === GenVariant::RESIZE)
                {
                    $gen = $this->createResizeSetting($gen_path[2], true);
                }
                elseif($gen_var === GenVariant::CROP)
                {
                    $gen = $this->createCropSetting($gen_path[2], true);
                }
                else
                {
                    throw new ImageAggrException('Вариант генерации модификатора должен быть ресайзом (RESIZE) или кропом (CROP), передан ('.$image_key.') '.$gen_var.'!');
                }

                //Создаем модификатор
                $modSettings[$mod_name] = new ModSetting($gen, $field, $mod_name, $position, $x, $y);
            }
        }

        $modSettingsSet = new ModSet($modSettings);

        return $modSettingsSet;
    }

    private function createResizeSetting($resize_name, $from_mod_section = false)
    {
        if(array_key_exists($resize_name, $this->resizes))
        {
            return $this->resizes[$resize_name];
        }


        if(!array_key_exists($resize_name, $this->resize_config))
        {
            throw new ImageAggrException('Настройка ресайза по имени '.$resize_name.' отсутствует!');
        }


        $attrs = $this->resize_config[$resize_name];

        if(array_key_exists('width', $attrs))
        {
            $width = $attrs['width'];
            if(!is_null($width))
            {
                if(!is_int($width))
                {
                    throw new ImageAggrException('Ширина ресайза (width) '.$resize_name.' должна быть задана целым положительным числом или null значением, передано '.gettype($width).'!');
                }
            }
        }
        else
        {
            $width = null;
        }

        if(array_key_exists('height', $attrs))
        {
            $height = $attrs['height'];

            if(!is_null($height))
            {
                if(!is_int($height))
                {
                    throw new ImageAggrException('Высота ресайза (height) '.$resize_name.' должна быть задана целым положительным числом или null значением, передано '.gettype($height).'!');
                }
            }
        }
        else
        {
            $height = null;
        }

        //Если нет размеров ресайза, значит просто применяем моды к оригиналу и сохраняем в ресайз
        //if(is_null($width) and is_null($height))
        //{
        //    throw new ImageAggrException('В настройке ресайза '.$resize_name.' высота (width) и ширина (height) заданы null значением, должно быть задано хотя бы одно значение числом больше нуля(0)!');
        //}

        $modSettingsSet = $this->createMods($attrs, $from_mod_section);


        if(array_key_exists('when_upload', $attrs))
        {
            $when_upload = $attrs['when_upload'];

            if(!is_bool($when_upload))
            {
                throw new ImageAggrException('Признак служебного ресайза (when_upload) '.$resize_name.' должен быть задан булевым значением, передано '.gettype($when_upload).'!');
            }
        }
        else
        {
            $when_upload = false;
        }

        $resizeSetting = new ResizeSetting($resize_name, $width, $height, $modSettingsSet, $when_upload);

        //Закэшируем, чтобы экземпляры не повторялись
        $this->resizes[$resize_name] = $resizeSetting;

        return $resizeSetting;
    }

    private function createCropSetting($crop_name, $from_mod_section = false)
    {
        if(array_key_exists($crop_name, $this->crops))
        {
            return $this->crops[$crop_name];
        }


        if(!array_key_exists($crop_name, $this->crop_config))
        {
            throw new ImageAggrException('Настройка кропа по имени '.$crop_name.' отсутствует!');
        }


        $attrs = $this->crop_config[$crop_name];

        if(array_key_exists('width', $attrs))
        {
            $width = $attrs['width'];

            if(!is_int($width))
            {
                throw new ImageAggrException('Ширина кропа (width) '.$crop_name.' должна быть задана целым положительным числом, передано '.gettype($width).'!');
            }
        }
        else
        {
            throw new ImageAggrException('Ширина кропа (width) '.$crop_name.' отсутствует в настройке!');
        }

        if(array_key_exists('height', $attrs))
        {
            $height = $attrs['height'];

            if(!is_int($height))
            {
                throw new ImageAggrException('Высота кропа (height) '.$crop_name.' должна быть задана целым положительным числом, передано '.gettype($height).'!');
            }
        }
        else
        {
            throw new ImageAggrException('Высота кропа (height) '.$crop_name.' отсутствует в настройке!');
        }

        if(array_key_exists('man', $attrs))
        {
            $man_name = $attrs['man'];

            if(!is_string($man_name))
            {
                throw new ImageAggrException('Имя ресайза виджета для кропа (man) '.$crop_name.' должно быть задано строкой, передано '.gettype($height).'!');
            }

            $man = $this->createResizeSetting($man_name);
        }
        else
        {
            throw new ImageAggrException('Имя ресайза виджета для кропа (man) '.$crop_name.' отсутствует в настройке!');
        }

        if(array_key_exists('target', $attrs))
        {
            $target_name = $attrs['target'];

            if(!is_string($target_name))
            {
                throw new ImageAggrException('Имя ресайза цели для кропа (target) '.$crop_name.' должно быть задано строкой, передано '.gettype($target_name).'!');
            }

            $target = $this->createResizeSetting($target_name);
        }
        else
        {
            throw new ImageAggrException('Имя ресайза цели для кропа (target) '.$crop_name.' отсутствует в настройке!');
        }

        $modSettingsSet = $this->createMods($attrs, $from_mod_section);



        $cropSetting = new CropSetting($crop_name, $width, $height, $man, $target, $modSettingsSet);

        //Закэшируем, чтобы экземпляры не повторялись
        $this->crops[$crop_name] = $cropSetting;

        return $cropSetting;
    }

    /**
     * @param array $resize_names
     *
     * @return \Interpro\ImageAggr\Contracts\Settings\Collection\ResizeSettingsSet
     */
    public function createResizeSettingsSet($resize_names = [])
    {
        $resizeSettings = [];

        foreach($resize_names as $resize_name)
        {
            $resizeSettings[$resize_name] = $this->createResizeSetting($resize_name);
        }

        $resizeSettingsSet = new ResizeSettingsSet($resizeSettings);

        return $resizeSettingsSet;
    }

    /**
     * @param array $crop_names
     *
     * @return \Interpro\ImageAggr\Contracts\Settings\Collection\CropSettingsSet
     */
    public function createCropSettingsSet($crop_names = [])
    {
        $cropSettings = [];

        foreach($crop_names as $crop_name)
        {
            $cropSettings[$crop_name] = $this->createCropSetting($crop_name);
        }

        $cropSettingsSet = new CropSettingsSet($cropSettings);

        return $cropSettingsSet;
    }
}
