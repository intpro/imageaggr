<?php

namespace Interpro\ImageAggr\CommandAgents;

use Interpro\Core\Contracts\Taxonomy\Taxonomy;
use Interpro\Core\Ref\ARef;
use Interpro\Core\Taxonomy\Enum\TypeMode;
use Interpro\ImageAggr\Contracts\CommandAgents\OperationsAgent as OperationsAgentInterface;
use Interpro\ImageAggr\Contracts\Operation\CleanOperation;
use Interpro\ImageAggr\Contracts\Operation\CleanPhOperation;
use Interpro\ImageAggr\Contracts\Operation\CropOperation;
use Interpro\ImageAggr\Contracts\Operation\RefreshOperation;
use Interpro\ImageAggr\Contracts\Operation\UploadOperation;
use Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet;
use Interpro\ImageAggr\Exception\ImageAggrException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OperationsAgent implements OperationsAgentInterface
{
    private $taxonomy;
    private $clean;
    private $cleanPh;
    private $refresh;
    private $crop;
    private $upload;
    private $imageSettingsSet;

    public function __construct(Taxonomy $taxonomy,
                                ImageSettingsSet $imageSettingsSet,
                                CleanOperation $clean,
                                CleanPhOperation $cleanPh,
                                RefreshOperation $refresh,
                                CropOperation $crop,
                                UploadOperation $upload)
    {
        $this->taxonomy = $taxonomy;

        $this->clean   = $clean;
        $this->cleanPh = $cleanPh;
        $this->refresh = $refresh;
        $this->crop    = $crop;
        $this->upload  = $upload;
        $this->imageSettingsSet = $imageSettingsSet;
    }

    private function ownerNameControl($owner_name)
    {
        if(!is_string($owner_name))
        {
            throw new ImageAggrException('Название типа хозяина) должно быть задано строкой!');
        }
    }

    private function ownerIdControl($owner_id)
    {
        if(!is_int($owner_id))
        {
            throw new ImageAggrException('Id типа хозяина должно быть задано целым числом!');
        }
    }

    private function imageNameControl($image_name)
    {
        if(!is_string($image_name))
        {
            throw new ImageAggrException('Имя поля картинки должно быть задано строкой!');
        }
    }

    private function cropNameControl($crop_name)
    {
        if(!is_string($crop_name))
        {
            throw new ImageAggrException('Имя кропа картинки должно быть задано строкой!');
        }
    }

    private function makeOwnerRef($owner_name, $owner_id)
    {
        $this->ownerNameControl($owner_name);
        $this->ownerIdControl($owner_id);

        $type = $this->taxonomy->getType($owner_name);

        $typeMode = $type->getMode();

        if($typeMode !== TypeMode::MODE_A)
        {
            throw new ImageAggrException('Агент удаления может удалять только тип (A) уровня, передан тип:'.$type->getName().'('.$typeMode.')!');
        }

        $ref = new ARef($type, $owner_id);

        return $ref;
    }

    private function makeImageField($owner_name, $image_name)
    {
        $this->ownerNameControl($owner_name);
        $this->imageNameControl($image_name);

        $type = $this->taxonomy->getType($owner_name);

        $imageField = $type->getOwn($image_name);

        if($imageField->getFieldTypeName() !== 'image')
        {
            throw new ImageAggrException('Тип поля хозяина '.$owner_name.' = '.$imageField->getName().' вместо ожидаемого image.');
        }

        return $imageField;
    }

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     *
     * @return void
     */
    public function clean($owner_name, $owner_id, $image_name)
    {
        $aRef = $this->makeOwnerRef($owner_name, $owner_id);
        $imageField = $this->makeImageField($owner_name, $image_name);

        $imageSetting = $this->imageSettingsSet->getImage($imageField);

        $this->clean->execute($aRef, $imageSetting);
    }

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     *
     * @return void
     */
    public function cleanToPh($owner_name, $owner_id, $image_name)
    {
        $aRef = $this->makeOwnerRef($owner_name, $owner_id);
        $imageField = $this->makeImageField($owner_name, $image_name);

        $imageSetting = $this->imageSettingsSet->getImage($imageField);

        $this->cleanPh->execute($aRef, $imageSetting);
    }

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     *
     * @return void
     */
    public function refresh($owner_name, $owner_id, $image_name)
    {
        $aRef = $this->makeOwnerRef($owner_name, $owner_id);
        $imageField = $this->makeImageField($owner_name, $image_name);

        $imageSetting = $this->imageSettingsSet->getImage($imageField);

        $this->refresh->execute($aRef, $imageSetting);
    }

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     * @param string $crop_name
     * @param array $attrs
     *
     * @return void
     */
    public function crop($owner_name, $owner_id, $image_name, $crop_name, array $attrs)
    {
        $aRef = $this->makeOwnerRef($owner_name, $owner_id);
        $imageField = $this->makeImageField($owner_name, $image_name);

        $imageSetting = $this->imageSettingsSet->getImage($imageField);
        $cropSetting = $imageSetting->getCrops()->getCrop($crop_name);

        $this->crop->execute($aRef, $imageSetting, $cropSetting);
    }

    /**
     * @param $owner_name
     * @param $owner_id
     * @param $image_name
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     *
     * @return void
     */
    public function upload($owner_name, $owner_id, $image_name, UploadedFile $uploadedFile)
    {
        $aRef = $this->makeOwnerRef($owner_name, $owner_id);
        $imageField = $this->makeImageField($owner_name, $image_name);

        $imageSetting = $this->imageSettingsSet->getImage($imageField);

        $this->upload->execute($aRef, $imageSetting, $uploadedFile);
    }

}
