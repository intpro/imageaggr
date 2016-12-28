<?php

namespace Interpro\ImageAggr\Db;

use Illuminate\Support\Facades\DB;
use Interpro\Core\Contracts\Ref\ARef;
use Interpro\Extractor\Contracts\Selection\SelectionUnit;
use Interpro\Extractor\Db\QueryBuilder;

class ImageQuerier
{
    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     * @param string $table
     *
     * @return \Interpro\Extractor\Db\QueryBuilder
     */
    private function selectByRef($table, ARef $ref)
    {
        $type  = $ref->getType();
        $type_name = $type->getName();
        $id = $ref->getId();

        $query = new QueryBuilder(DB::table($table));
        $query->where($table.'.entity_name', '=', $type_name);

        if($id > 0)
        {
            $query->where($table.'.entity_id', '=', $id);
        }

        return $query;
    }

    /**
     * @param SelectionUnit $selectionUnit
     * @param string $table
     *
     * @return \Interpro\Extractor\Db\QueryBuilder
     */
    public function selectByUnit($table, SelectionUnit $selectionUnit)
    {
        $type  = $selectionUnit->getType();
        $entity_name    = $type->getName();

        //В главном запросе можно пользоваться биндингом, а в подзапросах нельзя, так как порядок параметров будет сбиваться параметрами подзапросов
        $query = new QueryBuilder(DB::table($table));
        $query->where($table.'.entity_name', '=', $entity_name);

        if($selectionUnit->closeToIdSet())
        {
            $query->whereIn($table.'.entity_id', $selectionUnit->getIdSet());
        }

        return $query;
    }

    /**
     * @param SelectionUnit $selectionUnit
     * @param string $table
     *
     * @return \Interpro\Extractor\Db\QueryBuilder
     */
    public function selectImagesByUnit(SelectionUnit $selectionUnit)
    {
        return $this->selectByUnit('images', $selectionUnit);
    }

    /**
     * @param SelectionUnit $selectionUnit
     * @param string $table
     *
     * @return \Interpro\Extractor\Db\QueryBuilder
     */
    public function selectResizesByUnit(SelectionUnit $selectionUnit)
    {
        return $this->selectByUnit('resizes', $selectionUnit);
    }

    /**
     * @param SelectionUnit $selectionUnit
     * @param string $table
     *
     * @return \Interpro\Extractor\Db\QueryBuilder
     */
    public function selectCropsByUnit(SelectionUnit $selectionUnit)
    {
        return $this->selectByUnit('crops', $selectionUnit);
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     *
     * @return \Interpro\Extractor\Db\QueryBuilder
     */
    public function selectImagesByRef(ARef $ref)
    {
        return $this->selectByRef('images', $ref);
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     *
     * @return \Interpro\Extractor\Db\QueryBuilder
     */
    public function selectResizesByRef(ARef $ref)
    {
        return $this->selectByRef('resizes', $ref);
    }

    /**
     * @param \Interpro\Core\Contracts\Ref\ARef $ref
     *
     * @return \Interpro\Extractor\Db\QueryBuilder
     */
    public function selectCropsByRef(ARef $ref)
    {
        return $this->selectByRef('crops', $ref);
    }

}
