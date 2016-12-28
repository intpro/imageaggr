<?php

namespace Interpro\ImageAggr\Contracts\Db;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\ImageAggr\Contracts\Settings\CropSetting as CropSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\ImageSetting as ImageSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\ResizeSetting as ResizeSettingInterface;

interface ImageAggrDbAgent
{

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     */
    public function ownerExist(ARef $aRef);

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     *
     * @return array
     */
    public function getCropsCoordinates(ARef $aRef, ImageSettingInterface $imageSetting);

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param array $attrs
     *
     * @return void
     */
    public function imageToDb(ARef $aRef, ImageSettingInterface $imageSetting, $attrs = [], $init = false);

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\ResizeSetting $resizeSetting
     * @param array $attrs
     *
     * @return void
     */
    public function resizeToDb(ARef $aRef, ImageSettingInterface $imageSetting, ResizeSettingInterface $resizeSetting, $attrs = [], $init = false);

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\CropSetting $cropSetting
     * @param array $attrs
     *
     * @return void
     */
    public function cropToDb(ARef $aRef, ImageSettingInterface $imageSetting, CropSettingInterface $cropSetting, $attrs = [], $init = false);

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     *
     * @return void
     */
    public function deleteImage(ARef $aRef, ImageSettingInterface $imageSetting);

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\ResizeSetting $resizeSetting
     *
     * @return void
     */
    public function deleteResize(ARef $aRef, ImageSettingInterface $imageSetting, ResizeSettingInterface $resizeSetting);

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     * @param \Interpro\ImageAggr\Contracts\Settings\ImageSetting $imageSetting
     * @param \Interpro\ImageAggr\Contracts\Settings\CropSetting $cropSetting
     *
     * @return void
     */
    public function deleteCrop(ARef $aRef, ImageSettingInterface $imageSetting, CropSettingInterface $cropSetting);

}
