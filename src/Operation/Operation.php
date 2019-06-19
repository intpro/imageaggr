<?php

namespace Interpro\ImageAggr\Operation;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Db\ImageAggrDbAgent as ImageAggrDbAgentInterface;
use Interpro\ImageAggr\Contracts\Settings\Collection\ModSet;
use Interpro\ImageAggr\Contracts\Settings\GenerationSetting;
use Interpro\ImageAggr\Contracts\Settings\ModSetting;
use Interpro\ImageAggr\Exception\OperationException;
use Interpro\ImageAggr\Contracts\Operation\Enum\MimeType;
use Interpro\ImageAggr\Contracts\Placeholder\PlaceholderAgent as PlaceholderAgentInterface;
use Interpro\ImageAggr\Contracts\Settings\CropSetting as CropSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\Enum\GenVariant;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting as ImageSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\PathResolver as PathResolverInterface;
use Interpro\ImageAggr\Contracts\Settings\ResizeSetting as ResizeSettingInterface;
use Interpro\Core\Contracts\Taxonomy\Taxonomy as TaxonomyInterface;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image as ImageFacade;

abstract class Operation
{
    protected $imageType;
    protected $resizeType;
    protected $cropType;

    protected $pathResolver;
    protected $taxonomy;
    protected $dbAgent;
    protected $phAgent;

    protected $mime_types;

    public function __construct(PathResolverInterface $pathResolver,
                                TaxonomyInterface $taxonomy,
                                ImageAggrDbAgentInterface $imageAggrDbAgent,
                                PlaceholderAgentInterface $placeholderAgent)
    {
        $this->pathResolver = $pathResolver;
        $this->taxonomy = $taxonomy;
        $this->dbAgent = $imageAggrDbAgent;
        $this->phAgent = $placeholderAgent;

        $this->imageType = $this->taxonomy->getType('image');
        $this->resizeType = $this->taxonomy->getType('resize');
        $this->cropType = $this->taxonomy->getType('crop');

        $this->mime_types = [
            'gif'=>'image/gif',
            'jpeg'=>'image/jpeg',
            'png'=>'image/png',
            'svg'=>'image/svg+xml',
            'svg'=>'image/svg',
        ];
    }

    protected function checkOwner(ARef $aRef)
    {
        if(!$this->dbAgent->ownerExist($aRef))
        {
            throw new OperationException('Сущность '.$aRef->getType()->getName().'('.$aRef->getId().') не найдена!');
        }
    }

    protected function deleteAllFilesByPath($path)
    {
        foreach (glob($path.'*.*') as $file)
        {
            if(is_dir($file))
            {
                continue;
            }
            unlink($file);
        }
    }

    protected function deleteAllFiles(ARef $aRef, ImageSettingInterface $imageSetting, $tmp = false)
    {
        if($tmp)
        {
            $images_dir = $this->pathResolver->getTmpDir();
        }
        else
        {
            $images_dir = $this->pathResolver->getImageDir();
        }

        if (!is_writable($images_dir))
        {
            throw new OperationException('Дирректория картинок ('.$images_dir.') не доступна для записи!');
        }

        $image_prefix = $this->getImagePrefix($aRef->getType()->getName(), $aRef->getId(), $imageSetting->getName(), GenVariant::NONE);

        $this->deleteAllFilesByPath($images_dir.'/'.$image_prefix);

        $this->deleteGens($aRef, $imageSetting, $tmp);
    }

    protected function deleteGens(ARef $aRef, ImageSettingInterface $imageSetting, $tmp = false)
    {
        if($tmp)
        {
            $resize_dir = $this->pathResolver->getResizeTmpDir();
        }
        else
        {
            $resize_dir = $this->pathResolver->getResizeDir();
        }

        if (!is_writable($resize_dir))
        {
            throw new OperationException('Дирректория ресайзов ('.$resize_dir.') не доступна для записи!');
        }

        $image_prefix = $this->getImagePrefix($aRef->getType()->getName(), $aRef->getId(), $imageSetting->getName(), GenVariant::NONE);

        if(!$tmp)
        {
            $crop_dir = $this->pathResolver->getCropDir();

            if (!is_writable($crop_dir))
            {
                throw new OperationException('Дирректория кропов ('.$crop_dir.') не доступна для записи!');
            }

            $this->deleteAllFilesByPath($crop_dir.'/'.$image_prefix);
        }

        $this->deleteAllFilesByPath($resize_dir.'/'.$image_prefix);
    }

