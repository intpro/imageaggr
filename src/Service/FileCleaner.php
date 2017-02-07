<?php

namespace Interpro\ImageAggr\Service;

use Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet;
use Interpro\ImageAggr\Contracts\Settings\PathResolver;
use Interpro\ImageAggr\Model\Crop;
use Interpro\ImageAggr\Model\Image;
use Interpro\ImageAggr\Model\Resize;
use Interpro\Service\Contracts\Cleaner as CleanerInterface;
use Interpro\Service\Enum\Artefact;

class FileCleaner implements CleanerInterface
{
    private $settingsSet;
    private $pathResolver;
    private $consoleOutput;

    public function __construct(ImageSettingsSet $settingsSet, PathResolver $pathResolver)
    {
        $this->settingsSet = $settingsSet;
        $this->pathResolver = $pathResolver;
        $this->consoleOutput = new \Symfony\Component\Console\Output\ConsoleOutput();
    }

    /**
     * @param callable $action
     *
     * @return bool
     */
    private function strategy(callable $action)
    {
        $image_links = [];
        $resize_links = [];
        $crop_links = [];

        $image_links_finded = [];
        $resize_links_finded = [];
        $crop_links_finded = [];


        $originals = Image::all();

        foreach($originals as $model)
        {
            $image_links[] = public_path().$model->link;
        }


        $resizes = Resize::all();

        foreach($resizes as $model)
        {
            $resize_links[] = public_path().$model->link;
        }


        $crops = Crop::all();

        foreach($crops as $model)
        {
            $crop_links[] = public_path().$model->link;
        }


        $image_dir = $this->pathResolver->getImageDir();
        $resize_dir = $this->pathResolver->getResizeDir();
        $crop_dir = $this->pathResolver->getCropDir();

        foreach (glob($image_dir.'/*.*') as $file)
        {
            if(is_dir($file))
            {
                continue;
            }

            $image_links_finded[] = $file;
        }

        foreach (glob($resize_dir.'/*.*') as $file)
        {
            if(is_dir($file))
            {
                continue;
            }

            $resize_links_finded[] = $file;
        }

        foreach (glob($crop_dir.'/*.*') as $file)
        {
            if(is_dir($file))
            {
                continue;
            }

            $crop_links_finded[] = $file;
        }

        $report = false;

        $image_diff  = array_diff($image_links_finded, $image_links);
        $resize_diff = array_diff($resize_links_finded, $resize_links);
        $crop_diff   = array_diff($crop_links_finded, $crop_links);

        if((count($image_diff) + count($resize_diff) + count($crop_diff)) > 0)
        {
            $report = true;
        }

        foreach($image_diff as $image_link)
        {
            $action(1, $image_link);
        }

        foreach($resize_diff as $resize_link)
        {
            $action(2, $resize_link);
        }

        foreach($crop_diff as $crop_link)
        {
            $action(3, $crop_link);
        }

        return $report;
    }

    /**
     * @return bool
     */
    public function inspect()
    {
        $action = function($flag, $link)
        {
            if($flag === 1)
            {
                $this->consoleOutput->writeln('ImageAggr: обнаружен файл '.$link.' в папке картинок, ссылки на который отсутствуют в базе данных.');
            }
            elseif($flag === 2)
            {
                $this->consoleOutput->writeln('ImageAggr: обнаружен файл '.$link.' в папке ресайзов, ссылки на который отсутствуют в базе данных.');
            }
            elseif($flag === 3)
            {
                $this->consoleOutput->writeln('ImageAggr: обнаружен файл '.$link.' в папке кропов, ссылки на который отсутствуют в базе данных.');
            }
            else
            {
                return;
            }
        };

        $report = $this->strategy($action);

        return $report;
    }

    /**
     * @return void
     */
    public function clean()
    {
        $action = function($flag, $link)
        {
            if($flag === 1)
            {
                unlink($link);
                $this->consoleOutput->writeln('ImageAggr: удалён файл '.$link.' в папке картинок, ссылки на который отсутствуют в базе данных.');
            }
            elseif($flag === 2)
            {
                unlink($link);
                $this->consoleOutput->writeln('ImageAggr: удалён файл '.$link.' в папке ресайзов, ссылки на который отсутствуют в базе данных.');
            }
            elseif($flag === 3)
            {
                unlink($link);
                $this->consoleOutput->writeln('ImageAggr: удалён файл '.$link.' в папке кропов, ссылки на который отсутствуют в базе данных.');
            }
            else
            {
                return;
            }
        };

        $this->strategy($action);
    }

    /**
     * @return string
     */
    public function getArtefact()
    {
        return Artefact::FILE;
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return 'imageaggr';
    }
}
