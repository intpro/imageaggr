<?php

namespace Interpro\ImageAggr\Contracts\Operation;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UploadOperation
{
    /**
     * @param \Interpro\Core\Contracts\Ref\ARef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     *
     * @return void
     */
    public function execute(ARef $aRef, ImageSetting $imageSetting, UploadedFile $uploadedFile);
}
