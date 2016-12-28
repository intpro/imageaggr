<?php

namespace Interpro\ImageAggr\Settings;

use Interpro\ImageAggr\Contracts\Settings\GenSettingsSetFactory as GenSettingsSetFactoryInterface;
use Interpro\ImageAggr\Contracts\Settings\ImageSettingsSetFactory as ImageSettingsSetFactoryInterface;

use Interpro\ImageAggr\Settings\Collection\ImageSettingsSet;
use Interpro\Core\Contracts\Taxonomy\Taxonomy as TaxonomyInterface;

class ImageSettingsSetFactory implements ImageSettingsSetFactoryInterface
{
    private $images_config;
    private $genSettingsSetFactory;
    private $taxonomy;
    private $image_settings;

    /**
     * @param array $images_config
     * @param \Interpro\Core\Contracts\Taxonomy\Taxonomy $taxonomy
     * @param \Interpro\ImageAggr\Contracts\Settings\GenSettingsSetFactory $genSettingsSetFactory
     *
     * @return void
     */
    public function __construct(TaxonomyInterface $taxonomy, array $images_config, GenSettingsSetFactoryInterface $genSettingsSetFactory)
    {
        $this->images_config         = $images_config;
        $this->genSettingsSetFactory = $genSettingsSetFactory;
        $this->taxonomy              = $taxonomy;
        $this->image_settings        = [];
    }

    private function createImageSetting($owner_type, $field_name)
    {
        $image_key = $owner_type.'.'.$field_name;

        if(array_key_exists($image_key, $this->image_settings))
        {
            return $this->image_settings[$image_key];
        }

        $color = '#808080';
        $width = 400;
        $height = 400;
        $resizes = [];
        $crops = [];

        if(array_key_exists($image_key, $this->images_config))
        {
            $attrs = $this->images_config[$image_key];

            if(array_key_exists('color', $attrs))
            {
                $color = $attrs['color'];
            }

            if(array_key_exists('width', $attrs))
            {
                $width = $attrs['width'];
            }

            if(array_key_exists('height', $attrs))
            {
                $height = $attrs['height'];
            }

            if(array_key_exists('resizes', $attrs))
            {
                $resizes = $attrs['resizes'];
            }

            if(array_key_exists('crops', $attrs))
            {
                $crops = $attrs['crops'];
            }
        }

        $resizeSettingsSet = $this->genSettingsSetFactory->createResizeSettingsSet($resizes);
        $cropSettingsSet = $this->genSettingsSetFactory->createCropSettingsSet($crops);

        $imageSetting = new ImageSetting($owner_type, $field_name, $resizeSettingsSet, $cropSettingsSet, $color, $width, $height);

        //Закэшируем, чтобы экземпляры не повторялись
        $this->image_settings[$image_key] = $imageSetting;

        return $imageSetting;
    }

    /**
     * @param $owner_name
     *
     * @return \Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet
     */
    public function create($owner_name = 'all')
    {
        //Пытаемся получить из $images_config, если нет, то создаем без конфига с одним original линком
        //Получаем массив использования типа картинки
        $imageType = $this->taxonomy->getType('image');

        $using = $imageType->getUsing();

        $imageSettings = [];

        if($owner_name === 'all')
        {
            foreach($using as $owner_type_name => $ownerType)
            {
                $fields = $ownerType->getOwns()->getTyped('image');

                foreach($fields as $field_name => $field)
                {
                    $imageSetting = $this->createImageSetting($owner_type_name, $field_name);
                    $imageSettings[$owner_type_name.'.'.$field_name] = $imageSetting;
                }
            }
        }
        else
        {
            //Проверим тип хозяина на существование
            $ownerType = $this->taxonomy->getType($owner_name);

            $owner_type_name = $ownerType->getName();

            $fields = $ownerType->getOwns()->getTyped('image');

            foreach($fields as $field_name => $field)
            {
                $imageSetting = $this->createImageSetting($owner_type_name, $field_name);
                $imageSettings[$owner_type_name.'.'.$field_name] = $imageSetting;
            }
        }

        $imageSettingsSet = new ImageSettingsSet($imageSettings);

        return $imageSettingsSet;
    }
}