    protected function getImagePrefix($owner_name, $owner_id, $image_name, $gen_variant, $gen_name = '')
    {
        return $owner_name.'_'.$owner_id.'_'.$image_name.($gen_variant !== GenVariant::NONE ? '_'.$gen_name : '');
    }

    protected function isVectorImage($mime)
    {
        if($mime === MimeType::SVG)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function getExtension($mime)
    {
        $ext = array_search($mime, $this->mime_types);

        if(!$ext)
        {
            throw new OperationException('Попытка получить расширение файла для не поддерживаемого типа '.$mime.'!');
        }

        return $ext;
    }

    protected function getNamedAttrs($name, & $attrs)
    {
        $item_attrs = [];

        if(array_key_exists($name, $attrs))
        {
            $item_attrs = & $attrs[$name];
        }

        return $item_attrs;
    }

    protected function getModImg(ModSetting $modSetting)
    {
        $image = $modSetting->getImageField();
        $genSetting = $modSetting->getGen();
        $gen_var = $genSetting->getVariant();

        $mod_file_prefix = $this->getImagePrefix('modimages', 0, $image->getName(), $gen_var, $genSetting->getName());

        if($gen_var === GenVariant::RESIZE)
        {
            $images_dir = $this->pathResolver->getResizeDir();
        }
        elseif($gen_var === GenVariant::CROP)
        {
            $images_dir = $this->pathResolver->getCropDir();
        }
        else
        {
            throw new OperationException('Ошибка определения дирректории файла модификации картинки (вариант должен быть Resize или Crop)!');
        }

        $mod_file_path = $images_dir.'/'.$mod_file_prefix;

        $mod_file_name = false;

        foreach (glob($mod_file_path.'.*') as $file)
        {
            if(is_dir($file))
            {
                continue;
            }

            $original_mime = File::mimeType($file);

            if($this->isVectorImage($original_mime))
            {
                continue;
            }

            $mod_file_name = $file;

            break;
        }

        if(!$mod_file_name)
        {
            throw new OperationException('Не найден ни один файл модификации '.$mod_file_path.' с растровым расширением!');
        }

        $modImg = $resizeImage = ImageFacade::make($mod_file_name);

        return $modImg;
    }

    protected function modImage(\Intervention\Image\Image $img, ModSet $mods)
    {
        foreach($mods as $modSetting)
        {
            $position   = $modSetting->getPosition();
            $x          = $modSetting->getX();
            $y          = $modSetting->getY();

            $modImg = $this->getModImg($modSetting);

            $img->insert($modImg, $position, $x, $y);
        }
    }

    /**
     * @param ImageSettingInterface $imageSetting
     * @param int $owner_id
     * @param array $attrs
     * @param bool $init
     *
     * @return void
     */
    protected function makePhImageResizes(ARef $aRef, ImageSettingInterface $imageSetting, $attrs = [], $init = false)
    {
        $resizes = $imageSetting->getResizes();

        foreach($resizes as $resize_name => $resizeSetting)
        {
            $item_attrs = $this->getNamedAttrs($resize_name, $attrs);

            $item_attrs['link'] = $this->phAgent->getResizePh($resizeSetting);

            $width = $resizeSetting->getWidth();
            $height = $resizeSetting->getHeight();

            //Все будет квадратным
            if(!$width)
            {
                $width = $height;
            }
            if(!$height)
            {
                $height = $width;
            }

            $item_attrs['width'] = $width;
            $item_attrs['height'] = $height;

            $this->dbAgent->resizeToDb($aRef, $imageSetting, $resizeSetting, $item_attrs, $init);
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param array $attrs
     * @param bool $init
     *
     * @return void
     */
    protected function makePhImageCrops(ARef $aRef, ImageSettingInterface $imageSetting, $attrs = [], $init = false)
    {
        $crops = $imageSetting->getCrops();

        foreach($crops as $crop_name => $cropSetting)
        {
            $item_attrs = $this->getNamedAttrs($crop_name, $attrs);

            $item_attrs['link'] = $this->phAgent->getCropPh($cropSetting);

            $width = $cropSetting->getWidth();
            $height = $cropSetting->getHeight();

            $item_attrs['width'] = $width;
            $item_attrs['height'] = $height;
            $item_attrs['x'] = 0;
            $item_attrs['y'] = 0;

            $this->dbAgent->cropToDb($aRef, $imageSetting, $cropSetting, $item_attrs, $init);
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param string $original_file_name
     * @param array $attrs
     *
     * @return void
     */
    protected function makeSameImageResizes(ARef $aRef, ImageSettingInterface $imageSetting, $original_file_name, $attrs = [])
    {
        $resizes = $imageSetting->getResizes();

        foreach($resizes as $resize_name => $resizeSetting)
        {
            $item_attrs = $this->getNamedAttrs($resize_name, $attrs);

            $item_attrs['link'] = $original_file_name;

            $this->dbAgent->resizeToDb($aRef, $imageSetting, $resizeSetting, $item_attrs);
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param string $original_file_name
     * @param array $attrs
     *
     * @return void
     */
    protected function makeSameImageCrops(ARef $aRef, ImageSettingInterface $imageSetting, $original_file_name, $attrs = [])
    {
        $crops = $imageSetting->getCrops();

        foreach($crops as $crop_name => $cropSetting)
        {
            $item_attrs = $this->getNamedAttrs($crop_name, $attrs);

            $item_attrs['link'] = $original_file_name;

            $this->dbAgent->cropToDb($aRef, $imageSetting, $cropSetting, $item_attrs);
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param array $attrs
     * @param bool $tmp
     *
     * @return void
     */
    protected function makeImageResizes(ARef $aRef, ImageSettingInterface $imageSetting, array $attrs = [], $tmp = false)
    {
        $resizes = $imageSetting->getResizes();

        foreach($resizes as $resize_name => $resizeSetting)
        {
            if($tmp and !$resizeSetting->whenUpload())
            {
                continue;
            }

            $item_attrs = $this->getNamedAttrs($resize_name, $attrs);

            $this->makeResize($aRef, $imageSetting, $resizeSetting, $item_attrs, $tmp);
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param array $attrs
     *
     * @return void
     */
    protected function makeImageCrops(ARef $aRef, ImageSettingInterface $imageSetting, array $attrs = [])
    {
        $crops = $imageSetting->getCrops();

        foreach($crops as $crop_name => $cropSetting)
        {
            //Получить или создать атрибуты для текущего кропа
            $item_attrs = $this->getNamedAttrs($crop_name, $attrs);

            $this->makeCrop($aRef, $imageSetting, $cropSetting, $item_attrs);
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\ResizeSetting $resizeSetting
     * @param array $item_attrs
     * @param bool $tmp
     *
     * @return void
     */
    protected function makeResize(ARef $aRef, ImageSettingInterface $imageSetting, ResizeSettingInterface $resizeSetting, array $item_attrs = [], $tmp = false)
    {
        if($tmp)
        {
            $images_dir = $this->pathResolver->getTmpDir();
        }
        else
        {
            $images_dir = $this->pathResolver->getImageDir();
        }

        if (!is_readable($images_dir))
        {
            throw new OperationException('Дирректория картинок недоступна для чтения!');
        }

        if($tmp)
        {
            $resizes_dir = $this->pathResolver->getResizeTmpDir();
        }
        else
        {
            $resizes_dir = $this->pathResolver->getResizeDir();
        }

        if (!is_writable($resizes_dir))
        {
            throw new OperationException('Дирректория ресайзов недоступна для записи!');
        }

        $original_prefix = $this->getImagePrefix($imageSetting->getEntityName(), $aRef->getId(), $imageSetting->getName(), GenVariant::NONE);
        $original_file_path = $images_dir.'/'.$original_prefix;

        $original_file_name = false;

        foreach (glob($original_file_path.'.*') as $file)
        {
            if(is_dir($file))
            {
                continue;
            }

            $original_mime = File::mimeType($file);

            if($this->isVectorImage($original_mime))
            {
                throw new OperationException('Попытка выполнить ресайз векторного файла '.$file.'!');
            }

            $original_file_name = $file;

            break;
        }

        if(!$original_file_name)
        {
            throw new OperationException('Не найден файл цели для ресайза!');
        }

        $extension = File::extension($original_file_name);

        $width = $resizeSetting->getWidth();
        $height = $resizeSetting->getHeight();

        $img = ImageFacade::make($original_file_name);

        if($width or $height)
        {
            $aspectRatio = !($width and $height);

            if(!$width)
            {
                $width = null;
            }
            if(!$height)
            {
                $height = null;
            }

            $img = $img->resize($width, $height,
                function ($constraint) use ($aspectRatio)
                {
                    if($aspectRatio)
                    {
                        $constraint->aspectRatio();
                    }
                });
        }

        $this->modImage($img, $resizeSetting->getMods());

        $resize_prefix = $this->getImagePrefix($imageSetting->getEntityName(), $aRef->getId(), $imageSetting->getName(), GenVariant::RESIZE, $resizeSetting->getName());
        $resize_file_name = $resize_prefix.'.'.$extension;
        $resize_file_path = $resizes_dir.'/'.$resize_prefix.'.'.$extension;

        $img->save($resize_file_path, 100);

        chmod($resize_file_path, 0644);

        if(!$tmp)
        {
            $resizeImage = ImageFacade::make($resize_file_path);
            $width = $resizeImage->getWidth();
            $height = $resizeImage->getHeight();
            $item_attrs['link'] = $this->pathResolver->getResizePath().'/'.$resize_file_name;
            $item_attrs['width'] = $width;
            $item_attrs['height'] = $height;
            $this->dbAgent->resizeToDb($aRef, $imageSetting, $resizeSetting, $item_attrs);
        }
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\CropSetting $cropSetting
     * @param array $item_attrs
     *
     * @return void
     */
    protected function makeCrop(ARef $aRef, ImageSettingInterface $imageSetting, CropSettingInterface $cropSetting, array $item_attrs = [])
    {
        $manual = false;

        if(array_key_exists('manual', $item_attrs))
        {
            $manual = (bool) $item_attrs['manual'];
        }

        $resizes_dir = $this->pathResolver->getResizeDir();

        if (!is_readable($resizes_dir))
        {
            throw new OperationException('Дирректория ресайзов недоступна для чтения!');
        }

        $crops_dir = $this->pathResolver->getCropDir();

        if (!is_writable($crops_dir))
        {
            throw new OperationException('Дирректория кропов недоступна для записи!');
        }

        $crop_x = 0;
        $crop_y = 0;

        if(array_key_exists('x', $item_attrs))
        {
            $crop_x = $item_attrs['x'];
        }

        if(array_key_exists('y', $item_attrs))
        {
            $crop_y = $item_attrs['y'];
        }

        //Ищем целевой ресайз
        $targetResizeSetting = $cropSetting->getTarget();
        $target_prefix = $this->getImagePrefix($imageSetting->getEntityName(), $aRef->getId(), $imageSetting->getName(), GenVariant::RESIZE, $targetResizeSetting->getName());
        $target_path = $resizes_dir.'/'.$target_prefix;

        $target_file_name = false;

        foreach (glob($target_path.'.*') as $file)
        {
            if(is_dir($file))
            {
                continue;
            }

            $target_mime = File::mimeType($file);

            if($this->isVectorImage($target_mime))
            {
                throw new OperationException('Попытка выполнить кроп векторного файла '.$file.'!');
            }

            $target_file_name = $file;

            break;
        }

        if(!$target_file_name)
        {
            throw new OperationException('Не найден файл цели для кропа по пути '.$target_path.'!');
        }

        $target_extension = File::extension($target_file_name);

        //Формирование пути к файлу кропа
        $crop_prefix = $this->getImagePrefix($imageSetting->getEntityName(), $aRef->getId(), $imageSetting->getName(), GenVariant::CROP, $cropSetting->getName());
        $crop_file_name = $crop_prefix.'.'.$target_extension;
        $crop_path = $crops_dir.'/'.$crop_file_name;

        //Выполнение кропа картинки
        $crop_width = $cropSetting->getWidth();
        $crop_height = $cropSetting->getHeight();
        $crop_color = $cropSetting->getColor();

        $target = ImageFacade::make($target_file_name);

        //Размеры цели
        $target_width = $target->getWidth();
        $target_height = $target->getHeight();

        //Сценарий №1, если кроп больше цели и не спозиционирован
        if($target_width < $crop_width and $target_height < $crop_height and $crop_x === 0 and $crop_y === 0 and !$manual)
        {
            ImageFacade::canvas($crop_width, $crop_height, $crop_color)->insert($target, 'center')->save($crop_path, 100);
        }
        else //Сценарий №2, если кроп спозиционирован или меньше цели
        {
            $crop_left = $crop_x;
            $target_left = 0;

            $crop_right = $crop_x + $crop_width;
            $target_right = $target_width;

            $crop_top = $crop_y;
            $target_top = 0;

            $crop_bottom = $crop_y + $crop_height;
            $target_bottom = $target_height;

            //Если не пересекаются:
            //(Нижний край рамки кропа выше верхнего края цели)
            //или (верхний край рамки кропа ниже нижнего края цели)
            //или (правый край рамки кропа левее левого края цели)
            //или (левый край рамки кропа правее правого края цели)
            if($crop_bottom <= $target_top
                or $crop_top >= $target_bottom
                or $crop_right <= $target_left
                or $crop_left >= $target_right)
            {
                //Пустая картинка цветом фона кропа
                ImageFacade::canvas($crop_width, $crop_height, $crop_color)->save($crop_path, 100);
            }
            else
            {
                //Находим прямоугольник пересечения и вырезаем его

                //Если кроп внутри цели
                if($crop_left >= $target_left and $crop_right <= $target_right and $crop_top >= $target_top and $crop_bottom <= $target_bottom)
                {
                    $target->crop($crop_width, $crop_height, $crop_x, $crop_y);
                    $target->save($crop_path, 100);
                }
                //Если цель внутри кропа
                elseif($target_left >= $crop_left and $target_right <= $crop_right and $target_top >= $crop_top and $target_bottom <= $crop_bottom)
                {
                    ImageFacade::canvas($crop_width, $crop_height, $crop_color)->insert($target, 'top-left', abs($crop_x), abs($crop_y))->save($crop_path, 100);
                }
                else //Тогда находим прямоугольник совпадения
                {
                    //Координаты совпадения по X
                    if($crop_left <= $target_left)
                    {
                        $target_crop_x = 0;
                        $crop_insert_x = -$crop_left;
                    }
                    else
                    {
                        $target_crop_x = $crop_left;
                        $crop_insert_x = 0;
                    }

                    if($crop_right >= $target_right)
                    {
                        $target_crop_width = $target_width;
                    }
                    else
                    {
                        $target_crop_width = $target_width - ($target_right - $crop_right);
                    }

                    $target_crop_width -= $target_crop_x;


                    //Координаты совпадения по Y
                    if($crop_top <= $target_top)
                    {
                        $target_crop_y = 0;
                        $crop_insert_y = -$crop_top;
                    }
                    else
                    {
                        $target_crop_y = $crop_top;
                        $crop_insert_y = 0;
                    }

                    if($crop_bottom >= $target_bottom)
                    {
                        $target_crop_height = $target_height;
                    }
                    else
                    {
                        $target_crop_height = $target_height - ($target_bottom - $crop_bottom);
                    }

                    $target_crop_height -= $target_crop_y;


                    $target->crop($target_crop_width, $target_crop_height, $target_crop_x, $target_crop_y);

                    ImageFacade::canvas($crop_width, $crop_height, $crop_color)->insert($target, 'top-left', $crop_insert_x, $crop_insert_y)->save($crop_path, 100);
                }
            }
        }

        chmod($crop_path, 0644);

        $item_attrs['link'] = $this->pathResolver->getCropPath().'/'.$crop_file_name;
        //-----------------------------------------------------

        $this->dbAgent->cropToDb($aRef, $imageSetting, $cropSetting, $item_attrs);
    }

}
