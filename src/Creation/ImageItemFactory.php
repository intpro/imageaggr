<?php

namespace Interpro\ImageAggr\Creation;

use Interpro\Core\Contracts\Taxonomy\Fields\OwnField;
use Interpro\Core\Contracts\Taxonomy\Types\BType;
use Interpro\Extractor\Contracts\Creation\CItemBuilder;
use Interpro\Extractor\Contracts\Creation\CollectionFactory;
use Interpro\ImageAggr\Fields\OriginalRefField;
use Interpro\ImageAggr\Fields\ResizeRefField;
use Interpro\ImageAggr\Collections\CropsCollection;
use Interpro\ImageAggr\Collections\ResizesCollection;
use Interpro\ImageAggr\Exception\ImageAggrException;
use Interpro\ImageAggr\Fields\ImageOwnField;
use Interpro\ImageAggr\Items\CropItem;
use Interpro\ImageAggr\Items\ImageAggrItem;
use Interpro\ImageAggr\Items\ImageItem;
use Interpro\ImageAggr\Items\ResizeItem;

/**
 * Значения ссылок проставляются вне этой фабрики
 * Class ImageItemFactory
 * @package Interpro\ImageAggr\Creation
 */
class ImageItemFactory
{
    private $collectionFactory;
    private $cItemBuilder;
    private $capGenerator;
    private $defs;

    public function __construct(CollectionFactory $collectionFactory, CItemBuilder $cItemBuilder, CapGenerator $capGenerator)
    {
        $this->collectionFactory = $collectionFactory;
        $this->cItemBuilder = $cItemBuilder;
        $this->capGenerator = $capGenerator;

        $this->defs = [
            'integer' => 0,
            'string' => '',
            'boolean' => false
        ];
    }

    /**
     * @param ImageAggrItem $owner
     * @param OwnField $ownMeta
     * @param mixed $value
     *
     * @return \Interpro\ImageAggr\Fields\ImageOwnField
     */
    private function createLocalCField(ImageAggrItem $owner, OwnField $ownMeta, $value)
    {
        $fieldType = $ownMeta->getFieldType();

        $scalarItem = $this->cItemBuilder->create($fieldType, $value);

        $newField = new ImageOwnField($owner, $ownMeta);
        $newField->setItem($scalarItem);

        return $newField;
    }

