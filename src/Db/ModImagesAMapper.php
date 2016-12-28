<?php

namespace Interpro\ImageAggr\Db;

use Interpro\Core\Contracts\Ref\ARef;
use Interpro\Core\Contracts\Taxonomy\Fields\OwnField;
use Interpro\Core\Taxonomy\Enum\TypeMode;
use Interpro\Core\Taxonomy\Enum\TypeRank;
use Interpro\Extractor\Contracts\Collections\MapBCollection;
use Interpro\Extractor\Contracts\Creation\CItemBuilder;
use Interpro\Extractor\Contracts\Creation\CollectionFactory;
use Interpro\Extractor\Contracts\Db\AMapper;
use Interpro\Extractor\Contracts\Db\MappersMediator;
use Interpro\Extractor\Items\AItem;
use Interpro\Extractor\Contracts\Selection\SelectionUnit;
use Interpro\Extractor\Fields\ABOwnField;
use Interpro\Extractor\Fields\ACOwnField;
use Interpro\Extractor\Items\BlockItem;
use Interpro\ImageAggr\Exception\ImageAggrException;

class ModImagesAMapper implements AMapper
{
    private $collectionFactory;
    private $cItemBuilder;
    private $mappersMediator;
    private $item = null;
    private $local_fields = ['name' => 'modimages', 'title' => 'mod images'];

    public function __construct(CollectionFactory $collectionFactory, CItemBuilder $cItemBuilder, MappersMediator $mappersMediator)
    {
        $this->collectionFactory = $collectionFactory;
        $this->cItemBuilder      = $cItemBuilder;
        $this->mappersMediator   = $mappersMediator;
    }

    private function local($field_name)
    {
        return array_key_exists($field_name, $this->local_fields);
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->item = null;
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return 'modimages';
    }

    /**
     * @param AItem $owner
     * @param OwnField $ownMeta
     * @param mixed $value
     *
     * @return \Interpro\Extractor\Fields\ACOwnField
     */
    private function createLocalCField(AItem $owner, OwnField $ownMeta, $value)
    {
        $fieldType = $ownMeta->getFieldType();

        $scalarItem = $this->cItemBuilder->create($fieldType, $value);

        $newField = new ACOwnField($owner, $ownMeta);
        $newField->setItem($scalarItem);

        return $newField;
    }

    private function createExternalBFieldByRef(AItem $owner, OwnField $ownMeta, MapBCollection $map)
    {
        $field_name = $ownMeta->getName();

        $ref = $owner->getSelfRef();

        $fieldItem = $map->getItem($ref, $field_name);
        $newField = new ABOwnField($owner, $ownMeta);
        $newField->setItem($fieldItem);

        return $newField;
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     * @param bool $asUnitMember
     *
     * @return \Interpro\Extractor\Contracts\Items\AItem
     */
    public function getByRef(ARef $ref, $asUnitMember = false)
    {
        if($this->item)
        {
            return $this->item;
        }

        $type  = $ref->getType();
        $rank = $type->getRank();
        $type_name = $type->getName();

        if($type_name !== 'modimages' or $rank !== TypeRank::BLOCK)
        {
            throw new ImageAggrException('Маппер предназначен для получения блока modimages(A), передано: '.$type_name.'('.$rank.')!');
        }

        $fields  = $this->collectionFactory->createFieldsCollection();
        $owns    = $this->collectionFactory->createOwnsCollection();
        $refs    = $this->collectionFactory->createRefsCollection();
        $subVars = $this->collectionFactory->createSubVarCollection($ref);

        $item = new BlockItem($ref, $fields, $owns, $refs, $subVars);

        //====================================================поля
        $ownsMetaCollection = $type->getOwns();

        foreach($ownsMetaCollection as $ownMeta)
        {
            $fieldType = $ownMeta->getFieldType();
            $fieldMode = $ownMeta->getMode();

            $field_name = $ownMeta->getName();

            if($this->local($field_name)) //все поля локального хранения - С типы
            {
                $newField = $this->createLocalCField($item, $ownMeta, $this->local_fields[$field_name]);
            }
            else
            {
                if($fieldMode === TypeMode::MODE_B)
                {
                    $mapper = $this->mappersMediator->getBMapper($fieldType->getFamily());
                    $map = $mapper->getByRef($ref);
                    $newField = $this->createExternalBFieldByRef($item, $ownMeta, $map);
                }
                elseif($fieldMode === TypeMode::MODE_C)
                {
                    throw new ImageAggrException('Попытка добавить внешнее (С)поле в блок картинок модификации, такая возможность не поддерживается!');
                }
                else
                {
                    throw new ImageAggrException('В типе '.$type_name.' обнаружено поле-собственность типа отличного от В или С: '.$field_name.'('.$fieldMode.')!');
                }
            }

            $item->setOwn($newField);
            $item->setField($newField);
        }

        $this->item = $item;

        return $item;
    }

    /**
     * @param \Interpro\Extractor\Contracts\Selection\SelectionUnit $selectionUnit
     *
     * @return \Interpro\Extractor\Contracts\Collections\MapGroupCollection
     */
    public function select(SelectionUnit $selectionUnit)
    {
        //Заглушка: возвращаем пустую коллекцию

        $type = $selectionUnit->getType();

        $mapCollection = $this->collectionFactory->createMapGroupCollection($type);

        return $mapCollection;
    }



}
