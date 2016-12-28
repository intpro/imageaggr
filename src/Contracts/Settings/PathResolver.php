<?php

namespace Interpro\ImageAggr\Contracts\Settings;

interface PathResolver
{
    /**
     * @return string
     */
    public function getImageDir();

    /**
     * @return string
     */
    public function getPlaceholderDir();

    /**
     * @return string
     */
    public function getCropDir();

    /**
     * @return string
     */
    public function getResizeDir();

    /**
     * @return string
     */
    public function getTmpDir();

    /**
     * @return string
     */
    public function getResizeTmpDir();

    ///**
    // * @return string
    // */
    //public function getTransactDir();

    //-----------------------------------------------------------

    /**
     * @return string
     */
    public function getImagePath();

    /**
     * @return string
     */
    public function getPlaceholderPath();

    /**
     * @return string
     */
    public function getCropPath();

    /**
     * @return string
     */
    public function getResizePath();

    /**
     * @return string
     */
    public function getTmpPath();

    /**
     * @return string
     */
    public function getResizeTmpPath();

    ///**
    // * @return string
    // */
    //public function getTransactPath();

}