    private function checkAndGetData(array $data, $name, $type_name)
    {
        if(!is_string($name))
        {
            throw new ImageAggrException('Имя поля должно быть задано строкой, передано: '.gettype($name).'!');
        }

        if(!array_key_exists($type_name, $this->defs))
        {
            throw new ImageAggrException('Собственные поля картинки могут быть типа '.implode(',', $this->defs).', передано '.$type_name.'!');
        }

        if(array_key_exists($name, $data))
        {
            $value = $data[$name];
        }
        else
        {
            $value = $this->defs[$type_name];
        }

        if($type_name === 'integer')
        {
            $value = (int) $value;
        }
        elseif($type_name === 'string')
        {
            $value = (string) $value;
        }
        elseif($type_name === 'boolean')
        {
            $value = (bool) $value;
        }
        elseif(gettype($value) !== $type_name)
        {
            throw new ImageAggrException('Значение не соответствует заявленному типу: '.$value.'('.$type_name.')!');
        }

        return $value;
    }

    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Types\BType $type
     * @param array $data
     * @param bool $cap
     *
     * @return \Interpro\ImageAggr\Items\ResizeItem
     */
    public function createResize(BType $resizeType, array $data, $cap = false)
    {
        if($resizeType->getName() !== 'resize')
        {
            throw new ImageAggrException('Попытка создания ресайза с типом не равным resize ('.$resizeType->getName().')!');
        }

        $fields  = $this->collectionFactory->createFieldsCollection();
        $owns    = $this->collectionFactory->createOwnsCollection();
        $refs    = $this->collectionFactory->createRefsCollection();

        $resizeItem = new ResizeItem($resizeType, $fields, $owns, $refs, $cap);

        $metaName       = $resizeType->getOwn('name');
        $metaLink       = $resizeType->getOwn('link');
        $metaAlt        = $resizeType->getOwn('alt');
        $metaCacheIndex = $resizeType->getOwn('cache_index');
        $metaWidth      = $resizeType->getOwn('width');
        $metaHeight     = $resizeType->getOwn('height');

        $metaOriginal   = $resizeType->getRef('original');

        //Собственные поля
        $fieldName       = $this->createLocalCField($resizeItem, $metaName,       $this->checkAndGetData($data, 'name', 'string'));
        $fieldLink       = $this->createLocalCField($resizeItem, $metaLink,       $this->checkAndGetData($data, 'link', 'string'));
        $fieldAlt        = $this->createLocalCField($resizeItem, $metaAlt,        $this->checkAndGetData($data, 'alt', 'string'));
        $fieldCacheIndex = $this->createLocalCField($resizeItem, $metaCacheIndex, $this->checkAndGetData($data, 'cache_index', 'integer'));
        $fieldWidth      = $this->createLocalCField($resizeItem, $metaWidth,      $this->checkAndGetData($data, 'width', 'integer'));
        $fieldHeight     = $this->createLocalCField($resizeItem, $metaHeight,     $this->checkAndGetData($data, 'height', 'integer'));

        //Ссылки
        $resizeItem->setOwn($fieldName);   $resizeItem->setField($fieldName);
        $resizeItem->setOwn($fieldLink);   $resizeItem->setField($fieldLink);
        $resizeItem->setOwn($fieldAlt);    $resizeItem->setField($fieldAlt);
        $resizeItem->setOwn($fieldCacheIndex); $resizeItem->setField($fieldCacheIndex);
        $resizeItem->setOwn($fieldWidth);  $resizeItem->setField($fieldWidth);
        $resizeItem->setOwn($fieldHeight); $resizeItem->setField($fieldHeight);

        //Ссылки
        $originalRef = new OriginalRefField($resizeItem, $metaOriginal);

        $resizeItem->setRef($originalRef); $resizeItem->setField($originalRef);

        return $resizeItem;
    }

    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Types\BType $cropType
     * @param array $data
     * @param bool $cap
     *
     * @return \Interpro\ImageAggr\Items\CropItem
     */
    public function createCrop(BType $cropType, array $data, $cap = false)
    {
        if($cropType->getName() !== 'crop')
        {
            throw new ImageAggrException('Попытка создания кропа с типом не равным crop ('.$cropType->getName().')!');
        }

        $fields  = $this->collectionFactory->createFieldsCollection();
        $owns    = $this->collectionFactory->createOwnsCollection();
        $refs    = $this->collectionFactory->createRefsCollection();

        $cropItem = new CropItem($cropType, $fields, $owns, $refs, $cap);

        $metaName       = $cropType->getOwn('name');
        $metaLink       = $cropType->getOwn('link');
        $metaAlt        = $cropType->getOwn('alt');
        $metaCacheIndex = $cropType->getOwn('cache_index');
        $metaWidth      = $cropType->getOwn('width');
        $metaHeight     = $cropType->getOwn('height');
        $metaX          = $cropType->getOwn('x');
        $metaY          = $cropType->getOwn('y');

        $metaOriginal   = $cropType->getRef('original');
        $metaMan        = $cropType->getRef('man');
        $metaTarget     = $cropType->getRef('target');

        $fieldName       = $this->createLocalCField($cropItem, $metaName,       $this->checkAndGetData($data, 'name', 'string'));
        $fieldLink       = $this->createLocalCField($cropItem, $metaLink,       $this->checkAndGetData($data, 'link', 'string'));
        $fieldAlt        = $this->createLocalCField($cropItem, $metaAlt,        $this->checkAndGetData($data, 'alt', 'string'));
        $fieldCacheIndex = $this->createLocalCField($cropItem, $metaCacheIndex, $this->checkAndGetData($data, 'cache_index', 'integer'));
        $fieldWidth      = $this->createLocalCField($cropItem, $metaWidth,      $this->checkAndGetData($data, 'width', 'integer'));
        $fieldHeight     = $this->createLocalCField($cropItem, $metaHeight,     $this->checkAndGetData($data, 'height', 'integer'));
        $fieldX          = $this->createLocalCField($cropItem, $metaX,          $this->checkAndGetData($data, 'x', 'integer'));
        $fieldY          = $this->createLocalCField($cropItem, $metaY,          $this->checkAndGetData($data, 'y', 'integer'));

        //Собственные поля
        $cropItem->setOwn($fieldName);  $cropItem->setField($fieldName);
        $cropItem->setOwn($fieldLink);  $cropItem->setField($fieldLink);
        $cropItem->setOwn($fieldAlt);   $cropItem->setField($fieldAlt);
        $cropItem->setOwn($fieldCacheIndex); $cropItem->setField($fieldCacheIndex);
        $cropItem->setOwn($fieldWidth);  $cropItem->setField($fieldWidth);
        $cropItem->setOwn($fieldHeight); $cropItem->setField($fieldHeight);
        $cropItem->setOwn($fieldX);      $cropItem->setField($fieldX);
        $cropItem->setOwn($fieldY);      $cropItem->setField($fieldY);

        //Ссылки
        $originalRef = new OriginalRefField($cropItem, $metaOriginal);
        $manRef      = new ResizeRefField($cropItem, $metaMan);
        $targetRef   = new ResizeRefField($cropItem, $metaTarget);

        $cropItem->setRef($originalRef); $cropItem->setField($originalRef);
        $cropItem->setRef($manRef);      $cropItem->setField($manRef);
        $cropItem->setRef($targetRef);   $cropItem->setField($targetRef);

        return $cropItem;
    }

    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Fields\OwnField $field
     * @param array $data
     * @param bool $cap
     *
     * @return \Interpro\ImageAggr\Items\ImageItem
     */
    public function createImage(OwnField $field, array $data, $cap = false)
    {
        $imageType = $field->getFieldType();

        if($imageType->getName() !== 'image')
        {
            throw new ImageAggrException('Попытка картинки для поля не картинки ('.$imageType->getName().')!');
        }

        $fields  = $this->collectionFactory->createFieldsCollection();
        $owns    = $this->collectionFactory->createOwnsCollection();
        $refs    = $this->collectionFactory->createRefsCollection();

        $resizes = new ResizesCollection($field, $this->capGenerator);
        $crops = new CropsCollection($field, $this->capGenerator);

        $imageItem = new ImageItem($field, $fields, $owns, $refs, $resizes, $crops, $cap);

        $resizes->setOriginal($imageItem);
        $crops->setOriginal($imageItem);


        $metaName       = $imageType->getOwn('name');
        $metaLink       = $imageType->getOwn('link');
        $metaAlt        = $imageType->getOwn('alt');
        $metaCacheIndex = $imageType->getOwn('cache_index');
        $metaWidth      = $imageType->getOwn('width');
        $metaHeight     = $imageType->getOwn('height');

        $fieldName       = $this->createLocalCField($imageItem, $metaName,       $this->checkAndGetData($data, 'name', 'string'));
        $fieldLink       = $this->createLocalCField($imageItem, $metaLink,       $this->checkAndGetData($data, 'link', 'string'));
        $fieldAlt        = $this->createLocalCField($imageItem, $metaAlt,        $this->checkAndGetData($data, 'alt', 'string'));
        $fieldCacheIndex = $this->createLocalCField($imageItem, $metaCacheIndex, $this->checkAndGetData($data, 'cache_index', 'integer'));
        $fieldWidth      = $this->createLocalCField($imageItem, $metaWidth,      $this->checkAndGetData($data, 'width', 'integer'));
        $fieldHeight     = $this->createLocalCField($imageItem, $metaHeight,     $this->checkAndGetData($data, 'height', 'integer'));

        //Собственные поля
        $imageItem->setOwn($fieldName);   $imageItem->setField($fieldName);
        $imageItem->setOwn($fieldLink);   $imageItem->setField($fieldLink);
        $imageItem->setOwn($fieldAlt);    $imageItem->setField($fieldAlt);
        $imageItem->setOwn($fieldCacheIndex); $imageItem->setField($fieldCacheIndex);
        $imageItem->setOwn($fieldWidth);  $imageItem->setField($fieldWidth);
        $imageItem->setOwn($fieldHeight); $imageItem->setField($fieldHeight);

        return $imageItem;
    }

}
