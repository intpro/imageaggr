<?php

namespace Interpro\ImageAggr\Contracts\CommandAgents;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface OperationsAgent
{
    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     *
     * @return void
     */
    public function clean($owner_name, $owner_id, $image_name);

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     *
     * @return void
     */
    public function cleanToPh($owner_name, $owner_id, $image_name);

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     *
     * @return void
     */
    public function refresh($owner_name, $owner_id, $image_name);

    /**
     * @param string $owner_name
     * @param string $owner_id
     * @param string $image_name
     * @param string $crop_name
     * @param array $attrs
     *
     * @return void
     */
    public function crop($owner_name, $owner_id, $image_name, $crop_name, array $attrs);

    /**
     * @param $owner_name
     * @param $owner_id
     * @param $image_name
     * @param UploadedFile $uploadedFile
     *
     * @return void
     */
    public function upload($owner_name, $owner_id, $image_name, UploadedFile $uploadedFile);
}
