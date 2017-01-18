<?php

namespace Interpro\ImageAggr\Settings;

use Illuminate\Support\Facades\Log;
use Interpro\ImageAggr\Contracts\Settings\PathResolver as PathResolverInterface;

class PathResolver implements PathResolverInterface
{
    private $image_dir;
    private $placeholder_dir;
    private $crop_dir;
    private $resize_dir;
    private $tmp_dir;
    private $resize_tmp_dir;

    private $image_path;
    private $placeholder_path;
    private $crop_path;
    private $resize_path;
    private $tmp_path;
    private $resize_tmp_path;

    /**
     * @param array $dirs
     * @param array $paths
     * @param bool $test
     *
     * @return void
     */
    public function __construct(array $dirs, array $paths, $test = false)
    {
        $test = (bool) $test;
        $dir = public_path() . '/images'.($test ? '/test' : '');
        $path = '/images'.($test ? '/test' : '');

        $this->dir = $dir;
        $this->path = $path;

        $this->setAttr('image_dir',       'image',        $dir, '',             $dirs);
        $this->setAttr('tmp_dir',         'tmp',          $dir, 'tmp',          $dirs);
        $this->setAttr('placeholder_dir', 'placeholders', $dir, 'placeholders', $dirs);
        $this->setAttr('crop_dir',        'crops',        $dir, 'crops',        $dirs);
        $this->setAttr('resize_dir',      'resizes',      $dir, 'resizes',      $dirs);
        $this->setAttr('resize_tmp_dir',  'resizestmp',   $dir, 'tmp/resizes',  $dirs);

        $this->setAttr('image_path',       'image',        $path, '',             $paths);
        $this->setAttr('tmp_path',         'tmp',          $path, 'tmp',          $paths);
        $this->setAttr('placeholder_path', 'placeholders', $path, 'placeholders', $paths);
        $this->setAttr('crop_path',        'crops',        $path, 'crops',        $paths);
        $this->setAttr('resize_path',      'resizes',      $path, 'resizes',      $paths);
        $this->setAttr('resize_tmp_path',  'resizestmp',   $path, 'tmp/resizes',  $paths);
    }

    private function setAttr($name, $key, $dirpath, $def, array & $params)
    {
        $this->$name = $dirpath;

        if(array_key_exists($key, $params))
        {
            $continue_path = $params[$key];
        }
        else
        {
            $continue_path = $def;
        }

        if($continue_path)
        {
            $this->$name .= '/'.$continue_path;
        }

    }

    /**
     * @return string
     */
    public function getImageDir()
    {
        return $this->image_dir;
    }

    /**
     * @return string
     */
    public function getPlaceholderDir()
    {
        return $this->placeholder_dir;
    }

    /**
     * @return string
     */
    public function getCropDir()
    {
        return $this->crop_dir;
    }

    /**
     * @return string
     */
    public function getResizeDir()
    {
        return $this->resize_dir;
    }

    /**
     * @return string
     */
    public function getTmpDir()
    {
        return $this->tmp_dir;
    }

    /**
     * @return string
     */
    public function getResizeTmpDir()
    {
        return $this->resize_tmp_dir;
    }
    //--------------------------------------------------------------------------

    /**
     * @return string
     */
    public function getImagePath()
    {
        return $this->image_path;
    }

    /**
     * @return string
     */
    public function getPlaceholderPath()
    {
        return $this->placeholder_path;
    }

    /**
     * @return string
     */
    public function getCropPath()
    {
        return $this->crop_path;
    }

    /**
     * @return string
     */
    public function getResizePath()
    {
        return $this->resize_path;
    }

    /**
     * @return string
     */
    public function getTmpPath()
    {
        return $this->tmp_path;
    }

    /**
     * @return string
     */
    public function getResizeTmpPath()
    {
        return $this->resize_tmp_path;
    }

}
