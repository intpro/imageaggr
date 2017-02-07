<?php

namespace Interpro\ImageAggr\Service;

use Interpro\Core\Contracts\Taxonomy\Taxonomy;
use Interpro\Core\Taxonomy\Enum\TypeRank;
use Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet;
use Interpro\ImageAggr\Model\Crop;
use Interpro\ImageAggr\Model\Image;
use Interpro\ImageAggr\Model\Resize;
use Interpro\Service\Contracts\Cleaner as CleanerInterface;
use Interpro\Service\Enum\Artefact;

class DbCleaner implements CleanerInterface
{
    private $taxonomy;
    private $settingsSet;
    private $consoleOutput;

    public function __construct(Taxonomy $taxonomy, ImageSettingsSet $settingsSet)
    {
        $this->taxonomy = $taxonomy;
        $this->settingsSet = $settingsSet;
        $this->consoleOutput = new \Symfony\Component\Console\Output\ConsoleOutput();
    }

    /**
     * @param callable $action
     *
     * @return bool
     */
    private function strategy(callable $action)
    {
        $report = false;

        $tables = [
            'image' => Image::class,
            'resize' => Resize::class,
            'crop' => Crop::class
            ];

        foreach($tables as $type_name => $modelClass)
        {
            $wehave = $modelClass::all();

            foreach($wehave as $model)
            {
                $entity_name = $model->entity_name;

                if($type_name === 'image')
                {
                    $name = $model->name;
                }
                else
                {
                    $name = $model->image_name;
                }

                if(!$this->taxonomy->exist($entity_name))
                {
                    $action(1, $type_name, $model);
                    $report = true;
                }
                else
                {
                    $ownerType = $this->taxonomy->getType($entity_name);

                    if($ownerType->getRank() === TypeRank::OWN)
                    {
                        $action(2, $type_name, $model);
                        $report = true;
                    }
                    elseif(!$ownerType->ownExist($name))
                    {
                        $action(3, $type_name, $model);
                        $report = true;
                    }
                    elseif($ownerType->getFieldType($name)->getName() !== 'image')
                    {
                        $action(4, $type_name, $model);
                        $report = true;
                    }
                    elseif($type_name !== 'image')
                    {
                        $field = $ownerType->getField($name);

                        $imageSSet = $this->settingsSet->getImage($field);

                        if($type_name === 'resize')
                        {
                            if(!$imageSSet->resizeExist($model->name))
                            {
                                $action(5, $type_name, $model);
                                $report = true;
                            }
                        }
                        elseif($type_name === 'crop')
                        {
                            if(!$imageSSet->cropExist($model->name))
                            {
                                $action(5, $type_name, $model);
                                $report = true;
                            }
                        }
                    }
                }
            }
        }

        return $report;
    }

    /**
     * @return bool
     */
    public function inspect()
    {
        $action = function($flag, $type_name, $model)
        {
            $entity_name = $model->entity_name;
            $entity_id   = $model->entity_id;

            if($type_name === 'image')
            {
                $name = $model->name;
            }
            else
            {
                $name = $model->image_name;
            }

            if($flag === 1)
            {
                $message = 'ImageAggr '.$type_name.'('.$entity_id.'): обнаружена запись для типа хозяина'.$entity_name.' не найденого в таксономии.';
            }
            elseif($flag === 2)
            {
                $message = 'ImageAggr '.$type_name.'('.$entity_id.'): обнаружена запись для типа хозяина'.$entity_name.' не соответствующего ранга.';
            }
            elseif($flag === 3)
            {
                $message = 'ImageAggr '.$type_name.'('.$entity_id.'): обнаружена запись несуществующего поля '.$name.' для хозяина '.$entity_name.'.';
            }
            elseif($flag === 4)
            {
                $message = 'ImageAggr '.$type_name.'('.$entity_id.'): обнаружена запись несуществующего поля '.$name.' для хозяина '.$entity_name.'.';
            }
            elseif($flag === 5)
            {
                $message = 'ImageAggr '.$type_name.'('.$entity_id.'): обнаружена запись несуществующей генерации '.$model->name.' поля '.$name.' для хозяина '.$entity_name.'.';
            }
            else
            {
                return;
            }

            $this->consoleOutput->writeln($message);
        };

        $report = $this->strategy($action);

        return $report;
    }

    /**
     * @return void
     */
    public function clean()
    {
        $action = function($flag, $type_name, $model)
        {
            $entity_name = $model->entity_name;
            $entity_id   = $model->entity_id;

            if($type_name === 'image')
            {
                $name = $model->name;
            }
            else
            {
                $name = $model->image_name;
            }

            $model->delete();

            if($flag === 1)
            {
                $message = 'ImageAggr '.$type_name.'('.$entity_id.'): удалена запись для типа хозяина'.$entity_name.' не найденого в таксономии.';
            }
            elseif($flag === 2)
            {
                $message = 'ImageAggr '.$type_name.'('.$entity_id.'): удалена запись для типа хозяина'.$entity_name.' не соответствующего ранга.';
            }
            elseif($flag === 3)
            {
                $message = 'ImageAggr '.$type_name.'('.$entity_id.'): удалена запись несуществующего поля '.$name.' для хозяина '.$entity_name.'.';
            }
            elseif($flag === 4)
            {
                $message = 'ImageAggr '.$type_name.'('.$entity_id.'): удалена запись несуществующего поля '.$name.' для хозяина '.$entity_name.'.';
            }
            elseif($flag === 5)
            {
                $message = 'ImageAggr '.$type_name.'('.$entity_id.'): удалена запись несуществующей генерации '.$model->name.' поля '.$name.' для хозяина '.$entity_name.'.';
            }
            else
            {
                return;
            }

            $this->consoleOutput->writeln($message);
        };

        $this->strategy($action);
    }

    /**
     * @return string
     */
    public function getArtefact()
    {
        return Artefact::DB_ROW;
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return 'imageaggr';
    }
}
