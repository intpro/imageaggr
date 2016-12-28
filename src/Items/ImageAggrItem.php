<?php

namespace Interpro\ImageAggr\Items;

use Interpro\Core\Contracts\Taxonomy\Types\BType;
use Interpro\Core\Taxonomy\Enum\TypeMode;
use Interpro\Extractor\Contracts\Collections\FieldsCollection as FieldsCollectionInterface;
use Interpro\Extractor\Contracts\Collections\OwnsCollection as OwnsCollectionInterface;
use Interpro\Extractor\Contracts\Collections\RefsCollection as RefsCollectionInterface;
use Interpro\Extractor\Contracts\Fields\Field;
use Interpro\Extractor\Contracts\Fields\OwnField;
use Interpro\Extractor\Contracts\Fields\RefField;
use Interpro\Extractor\Contracts\Items\AggrOwnItem as AggrOwnItemInterface;

abstract class ImageAggrItem implements AggrOwnItemInterface
{
    private $fields;
    private $refs;
    private $owns;
    private $type;
    private $cap;

    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Types\BType BType
     * @param \Interpro\Extractor\Contracts\Collections\FieldsCollection $fields
     * @param \Interpro\Extractor\Contracts\Collections\OwnsCollection $owns
     * @param \Interpro\Extractor\Contracts\Collections\RefsCollection $refs
     * @param bool $cap
     *
     * @return void
     */
    public function __construct(BType $type, FieldsCollectionInterface $fields, OwnsCollectionInterface $owns, RefsCollectionInterface $refs, $cap)
    {
        $this->type = $type;
        $this->fields = $fields;
        $this->refs = $refs;
        $this->owns = $owns;
        $this->cap = $cap;
    }



    /**
     * @return \Interpro\Extractor\Contracts\Fields\Field
     */
    public function setField(Field $field)
    {
        $this->fields->addField($field);
    }

    /**
     * @param string $ref_name
     *
     * @return \Interpro\Extractor\Contracts\Fields\RefField
     */
    public function setRef(RefField $refField)
    {
        $this->refs->addRef($refField);
    }

    /**
     * @param string $own_name
     *
     * @return \Interpro\Extractor\Contracts\Fields\OwnField
     */
    public function setOwn(OwnField $ownField)
    {
        $this->owns->addOwn($ownField);
    }

    /**
     * @return \Interpro\Extractor\Contracts\Fields\Field
     */
    public function getField($field_name)
    {
        return $this->fields->getFieldByName($field_name);
    }

    /**
     * @param string $ref_name
     *
     * @return \Interpro\Extractor\Contracts\Fields\RefField
     */
    public function getRef($ref_name)
    {
        return $this->refs->getRefByName($ref_name);
    }

    /**
     * @param string $own_name
     *
     * @return \Interpro\Extractor\Contracts\Fields\OwnField
     */
    public function getOwn($own_name)
    {
        return $this->owns->getOwnByName($own_name);
    }

    /**
     * @return \Interpro\Extractor\Contracts\Collections\FieldsCollection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return \Interpro\Extractor\Contracts\Collections\RefsCollection
     */
    public function getRefs()
    {
        return $this->refs;
    }

    /**
     * @return \Interpro\Extractor\Contracts\Collections\OwnsCollection
     */
    public function getOwns()
    {
        return $this->owns;
    }

    /**
     * @return \Interpro\Core\Contracts\Taxonomy\Types\BType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function cap()
    {
        return $this->cap;
    }

    private function prepareValue(Field $field)
    {
        $meta = $field->getFieldMeta();

        if($meta->getMode() === TypeMode::MODE_C)
        {
            return $field->getValue();
        }
        elseif($meta->getMode() === TypeMode::MODE_B)
        {
            return $field->getId();
        }

        return null;
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

            if($suffix === 'field')
            {
                $field = $this->getField($name);
                return $this->prepareValue($field);
            }
            elseif($suffix === 'own')
            {
                return $this->getOwn($name);
            }
            elseif($suffix === 'ref')
            {
                return $this->getRef($name)->getId();
            }
        }
        else
        {
            if($req_name === 'fields')
            {
                return $this->getFields();
            }
            elseif($req_name === 'owns')
            {
                return $this->getOwns();
            }
            elseif($req_name === 'refs')
            {
                return $this->getRefs();
            }
        }

        //Крайний случай - пытаемся получить поле (собственное или ссылку)
        $field = $this->getField($req_name);
        return $this->prepareValue($field);
    }

}
