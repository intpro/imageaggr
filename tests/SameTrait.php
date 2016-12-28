<?php

namespace Interpro\ImageAggr\Test;

use Interpro\Core\Contracts\Taxonomy\Taxonomy;
use Interpro\Core\Taxonomy\Factory\TaxonomyFactory;
use Interpro\Core\Taxonomy\Manifests\ATypeManifest;
use Interpro\Core\Taxonomy\Manifests\BTypeManifest;
use Interpro\Core\Taxonomy\Manifests\CTypeManifest;
use Interpro\ImageAggr\Settings\GenSettingsSetFactory;
use Interpro\ImageAggr\Settings\ImageSettingsSetFactory;

trait SameTrait
{
    private function getSettings(Taxonomy $taxonomy)
    {
        $modImagesType = $taxonomy->getBlock('modimages');

        $resize_config = [
            'res100x100' => [
                'width' => 100,
                'height' => 100,
                'when_upload' => true
            ],

            'res400' => [
                'width' => 400, 'height' => null,
            ],

            'res400mod' => [
                'width' => 400, 'height' => null,
                'mods' => [
                    'wmark' => ['image_key' => 'wmimage.crop.crop50x50', 'position' => 'top-left', 'x' => 10, 'y' => 10],
                    'mask' => ['image_key' => 'maskimage.crop.crop40x40', 'position' => 'bottom-right', 'x' => 10, 'y' => 10]
                ]
            ],

            'res800' => [
                'width' => 800, 'height' => null
            ],

            'res1000' => [
                'width' => 1000
            ],

            'resEq' => [
                'mods' => [
                    'wmark' => ['image_key' => 'wmimage.crop.crop50x50', 'position' => 'top-left', 'x' => 10, 'y' => 10],
                    'mask' => ['image_key' => 'maskimage.crop.crop40x40', 'position' => 'bottom-right', 'x' => 10, 'y' => 10]
                ]
            ]
        ];

        $crop_config = [
            'crop400x300' => ['width' => 400, 'height' => 300, 'man' => 'res400', 'target' => 'res800'],
            'crop800x600' => ['width' => 800, 'height' => 600, 'man' => 'res400', 'target' => 'res800'],
            'crop40x40' => ['width' => 40, 'height' => 40, 'man' => 'res400', 'target' => 'res800'],
            'crop50x50' => ['width' => 50, 'height' => 50, 'man' => 'res400', 'target' => 'res800']
        ];

        $genSettingsSetFactory = new GenSettingsSetFactory($modImagesType, $resize_config, $crop_config);


        $image_config = [
            'modimages.wmimage' => [
                'resizes' => ['res100x100', 'res400'],
                'crops' => ['crop50x50']
            ],

            'modimages.maskimage' => [
                'resizes' => ['res100x100', 'res400'],
                'crops' => ['crop40x40']
            ],

            'test_block.foto_face' => [
                'resizes' => ['res100x100', 'res400', 'res400mod', 'res800', 'res1000', 'resEq'],
                'crops' => ['crop800x600', 'crop400x300']
            ],

            'test_block.foto_profile' => [
                'resizes' => ['res100x100', 'res400mod', 'res800'],
                'crops' => ['crop800x600']
            ],

            'group_session.foto_1' => [
                'resizes' => ['res100x100', 'res400mod', 'res800'],
                'crops' => ['crop800x600']
            ],

            'group_session.foto_2' => [
                'resizes' => ['res100x100', 'res400mod', 'res800'],
                'crops' => ['crop800x600']
            ],

            'group_session.foto_3' => [
                'resizes' => ['res100x100', 'res400mod', 'res800'],
                'crops' => ['crop800x600']
            ]
        ];

        $imageSettingsFactory = new ImageSettingsSetFactory($taxonomy, $image_config, $genSettingsSetFactory);

        $imageSettngs = $imageSettingsFactory->create();//Все

        return $imageSettngs;
    }

    public function getTaxonomy()
    {
        $family = 'qs';
        $name = 'test_block';
        $owns = ['annotation'=>'string', 'foto_face'=>'image', 'foto_profile'=>'image'];
        $manA1 = new ATypeManifest($family, $name, \Interpro\Core\Taxonomy\Enum\TypeRank::BLOCK, $owns, []);

        $family = 'qs';
        $name = 'group_session';
        $owns = ['descr'=>'string', 'foto_1'=>'image', 'foto_2'=>'image', 'foto_3'=>'image'];
        $refs = ['block_name'=>'test_block'];
        $manA2 = new ATypeManifest($family, $name, \Interpro\Core\Taxonomy\Enum\TypeRank::GROUP, $owns, $refs);

        $family = 'modimages';
        $name = 'modimages';
        $owns = ['maskimage'=>'image', 'wmimage'=>'image'];
        $manA3 = new ATypeManifest($family, $name, \Interpro\Core\Taxonomy\Enum\TypeRank::BLOCK, $owns, []);

        $family = 'scalar';
        $name = 'string';
        $manC1 = new \Interpro\Core\Taxonomy\Manifests\CTypeManifest($family, $name, [], []);

        $family = 'scalar';
        $name = 'int';
        $manC2 = new CTypeManifest($family, $name, [], []);

        $imageMan  = new BTypeManifest('imageaggr', 'image',
            ['name' => 'string'],
            []);

        $resizeMan = new BTypeManifest('imageaggr', 'resize',
            ['name' => 'string',
                'alt' => 'string',
                'link' => 'string',
                'width' => 'int',
                'height' => 'int',
                'cache_index' => 'int'],
            ['image' => 'image']);

        $cropMan   = new BTypeManifest('imageaggr', 'crop',
            ['name' => 'string',
                'alt' => 'string',
                'link' => 'string',
                'cache_index' => 'int',
                'x' => 'int',
                'y' => 'int',
                'width' => 'int',
                'height' => 'int'],
            ['image' => 'image',
                'man' => 'resize',
                'target' => 'crop']);

        $manifestsCollection = new \Interpro\Core\Taxonomy\Collections\ManifestsCollection();
        $manifestsCollection->addManifest($manA1);
        $manifestsCollection->addManifest($manA2);
        $manifestsCollection->addManifest($manA3);
        $manifestsCollection->addManifest($imageMan);
        $manifestsCollection->addManifest($resizeMan);
        $manifestsCollection->addManifest($cropMan);
        $manifestsCollection->addManifest($manC1);
        $manifestsCollection->addManifest($manC2);

        $taxonomyFactory = new TaxonomyFactory();

        $taxonomy = $taxonomyFactory->createTaxonomy($manifestsCollection);

        return $taxonomy;
    }

}
