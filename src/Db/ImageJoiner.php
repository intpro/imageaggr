<?php

namespace Interpro\ImageAggr\Db;

use Illuminate\Support\Facades\DB;
use Interpro\Core\Contracts\Taxonomy\Fields\Field;
use Interpro\Core\Taxonomy\Enum\TypeMode;
use Interpro\Extractor\Contracts\Db\Joiner;
use Interpro\Extractor\Db\QueryBuilder;
use Interpro\ImageAggr\Exception\ImageAggrException;

class ImageJoiner implements Joiner
{
    /**
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @param \Interpro\Core\Contracts\Taxonomy\Fields\Field $field
     * @param array $join_array
     *
     * @return \Interpro\Extractor\Db\QueryBuilder
     */
    public function joinByField(Field $field, $join_array)
    {
        $fieldType = $field->getFieldType();
        $type_name = $fieldType->getName();
        $field_name = $field->getName();
        $mode = $fieldType->getMode();

        $image_table_fields = ['id', 'name', 'entity_name', 'entity_id', 'link', 'alt', 'cache_index', 'width', 'height'];

        if($type_name !== 'image' or $mode !== TypeMode::MODE_B)
        {
            throw new ImageAggrException('Соединитель предназначен для соединения с основным запросом поля типа images(B), передано: '.$type_name.'('.$mode.')!');
        }

        $join_q = new QueryBuilder(DB::table('images'));

        $join_q->addSelect('images.entity_name');
        $join_q->addSelect('images.entity_id');
        $join_q->whereRaw('images.name = "'.$field_name.'"');


        //Если в продолжения пути нет, то $field_name и есть нужное поле
        foreach($join_array['sub_levels'] as $levelx_field_name => $sub_array)
        {
            if(in_array($levelx_field_name, $image_table_fields))
            {
                $join_q->addSelect('images.'.$levelx_field_name.' as '.$sub_array['full_field_names'][0]);//Законцовка - в массиве только одно поле x_..x_id
            }
            elseif($levelx_field_name === 'crops' or $levelx_field_name === 'resizes')
            {
                foreach($sub_array['sub_levels'] as $gen_name => $crop_array)
                {
                    $sub_q = $this->joinByGen($levelx_field_name, $gen_name, $crop_array);

                    $join_q->leftJoin(DB::raw('('.$sub_q->toSql().') AS '.$gen_name.'_table'), function($join) use ($gen_name)
                    {
                        $join->on('images.entity_name', '=', $gen_name.'_table.entity_name');
                        $join->on('images.entity_id',   '=', $gen_name.'_table.entity_id');
                    });

                    foreach($crop_array['full_field_names'] as $full_field_name)
                    {
                        $join_q->addSelect($full_field_name);//Из $sub_q пришли поля с именами, добавляем все в текущую выборку
                    }
                }
            }
            else
            {
                throw new ImageAggrException('Соединение в целях сортировки или отбора возможно только по следующим полям картинки: '.implode(',', $image_table_fields).', генерациям crops или resizes, передано '.$levelx_field_name.'!');
            }
        }

        return $join_q;
    }

    private function joinByGen($table, $resize_name, $join_array)
    {
        $join_q = new QueryBuilder(DB::table($table));

        $join_q->addSelect($table.'.entity_name');
        $join_q->addSelect($table.'.entity_id');
        $join_q->whereRaw($table.'.image_name = "'.$resize_name.'"');

        $table_fields = ['id', 'name', 'entity_name', 'entity_id', 'image_name', 'man_name', 'target_name', 'link', 'alt', 'cache_index', 'width', 'height', 'x', 'y'];

        foreach($join_array['sub_levels'] as $levelx_field_name => $sub_array)
        {
            if(in_array($levelx_field_name, $table_fields))
            {
                $join_q->addSelect($table.'.'.$levelx_field_name.' as '.$sub_array['full_field_names'][0]);
            }
        }

        return $join_q;
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return 'imageaggr';
    }

}
