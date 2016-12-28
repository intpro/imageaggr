<?php

namespace Interpro\ImageAggr;

use Interpro\Core\Contracts\Taxonomy\TypesForecastList;
use Interpro\Core\Taxonomy\Enum\TypeRank;
use Interpro\Core\Taxonomy\Manifests\ATypeManifest;
use Interpro\ImageAggr\Exception\ImageAggrException;

class ModImagesConfigInterpreter
{
    private $forecastList;

    public function __construct(TypesForecastList $forecastList)
    {
        $this->forecastList = $forecastList;
    }

    /**
     * @param array $config
     *
     * @return \Interpro\Core\Taxonomy\Manifests\ATypeManifest
     */
    public function interpretConfig(array $config)
    {
        $owns = [
            'name' => 'string',
            'title' => 'string',
        ];

        //Добавляем имена картинок
        foreach($config as $image_name)
        {
            if(!is_string($image_name))
            {
                throw new ImageAggrException('Имена в конфигурации блока модифицирования картинок должны быть заданы строкой!');
            }
            else
            {
                $owns[$image_name] = 'image';
            }
        }

        return new ATypeManifest('modimages', 'modimages', TypeRank::BLOCK, $owns, []);
    }

}
