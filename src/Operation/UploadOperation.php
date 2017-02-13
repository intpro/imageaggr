<?php

namespace Interpro\ImageAggr\Operation;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting;
use Interpro\ImageAggr\Exception\OperationException;
use Interpro\ImageAggr\Contracts\Settings\Enum\GenVariant;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Interpro\ImageAggr\Contracts\Operation\UploadOperation as UploadOperationInterface;

class UploadOperation extends Operation implements UploadOperationInterface
{
    /**
     * @param \Interpro\Core\Contracts\Ref\ARef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     *
     * @return void
     */
    public function execute(ARef $aRef, ImageSetting $imageSetting, UploadedFile $uploadedFile)
    {
        $owner_name = $aRef->getType()->getName();
        $owner_id = $aRef->getId();
        $image_name = $imageSetting->getName();

        $this->checkOwner($aRef);

        $exts = ['png', 'jpg', 'jpeg', 'gif', 'svg'];

        $tmp_dir = $this->pathResolver->getTmpDir();

        if (!is_writable($tmp_dir))
        {
            throw new OperationException('Временная дирректория картинок ('.$tmp_dir.') не доступна для записи!');
        }

        $ext = $uploadedFile->guessClientExtension();

        if(!in_array($ext, $exts))
        {
            throw new OperationException('Тип файла картинки может быть только '.implode(',', $exts).'!');
        }

        $image_name_ext = $this->getImagePrefix($owner_name, $owner_id, $image_name, GenVariant::NONE).'.'.$ext;

        $this->deleteAllFiles($aRef, $imageSetting, true);

        $uploadedFile->move(
            $tmp_dir,
            $image_name_ext
        );

        $tmp_path = $tmp_dir.'/'.$image_name_ext;

        chmod($tmp_path, 0644);

        //5й аргумент $tmp определяет, что будут сделаны только служебные ресайзы, например иконка для обновления в интерфесе загрузки
        $this->makeImageResizes($aRef, $imageSetting, [], true);
    }
}
