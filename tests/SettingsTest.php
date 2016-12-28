<?php

namespace Interpro\ImageAggr\Test;

class SettingsTest extends \PHPUnit_Framework_TestCase
{
    private $imageSettngs;
    private $taxonomy;

    use SameTrait;

    public function setUp()
    {
        $this->taxonomy = $this->getTaxonomy();

        $this->imageSettngs = $this->getSettings($this->taxonomy);

    }

    public function testCreateSettings()
    {
        $testBlockFields = $this->taxonomy->getBlock('test_block')->getOwns()->getTyped('image');
        $testSessionFields = $this->taxonomy->getGroup('group_session')->getOwns()->getTyped('image');

        $fotoFace    = $testBlockFields->getField('foto_face');
        $fotoProfile = $testBlockFields->getField('foto_profile');

        $foto1 = $testSessionFields->getField('foto_1');
        $foto2 = $testSessionFields->getField('foto_2');
        $foto3 = $testSessionFields->getField('foto_3');

        $imageFace = $this->imageSettngs->getImage($fotoFace);
        $imageProfile = $this->imageSettngs->getImage($fotoProfile);

        $image1 = $this->imageSettngs->getImage($foto1);
        $image2 = $this->imageSettngs->getImage($foto2);
        $image3 = $this->imageSettngs->getImage($foto3);

        $crop = $imageFace->getCrop('crop800x600');
        $mod = $imageFace->getResize('res400mod')->getMod('wmark');

        //и так далее, дописать, придумать какие виды утверждений использовать

        $this->assertTrue(true);
    }

}
