<?php

namespace Interpro\ImageAggr\Operation;

use Illuminate\Support\Facades\File;
use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Settings\Enum\GenVariant;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting;
use Interpro\ImageAggr\Exception\OperationException;
use Interpro\ImageAggr\Contracts\Operation\Enum\MimeType;
use Interpro\ImageAggr\Contracts\Operation\RefreshOperation as RefreshOperationInterface;

class RefreshOperation extends Operation implements RefreshOperationInterface
{
    /**
     * @param \Interpro\Core\Contracts\Ref\ARef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     *
     * @return void
     */
    public function execute(ARef $aRef, ImageSetting $imageSetting)
    {
        $owner_name = $aRef->getType()->getName();
        $owner_id = $aRef->getId();
        $image_name = $imageSetting->getName();

        $this->checkOwner($aRef);

        $images_dir = $this->pathResolver->getImageDir();

        if (!is_readable($images_dir))
        {
            throw new OperationException('Дирректория картинок ('.$images_dir.') недоступна для чтения!');
        }

        $image_prefix = $this->getImagePrefix($owner_name, $owner_id, $image_name, GenVariant::NONE);

        $image_path = $images_dir.'/'.$image_prefix;

        $phway = true;

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

        //Ничего не нашли, подставляем плэйсхолдер заданных параметров
        if($phway)
        {
            $original_file_path = $this->phAgent->getPh(
                $imageSetting->getWidth(),
                $imageSetting->getHeight(),
                $imageSetting->getColor()
            );

            $original_mime = MimeType::JPEG;
        }
        else
        {
            $original_file_path = $this->pathResolver->getImagePath().'/'.$image_prefix.'.'.$original_ext;
        }

        $this->dbAgent->imageToDb($aRef, $imageSetting, ['link' => $original_file_path]);
        //--------------------------------------------------------------------------------------

        if($phway)
        {
            $this->makePhImageResizes($aRef, $imageSetting);
            $this->makePhImageCrops($aRef, $imageSetting);
        }
        else
        {
            //Удаление старых ресайзов и кропов
            $this->deleteGens($aRef, $imageSetting);

            $vector = $this->isVectorImage($original_mime);

            if($vector)
            {
                $this->makeSameImageResizes($aRef, $imageSetting, $original_file_name);
                $this->makeSameImageCrops($aRef, $imageSetting, $original_file_name);
            }
            else
            {
                //В Save операции координаты будут приходить от пользователя, здесь из базы
                $crop_xy = $this->dbAgent->getCropsCoordinates($aRef, $imageSetting);

                $this->makeImageResizes($aRef, $imageSetting);
                $this->makeImageCrops($aRef, $imageSetting, $crop_xy);
            }
        }

    }
}
