<?php

namespace Interpro\ImageAggr\Placeholder;

use Interpro\ImageAggr\Exception\PlaceholderException;
use Interpro\ImageAggr\Contracts\Settings\CropSetting as CropSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\Enum\GenVariant;
use Interpro\ImageAggr\Contracts\Settings\GenerationSetting as GenerationSettingInterface;
use Interpro\ImageAggr\Contracts\Settings\PathResolver as PathResolverInterface;
use Interpro\ImageAggr\Contracts\Placeholder\PlaceholderAgent as PlaceholderAgentInterface;
use Interpro\ImageAggr\Contracts\Settings\ResizeSetting as ResizeSettingInterface;
use Intervention\Image\Facades\Image;

class PlaceholderAgent implements PlaceholderAgentInterface
{
    protected $pathResolver;

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    /**
     * @param string $color
     * @return bool
     */
    private function validateColor($color)
    {
        preg_match('/(#[a-f0-9]{3}([a-f0-9]{3})?)/i', $color, $matches);
        if (isset($matches[1]))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @param int $width
     * @param int $height
     * @param string $color
     * @return string
     */
    public function getPh($width, $height, $color = '#808080')
    {
        $ph_dir = rtrim($this->pathResolver->getPlaceholderDir(), '/ \t\n\r\0\x0B');

        if(!is_writable($ph_dir))
        {
            throw new \Interpro\ImageAggr\Exception\PlaceholderException('Дирректория картинок ('.$ph_dir.') не доступна для записи!');
        }

        if($width < 0)
        {
            throw new PlaceholderException('Ширина не может быть меньше нуля (0).');
        }

        if($height < 0)
        {
            throw new PlaceholderException('Высота не может быть меньше нуля (0).');
        }

        if(!$this->validateColor($color))
        {
            throw new PlaceholderException('Формат строки цвета неправильный (hex).');
        }

        $file_name = 'placeholder_' . $width . '_'.$height . '_' . substr($color, -6).'.jpeg';
        $file_dir = $ph_dir . '/' . $file_name;

        if(!file_exists($file_dir))
        {
            $img = Image::canvas($width, $height, $color);

            $img->save($file_dir, 100);

            chmod($file_dir, 0644);
        }

        $ph_path = rtrim($this->pathResolver->getPlaceholderPath(), '/ \t\n\r\0\x0B');

        return $ph_path.'/'.$file_name;
    }

    /**
     * @param \Interpro\ImageAggr\Contracts\Settings\ResizeSetting $resize
     * @param string $color
     * @return string
     */
    public function getResizePh(ResizeSettingInterface $resize, $color = '#808080')
    {
        $width = $resize->getWidth();
        $height = $resize->getHeight();

        //Если нет одной из координат, пусть будет квадратным
        if(!($width or $height))
        {
            $width = 400;
            $height = 400;
        }
        else
        {
            if(!$width)
            {
                $width = $height;
            }

            if(!$height)
            {
                $height = $width;
            }
        }

        return $this->getPh($width, $height, $color);
    }

    /**
     * @param \Interpro\ImageAggr\Contracts\Settings\CropSetting $crop
     * @param string $color
     * @return string
     */
    public function getCropPh(CropSettingInterface $crop, $color = '#808080')
    {
        $width = $crop->getWidth();
        $height = $crop->getHeight();

        return $this->getPh($width, $height, $color);
    }

    /**
     * @param \Interpro\ImageAggr\Contracts\Settings\GenerationSetting $gen
     * @param string $gen_variant
     * @param string $color
     * @return string
     */
    public function getGenPh(GenerationSettingInterface $gen, $color = '#808080')
    {
        $gen_variant = $gen->getVariant();

        if($gen_variant === GenVariant::RESIZE)
        {
            return $this->getResizePh($gen, $color);
        }
        elseif($gen_variant === GenVariant::CROP)
        {
            return $this->getCropPh($gen, $color);
        }
        else
        {
            throw new PlaceholderException('Получение placeholder для варианта картинки '.$gen_variant.' методом агента getGenPh не возможно, воспользуйтесь методом getPh!');
        }

    }



}
