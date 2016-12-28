<?php

namespace Interpro\ImageAggr\Operation;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Operation\InitOperation as InitOperationInterface;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting;

class InitOperation extends Operation implements InitOperationInterface
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
        $this->checkOwner($aRef);

        //Не обращая внимание на то что хранится в папке оригинала проставляем везде плейсхолдеры
        //Если в БД совпали по ключам записи для картинки - сохраняем значения атрибутов неизменными

        $original_file_path = $this->phAgent->getPh(
                $imageSetting->getWidth(),
                $imageSetting->getHeight(),
                $imageSetting->getColor()
            );

        $user_attrs['link'] = $original_file_path;

        $this->dbAgent->imageToDb($aRef, $imageSetting, $user_attrs, true);

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

        $this->makePhImageResizes($aRef, $imageSetting, $resize_attrs, true);
        $this->makePhImageCrops($aRef, $imageSetting, $crop_attrs, true);
    }

}
