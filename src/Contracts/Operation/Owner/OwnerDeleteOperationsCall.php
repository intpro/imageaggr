<?php

namespace Interpro\ImageAggr\Contracts\Operation\Owner;

use Interpro\Core\Contracts\Ref\ARef;

interface OwnerDeleteOperationsCall
{
    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $aRef
     */
    public function execute(ARef $aRef);
}
