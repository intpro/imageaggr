<?php

namespace Interpro\ImageAggr\Db;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\Core\Contracts\Taxonomy\Fields\OwnField;
use Interpro\Core\Contracts\Taxonomy\Types\AType;
use Interpro\Core\Contracts\Taxonomy\Types\BType;
use Interpro\Core\Taxonomy\Enum\TypeRank;
use Interpro\Extractor\Contracts\Db\BMapper;
use Interpro\Extractor\Contracts\Selection\SelectionUnit;
use Interpro\Extractor\Contracts\Selection\Tuner;
use Interpro\ImageAggr\Collections\MapImageCollection;
use Interpro\ImageAggr\Creation\CapGenerator;
use Interpro\ImageAggr\Creation\ImageItemFactory;
use Interpro\Core\Helpers;

class ImageBMapper implements BMapper
{
    private $itemFactory;
    private $imageQuerier;
    private $tuner;
    private $capGenerator;
    private $units = [];

    public function __construct(ImageItemFactory $itemFactory, CapGenerator $capGenerator, ImageQuerier $imageQuerier, Tuner $tuner)
    {
        $this->itemFactory  = $itemFactory;
        $this->imageQuerier = $imageQuerier;
        $this->tuner        = $tuner;
        $this->capGenerator = $capGenerator;
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->units = [];
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return 'imageaggr';
    }

    private function createImage(OwnField $imageField, array $item_array)
    {
        $imageItem = $this->itemFactory->createImage($imageField, $item_array);

        return $imageItem;
    }

    private function createCrop(BType $cropType, array $crop_arr)
    {
        $cropItem = $this->itemFactory->createCrop($cropType, $crop_arr);

        return $cropItem;
    }

    private function createResize(BType $resizeType, array $resize_arr)
    {
        $resizeItem = $this->itemFactory->createResize($resizeType, $resize_arr);

        return $resizeItem;
    }

    /**
     * @param AType $ownerType
     * @param $images_result
     * @param $resizes_result
     * @param $crops_result
     *
     * @return MapImageCollection
     */
    private function resultsToCollection(AType $ownerType, $images_result, $resizes_result, $crops_result)
    {
        $collection = new MapImageCollection($this->capGenerator);

        $gens = [];

        foreach($images_result as $item_array)
        {
            $field_name = $item_array['name'];

            if($ownerType->fieldExist($field_name))
            {
                $imageField = $ownerType->getField($field_name);

                $imageItem = $this->createImage($imageField, $item_array);

                $ref = new \Interpro\Core\Ref\ARef($ownerType, $item_array['entity_id']);

                $collection->addItem($ref, $field_name, $imageItem);

                $key = $item_array['name'].'_'.$item_array['entity_id'];
                $gens[$key] = ['resizes' => [], 'crops' => [], 'item' => $imageItem, 'name' => $item_array['name']];
            }
        }

        //-----------------------------------------------------------

        foreach($resizes_result as $resize_arr)
        {
            $key = $resize_arr['image_name'].'_'.$resize_arr['entity_id'];

            if(array_key_exists($key, $gens)) //Записи без соответствия картинке отметаем
            {
                $gens[$key]['resizes'][$resize_arr['name']] = $resize_arr;
            }
        }

        foreach($crops_result as $crop_arr)
        {
            $key = $crop_arr['image_name'].'_'.$crop_arr['entity_id'];

            if(array_key_exists($key, $gens)) //Записи без соответствия картинке отметаем
            {
                $gens[$key]['crops'][$crop_arr['name']] = $crop_arr;
            }
        }
        //-----------------------------------------------------------

        foreach($gens as $gen_arr)
        {
            $image_name = $gen_arr['name'];

            if($ownerType->fieldExist($image_name))
            {
                $imageField = $ownerType->getField($image_name);

                //Костыль, чтобы не заводить экземпляр таксономии в этот класс
                $imageType = $imageField->getFieldType();
                $subs = $imageType->getSubs('original');
                $resizeType = $subs->getSub('resize');
                $cropType = $subs->getSub('crop');
                //--------------------------------------

                $resizes = $gen_arr['resizes'];
                $crops = $gen_arr['crops'];
                $imageItem = $gen_arr['item'];

                foreach($resizes as $resize_arr)
                {
                    $resizeItem = $this->createResize($resizeType, $resize_arr);

                    $resizeItem->getRef('original')->setItem($imageItem);

                    $imageItem->addResize($resizeItem);
                }

                foreach($crops as $crop_arr)
                {
                    $cropItem = $this->createCrop($cropType, $crop_arr);

                    $cropItem->getRef('original')->setItem($imageItem);
                    $cropItem->getRef('man')->setItem($imageItem->getResize($crop_arr['man_name']));
                    $cropItem->getRef('target')->setItem($imageItem->getResize($crop_arr['target_name']));

                    $imageItem->addCrop($cropItem);
                }
            }
        }

        return $collection;
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     * @param bool $asUnitMember
     *
     * @return \Interpro\Extractor\Contracts\Collections\MapBCollection
     */
    public function getByRef(ARef $ref, $asUnitMember = false)
    {
        $ownerType = $ref->getType();
        $type_name = $ownerType->getName();
        $typeRank = $ownerType->getRank();

        $key = $type_name.'_'.$ref->getId();

        if($typeRank === TypeRank::GROUP and $asUnitMember)
        {
            $selectionUnit = $this->tuner->getSelection($type_name, 'group');

            return $this->select($selectionUnit);
        }

        if(array_key_exists($key, $this->units))
        {
            return $this->units[$key];
        }

        $imagesQuery = $this->imageQuerier->selectImagesByRef($ref);
        $resizesQuery = $this->imageQuerier->selectResizesByRef($ref);
        $cropsQuery = $this->imageQuerier->selectCropsByRef($ref);

        $images_result = Helpers::laravel_db_result_to_array($imagesQuery->get());
        $resizes_result = Helpers::laravel_db_result_to_array($resizesQuery->get());
        $crops_result = Helpers::laravel_db_result_to_array($cropsQuery->get());

        $collection = $this->resultsToCollection($ownerType, $images_result, $resizes_result, $crops_result);

        $this->units[$key] = $collection;

        return $collection;
    }

    /**
     * @param \Interpro\Extractor\Contracts\Selection\SelectionUnit $selectionUnit
     *
     * @return \Interpro\Extractor\Contracts\Collections\MapBCollection
     */
    public function select(SelectionUnit $selectionUnit)
    {
        $ownerType = $selectionUnit->getType();

        $unit_number = $selectionUnit->getNumber();
        $key = 'unit_'.$unit_number;

        if(array_key_exists($key, $this->units))
        {
            return $this->units[$key];
        }

        //----------------------------------------------------------
        $imagesQuery = $this->imageQuerier->selectImagesByUnit($selectionUnit);
        $resizesQuery = $this->imageQuerier->selectResizesByUnit($selectionUnit);
        $cropsQuery = $this->imageQuerier->selectCropsByUnit($selectionUnit);

        $images_result = Helpers::laravel_db_result_to_array($imagesQuery->get());
        $resizes_result = Helpers::laravel_db_result_to_array($resizesQuery->get());
        $crops_result = Helpers::laravel_db_result_to_array($cropsQuery->get());

        $collection = $this->resultsToCollection($ownerType, $images_result, $resizes_result, $crops_result);

        $this->units[$key] = $collection;

        return $collection;
    }

}
