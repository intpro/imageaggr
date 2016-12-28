<?php

namespace Interpro\ImageAggr\Items;

use Interpro\Core\Contracts\Named;
use Interpro\Core\Contracts\Taxonomy\Types\BType;
use Interpro\Extractor\Contracts\Collections\FieldsCollection as FieldsCollectionInterface;
use Interpro\Extractor\Contracts\Collections\OwnsCollection as OwnsCollectionInterface;
use Interpro\Extractor\Contracts\Collections\RefsCollection as RefsCollectionInterface;

class CropItem extends ImageAggrItem implements Named
{
    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Types\BType BType
     * @param \Interpro\Extractor\Contracts\Collections\FieldsCollection $fields
     * @param \Interpro\Extractor\Contracts\Collections\OwnsCollection $owns
     * @param \Interpro\Extractor\Contracts\Collections\RefsCollection $refs
     * @param bool $cap
     *
     * @return void
     */
    public function __construct(BType $type, FieldsCollectionInterface $fields, OwnsCollectionInterface $owns, RefsCollectionInterface $refs, $cap = false)
    {
        parent::__construct($type, $fields, $owns, $refs, $cap);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
