<?php

namespace Interpro\ImageAggr\Operation;

use Illuminate\Support\Facades\File;
use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting;
use Interpro\ImageAggr\Exception\OperationException;
use Interpro\ImageAggr\Contracts\Operation\Enum\MimeType;
use Interpro\ImageAggr\Contracts\Settings\Enum\GenVariant;
use Intervention\Image\Facades\Image;
use Interpro\ImageAggr\Contracts\Operation\SaveOperation as SaveOperationInterface;

class SaveOperation extends Operation implements SaveOperationInterface
{
    /**
     * @param \Interpro\Core\Contracts\Ref\ARef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param array $user_attrs
     *
     * @return void
     */
    public function execute(ARef $aRef, ImageSetting $imageSetting, array $user_attrs = [])
    {
        if(array_key_exists('update_flag', $user_attrs))
        {
            if($user_attrs['update_flag'] === 'false')
            {
                $update_flag = false;
            }
            else
            {
                $update_flag = (bool) $user_attrs['update_flag'];
            }
        }
        else
        {
            $update_flag = false;
        }

        $owner_name = $aRef->getType()->getName();
        $owner_id = $aRef->getId();
        $image_name = $imageSetting->getName();

        $this->checkOwner($aRef);

        $tmp_dir = $this->pathResolver->getTmpDir();

        if (!is_readable($tmp_dir))
        {
            throw new OperationException('Временная дирректория картинок ('.$tmp_dir.') не доступна для чтения!');
        }

        $images_dir = $this->pathResolver->getImageDir();

        if (!is_writable($images_dir))
        {
            throw new OperationException('Дирректория картинок ('.$images_dir.') не доступна для записи!');
        }

        $image_prefix = $this->getImagePrefix($owner_name, $owner_id, $image_name, GenVariant::NONE);

        $image_path = $images_dir.'/'.$image_prefix;

        $tmp_finded = false;
        $phway = true;

        $tmp_file_name = '';

        if($update_flag)
        {
            $tmp_path = $tmp_dir.'/'.$image_prefix;

            foreach (glob($tmp_path.'.*') as $file)
            {
                if(is_dir($file))
                {
                    continue;
                }

                $tmp_finded = true;
                $tmp_file_name = $file;
                break;
            }
        }
        else
        {
            //Удаление содержимого тэмпа
            $this->deleteAllFiles($aRef, $imageSetting, true);
        }

        if($tmp_finded)
        {
            //Удаление всех файлов картинок по пути без расширения, для очистки картинок отличающимся расширением от загруженной
            $this->deleteAllFiles($aRef, $imageSetting);

            $original_mime = File::mimeType($tmp_file_name);

            $original_ext = $this->getExtension($original_mime);
            $original_file_name = $image_path.'.'.$original_ext;

            File::move($tmp_file_name, $original_file_name);
            chmod($original_file_name, 0644);
            $phway = false;
        }
        else
        {
            foreach (glob($image_path.'.*') as $file)
            {
                if(is_dir($file))
                {
                    continue;
                }

                $phway = false;
                $original_mime = File::mimeType($file);
                $original_ext = $this->getExtension($original_mime);
                $original_file_name = $image_path.'.'.$original_ext;

                //Переименуем, если расширение не соответствует типу
                if($file !== $original_file_name)
                {
                    rename($file, $original_file_name);
                }

                break;
            }
        }

        //Ничего не нашли и не переместили, подставляем плэйсхолдер заданных параметров
        if($phway)
        {
            $width = $imageSetting->getWidth();
            $height = $imageSetting->getHeight();

            $original_file_path = $this->phAgent->getPh(
                $width,
                $height,
                $imageSetting->getColor()
            );

            $original_mime = MimeType::JPEG;
        }
        else
        {
            $originalImage = Image::make($original_file_name);

            $width = $originalImage->getWidth();
            $height = $originalImage->getHeight();

            $original_file_path = $this->pathResolver->getImagePath().'/'.$image_prefix.'.'.$original_ext;
        }

        $user_attrs['link'] = $original_file_path;
        $user_attrs['width'] = $width;
        $user_attrs['height'] = $height;

        $user_attrs = array_merge($user_attrs, ['alt' => '']);

        $this->dbAgent->imageToDb($aRef, $imageSetting, $user_attrs);
        //--------------------------------------------------------------------------------------

        $resize_attrs = [];
        if(array_key_exists('resizes', $user_attrs))
        {
            $resize_attrs = $user_attrs['resizes'];
        }

        $crop_attrs = [];
        if(array_key_exists('crops', $user_attrs))
        {
            $crop_attrs = $user_attrs['crops'];
        }

        if($phway)
        {
            $this->makePhImageResizes($aRef, $imageSetting, $resize_attrs);
            $this->makePhImageCrops($aRef, $imageSetting, $crop_attrs);
        }
        else
        {
            //Удаление старых ресайзов и кропов
            $this->deleteGens($aRef, $imageSetting);

            $vector = $this->isVectorImage($original_mime);

            if($vector)
            {
                $this->makeSameImageResizes($aRef, $imageSetting, $original_file_name, $resize_attrs);
                $this->makeSameImageCrops($aRef, $imageSetting, $original_file_name, $crop_attrs);
            }
            else
            {
                $this->makeImageResizes($aRef, $imageSetting, $resize_attrs);
                $this->makeImageCrops($aRef, $imageSetting, $crop_attrs);
            }
        }

        //Удаление содержимого тэмпа
        if($update_flag)
        {
            $this->deleteAllFiles($aRef, $imageSetting, true);
        }

    }
}
