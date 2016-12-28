<?php

namespace Interpro\ImageAggr\Items;

use Interpro\Core\Contracts\Taxonomy\Fields\OwnField;
use Interpro\Extractor\Contracts\Collections\FieldsCollection as FieldsCollectionInterface;
use Interpro\Extractor\Contracts\Collections\OwnsCollection as OwnsCollectionInterface;
use Interpro\Extractor\Contracts\Collections\RefsCollection as RefsCollectionInterface;
use Interpro\ImageAggr\Collections\CropsCollection;
use Interpro\ImageAggr\Collections\ResizesCollection;

class ImageItem extends ImageAggrItem
{
    private $resizes;
    private $crops;

    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Fields\OwnField $field
     * @param \Interpro\Extractor\Contracts\Collections\FieldsCollection $fields
     * @param \Interpro\Extractor\Contracts\Collections\OwnsCollection $owns
     * @param \Interpro\Extractor\Contracts\Collections\RefsCollection $refs
     * @param \Interpro\ImageAggr\Collections\ResizesCollection $resizesCollection
     * @param \Interpro\ImageAggr\Collections\CropsCollection $cropsCollection
     * @param bool $cap
     *
     * @return void
     */
    public function __construct(OwnField $field, FieldsCollectionInterface $fields, OwnsCollectionInterface $owns, RefsCollectionInterface $refs, ResizesCollection $resizesCollection, CropsCollection $cropsCollection, $cap = false)
    {
        $type = $field->getFieldType();

        parent::__construct($type, $fields, $owns, $refs, $cap);

        $this->resizes = $resizesCollection;
        $this->crops = $cropsCollection;
    }

    public function addResize(ResizeItem $resize)
    {
        $this->resizes->addItem($resize);
    }

    public function addCrop(CropItem $crop)
    {
        $this->crops->addItem($crop);
    }

    public function getCrops()
    {
        return $this->crops;
    }

    public function getResize($resize_name)
    {
        return $this->resizes->getItem($resize_name);
    }

    public function getCrop($crop_name)
    {
        return $this->crops->getItem($crop_name);
    }

    public function getResizes()
    {
        return $this->resizes;
    }

    /**
     * @param string $req_name
     *
     * @return mixed
     */
    public function __get($req_name)
    {
        $suffix_pos = strripos($req_name, '_');

        if($suffix_pos)
        {
            $suffix = substr($req_name, $suffix_pos+1);
            $name = substr($req_name, 0, $suffix_pos);

            if($suffix === 'resize')
            {
                return $this->getResize($name);
            }
            elseif($suffix === 'crop')
            {
                return $this->getCrop($name);
            }
        }
        else
        {
            if($req_name === 'resizes')
            {
                return $this->getResizes();
            }
            elseif($req_name === 'crops')
            {
                return $this->getCrops();
            }
        }

        return parent::__get($req_name);
    }

}
