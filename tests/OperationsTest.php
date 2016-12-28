<?php

namespace Interpro\ImageAggr\Test;

use Illuminate\Support\Facades\File;
use Interpro\Core\Ref\ARef;
use Interpro\ImageAggr\Db\TestImageAggrDbAgent;
use Interpro\ImageAggr\Operation\CleanOperation;
use Interpro\ImageAggr\Operation\CleanPhOperation;
use Interpro\ImageAggr\Operation\CropOperation;
use Interpro\ImageAggr\Operation\DeleteOperation;
use Interpro\ImageAggr\Operation\InitOperation;
use Interpro\ImageAggr\Operation\RefreshOperation;
use Interpro\ImageAggr\Operation\SaveOperation;
use Interpro\ImageAggr\Operation\UploadOperation;
use Interpro\ImageAggr\Placeholder\PlaceholderAgent;
use Interpro\ImageAggr\Settings\PathResolver;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Foundation\Testing\TestCase;

class OperationsTest extends TestCase
{
    private $imageSettngs;
    private $taxonomy;
    private $pathResolver;
    private $dbAgent;
    private $phAgent;

    use SameTrait;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    public function setUp()
    {
        parent::setUp();

        //Создание основных папок -------------------------------------------------------------------------
        if(!File::isDirectory(public_path('images/test')))
        {
            File::makeDirectory(public_path('images/test'));
        }

        if(!File::isDirectory(public_path('images/test/resizes')))
        {
            File::makeDirectory(public_path('images/test/resizes'));
        }

        if(!File::isDirectory(public_path('images/test/crops')))
        {
            File::makeDirectory(public_path('images/test/crops'));
        }

        if(!File::isDirectory(public_path('images/test/placeholders')))
        {
            File::makeDirectory(public_path('images/test/placeholders'));
        }

        //Создание папок временного хранения для обеспечения процесса выборка картинки в админ. панели -----
        if(!File::isDirectory(public_path('images/test/tmp')))
        {
            File::makeDirectory(public_path('images/test/tmp'));
        }

        if(!File::isDirectory(public_path('images/test/tmp/resizes')))
        {
            File::makeDirectory(public_path('images/test/tmp/resizes'));
        }

        $this->taxonomy = $this->getTaxonomy();
        $this->imageSettngs = $this->getSettings($this->taxonomy);
        $this->pathResolver = new PathResolver([], [], true);
        $this->dbAgent = new TestImageAggrDbAgent();
        $this->phAgent = new PlaceholderAgent($this->pathResolver);

        //Файлы модификации
        $mod_file1 = public_path('images/test/crops').'/modimages_0_wmimage_crop50x50.png';
        $mod_file2 = public_path('images/test/crops').'/modimages_0_maskimage_crop40x40.png';

        $img = Image::canvas(50, 50, '#ff0000')->encode('png', 100);
        $img->save($mod_file1, 100);
        chmod($mod_file1, 0644);

        $img = Image::canvas(40, 40, '#00ff00')->encode('png', 100);
        $img->save($mod_file2, 100);
        chmod($mod_file2, 0644);
    }

    public function tearDown()
    {
        $image_dir = $this->pathResolver->getImageDir();
        $resize_dir = $this->pathResolver->getResizeDir();
        $ph_dir = $this->pathResolver->getPlaceholderDir();

        foreach (glob($image_dir.'/test_block*.*') as $file)
        {
            if(is_dir($file))
            {
                continue;
            }

            unlink($file);
        }

        foreach (glob($resize_dir.'/test_block*.*') as $file)
        {
            if(is_dir($file))
            {
                continue;
            }

            unlink($file);
        }

        foreach (glob($this->pathResolver->getCropDir().'/test_block*.*') as $file)
        {
            if(is_dir($file))
            {
                continue;
            }

            unlink($file);
        }

        foreach (glob($ph_dir.'/placeholder*.*') as $file)
        {
            if(is_dir($file))
            {
                continue;
            }

            unlink($file);
        }

        $mod_file1 = public_path('images/test/crops').'/modimages_0_wmimage_crop50x50.png';
        $mod_file2 = public_path('images/test/crops').'/modimages_0_maskimage_crop40x40.png';

        unlink($mod_file1);
        unlink($mod_file2);
    }

    public function testInitOperation()
    {
        $this->dbAgent->setImages(
            [
                [
                    'id' => 1,
                    'name' => 'foto_face',
                    'entity_name' => 'test_block',
                    'entity_id' => 0,
                    'link' => '',
                    'alt' => '',
                    'cache_index' => 0,
                    'width' => 400,
                    'height' => 400
                ]
            ]
        );

        $this->dbAgent->setResizes(
            [
                [
                    'id' => 1,
                    'name' => 'res100x100',
                    'entity_name' => 'test_block',
                    'entity_id' => 0,
                    'image_name' => 'foto_face',
                    'alt' => '',
                    'link' => '',
                    'cache_index' => 0,
                    'width' => 100,
                    'height' => 100
                ],
                [
                    'id' => 2,
                    'name' => 'res400',
                    'entity_name' => 'test_block',
                    'entity_id' => 0,
                    'image_name' => 'foto_face',
                    'alt' => '',
                    'link' => '',
                    'cache_index' => 0,
                    'width' => 400,
                    'height' => 400
                ],
                [
                    'id' => 3,
                    'name' => 'res800',
                    'entity_name' => 'test_block',
                    'entity_id' => 0,
                    'image_name' => 'foto_face',
                    'alt' => '',
                    'link' => '',
                    'cache_index' => 0,
                    'width' => 800,
                    'height' => 800
                ],
                [
                    'id' => 4,
                    'name' => 'res1000',
                    'entity_name' => 'test_block',
                    'entity_id' => 0,
                    'image_name' => 'foto_face',
                    'alt' => '',
                    'link' => '',
                    'cache_index' => 0,
                    'width' => 1000,
                    'height' => 1000
                ],
            ]
        );

        $this->dbAgent->setCrops(
            [
                [
                    'id' => 1,
                    'name' => 'crop800x600',
                    'entity_name' => 'test_block',
                    'entity_id' => 0,
                    'image_name' => 'foto_face',
                    'alt' => '',
                    'link' => '',
                    'man_name' => 'res400',
                    'target_name' => 'res800',
                    'cache_index' => 0,
                    'x' => 0,
                    'y' => 0,
                    'width' => 800,
                    'height' => 600,
                ],

                [
                    'id' => 2,
                    'name' => 'crop400x300',
                    'entity_name' => 'test_block',
                    'entity_id' => 0,
                    'image_name' => 'foto_face',
                    'alt' => '',
                    'link' => '',
                    'man_name' => 'res400',
                    'target_name' => 'res800',
                    'cache_index' => 0,
                    'x' => 0,
                    'y' => 0,
                    'width' => 800,
                    'height' => 600,
                ],
            ]
        );

        $init = new InitOperation($this->pathResolver, $this->taxonomy, $this->dbAgent, $this->phAgent);

        $blockType = $this->taxonomy->getType('test_block');
        $testBlockFields = $blockType->getOwns()->getTyped('image');
        $fotoFace = $testBlockFields->getField('foto_face');
        $imageFace = $this->imageSettngs->getImage($fotoFace);
        $aRef = new ARef($blockType, 0);

        $init->execute($aRef, $imageFace);

        //Проверить наличие плэйсхолдеров
        $this->assertTrue(true, 'init');
    }

    /**
     * @depends testInitOperation
     */
    public function testUploadOperation()
    {
        $tmp_file_path = sys_get_temp_dir().'/test_imagearrg_oper.png';

        //Болванка в тэмп
        $img = Image::canvas(1600, 1200, '#ffffff')->encode('png', 100);
        $img->save($tmp_file_path, 100);
        chmod($tmp_file_path, 0644);

        //Создать симфони аплоад
        $uploadedFile = new UploadedFile(
            $tmp_file_path,
            'test_imagearrg_oper.png',
            'image/png',
            filesize($tmp_file_path),
            null, true
        );

        $upload  = new UploadOperation($this->pathResolver, $this->taxonomy, $this->dbAgent, $this->phAgent);

        //Выполнить операцию
        $blockType = $this->taxonomy->getType('test_block');
        $testBlockFields = $blockType->getOwns()->getTyped('image');
        $fotoFace = $testBlockFields->getField('foto_face');
        $imageFace = $this->imageSettngs->getImage($fotoFace);
        $aRef = new ARef($blockType, 0);

        $upload->execute($aRef, $imageFace, $uploadedFile);

        $file_name = 'test_block_0_foto_face.png';
        $resize_file_name = 'test_block_0_foto_face_res100x100.png';
        $file_path = $this->pathResolver->getTmpDir().'/'.$file_name;
        $resize_file_path = $this->pathResolver->getResizeTmpDir().'/'.$resize_file_name;

        //Проверить наличие файлов
        $this->assertFileExists($file_path, 'upload');
        $this->assertFileExists($resize_file_path, 'upload');
    }

    /**
     * @depends testUploadOperation
     */
    public function testSaveOperation()
    {
        $save = new SaveOperation($this->pathResolver, $this->taxonomy, $this->dbAgent, $this->phAgent);

        $blockType = $this->taxonomy->getType('test_block');
        $testBlockFields = $blockType->getOwns()->getTyped('image');
        $fotoFace = $testBlockFields->getField('foto_face');
        $imageFace = $this->imageSettngs->getImage($fotoFace);
        $aRef = new ARef($blockType, 0);

        $user_attrs = ['alt' => 'Альт картинки', 'resizes'=>['res100x100' => ['alt' => 'Альт ресайза res100x100']], 'crops'=>['crop800x600' => ['alt' => 'Альт кропа crop800x600']]];

        $save->execute($aRef, $imageFace, $user_attrs);

        $owner_name = $aRef->getType()->getName();
        $owner_id   = $aRef->getId();
        $image_name = $imageFace->getName();

        $path_resize1 = $this->pathResolver->getResizeDir().'/test_block_0_foto_face_res100x100.png';
        $path_resize2 = $this->pathResolver->getResizeDir().'/test_block_0_foto_face_res400.png';
        $path_resize3 = $this->pathResolver->getResizeDir().'/test_block_0_foto_face_res400mod.png';
        $path_resize4 = $this->pathResolver->getResizeDir().'/test_block_0_foto_face_res800.png';
        $path_resize5 = $this->pathResolver->getResizeDir().'/test_block_0_foto_face_res1000.png';
        $path_crop1 = $this->pathResolver->getCropDir().'/test_block_0_foto_face_crop800x600.png';
        $path_crop2 = $this->pathResolver->getCropDir().'/test_block_0_foto_face_crop400x300.png';

        $this->assertFileExists($path_resize1, 'save');
        $this->assertFileExists($path_resize2, 'save');
        $this->assertFileExists($path_resize3, 'save');
        $this->assertFileExists($path_resize4, 'save');
        $this->assertFileExists($path_resize5, 'save');
        $this->assertFileExists($path_crop1, 'save');
        $this->assertFileExists($path_crop2, 'save');

        $image_eq = $this->dbAgent->imageAttrEq($owner_name, $owner_id, $image_name, 'alt', 'Альт картинки');
        $resize_eq = $this->dbAgent->resizeAttrEq($owner_name, $owner_id, $image_name, 'res100x100', 'alt', 'Альт ресайза res100x100');
        $crop_eq = $this->dbAgent->cropAttrEq($owner_name, $owner_id, $image_name, 'crop800x600', 'alt', 'Альт кропа crop800x600');

        $this->assertTrue($image_eq, 'save');
        $this->assertTrue($resize_eq, 'save');
        $this->assertTrue($crop_eq, 'save');

        //Проверка модов
        $color1 = Image::make($path_resize3)->pickColor(10, 10, 'hex');
        $color2 = Image::make($path_resize3)->pickColor(59, 59, 'hex');
        $color3 = Image::make($path_resize3)->pickColor(389, 289, 'hex');
        $color4 = Image::make($path_resize3)->pickColor(350, 250, 'hex');

        $this->assertEquals('#ff0000', $color1, 'save');
        $this->assertEquals('#ff0000', $color2, 'save');
        $this->assertEquals('#00ff00', $color3, 'save');
        $this->assertEquals('#00ff00', $color4, 'save');
    }

    /**
     * @depends testSaveOperation
     */
    public function testRefreshOperation()
    {
        //Перекрасить оригинал в новый цвет
        $img = Image::canvas(1600, 1200, '#990099')->encode('png', 100);
        $path_image = $this->pathResolver->getImageDir().'/test_block_0_foto_face.png';
        $img->save($path_image, 100);

        $refresh = new RefreshOperation($this->pathResolver, $this->taxonomy, $this->dbAgent, $this->phAgent);

        $blockType = $this->taxonomy->getType('test_block');
        $testBlockFields = $blockType->getOwns()->getTyped('image');
        $fotoFace = $testBlockFields->getField('foto_face');
        $imageFace = $this->imageSettngs->getImage($fotoFace);
        $aRef = new ARef($blockType, 0);

        //Вписать альты для картинок
        $refresh->execute($aRef, $imageFace);

        $path_resize1 = $this->pathResolver->getResizeDir().'/test_block_0_foto_face_res100x100.png';
        $path_resize2 = $this->pathResolver->getResizeDir().'/test_block_0_foto_face_res400.png';
        $path_resize3 = $this->pathResolver->getResizeDir().'/test_block_0_foto_face_res400mod.png';
        $path_resize4 = $this->pathResolver->getResizeDir().'/test_block_0_foto_face_res800.png';
        $path_resize5 = $this->pathResolver->getResizeDir().'/test_block_0_foto_face_res1000.png';
        $path_crop1 = $this->pathResolver->getCropDir().'/test_block_0_foto_face_crop800x600.png';
        $path_crop2 = $this->pathResolver->getCropDir().'/test_block_0_foto_face_crop400x300.png';

        //перекрасился ли весь набор от оригинала?
        $color1 = Image::make($path_resize1)->pickColor(99, 99, 'hex');
        $color2 = Image::make($path_resize2)->pickColor(99, 99, 'hex');
        $color3 = Image::make($path_resize3)->pickColor(99, 99, 'hex');
        $color4 = Image::make($path_resize4)->pickColor(99, 99, 'hex');
        $color5 = Image::make($path_resize5)->pickColor(99, 99, 'hex');
        $color6 = Image::make($path_crop1)->pickColor(99, 99, 'hex');
        $color7 = Image::make($path_crop2)->pickColor(99, 99, 'hex');

        $this->assertEquals('#990099', $color1, 'refresh');
        $this->assertEquals('#990099', $color2, 'refresh');
        $this->assertEquals('#990099', $color3, 'refresh');
        $this->assertEquals('#990099', $color4, 'refresh');
        $this->assertEquals('#990099', $color5, 'refresh');
        $this->assertEquals('#990099', $color6, 'refresh');
        $this->assertEquals('#990099', $color7, 'refresh');

    }

    /**
     * @depends testSaveOperation
     */
    public function testCleanOperation()
    {
        $clean = new CleanOperation($this->pathResolver, $this->taxonomy, $this->dbAgent, $this->phAgent);

        $blockType = $this->taxonomy->getType('test_block');
        $testBlockFields = $blockType->getOwns()->getTyped('image');
        $fotoFace = $testBlockFields->getField('foto_face');
        $imageFace = $this->imageSettngs->getImage($fotoFace);
        $aRef = new ARef($blockType, 0);

        $clean->execute($aRef, $imageFace);

        $image_dir = $this->pathResolver->getImageDir();
        $resize_dir = $this->pathResolver->getResizeDir();

        $path_image = $image_dir.'/test_block_0_foto_face.png';
        $path_resize1 = $resize_dir.'/test_block_0_foto_face_res100x100.png';
        $path_resize2 = $resize_dir.'/test_block_0_foto_face_res400.png';
        $path_resize3 = $resize_dir.'/test_block_0_foto_face_res400mod.png';
        $path_resize4 = $resize_dir.'/test_block_0_foto_face_res800.png';
        $path_resize5 = $resize_dir.'/test_block_0_foto_face_res1000.png';
        $path_crop1 = $this->pathResolver->getCropDir().'/test_block_0_foto_face_crop800x600.png';
        $path_crop2 = $this->pathResolver->getCropDir().'/test_block_0_foto_face_crop400x300.png';

        //Проверка очищенности файлов
        $this->assertFileNotExists($path_image);
        $this->assertFileNotExists($path_resize1);
        $this->assertFileNotExists($path_resize2);
        $this->assertFileNotExists($path_resize3);
        $this->assertFileNotExists($path_resize4);
        $this->assertFileNotExists($path_resize5);
        $this->assertFileNotExists($path_crop1);
        $this->assertFileNotExists($path_crop2);
    }

    /**
     * @depends testSaveOperation
     */
    public function testCleanPhOperation()
    {
        $cleanPh = new CleanPhOperation($this->pathResolver, $this->taxonomy, $this->dbAgent, $this->phAgent);

        $blockType = $this->taxonomy->getType('test_block');
        $testBlockFields = $blockType->getOwns()->getTyped('image');
        $fotoFace = $testBlockFields->getField('foto_face');
        $imageFace = $this->imageSettngs->getImage($fotoFace);
        $aRef = new ARef($blockType, 0);

        $cleanPh->execute($aRef, $imageFace);

        $ph_dir = $this->pathResolver->getPlaceholderDir();

        $this->assertFileExists($ph_dir.'/placeholder_100_100_808080.jpeg');
        $this->assertFileExists($ph_dir.'/placeholder_400_300_808080.jpeg');
        $this->assertFileExists($ph_dir.'/placeholder_400_400_808080.jpeg');
        $this->assertFileExists($ph_dir.'/placeholder_800_600_808080.jpeg');
        $this->assertFileExists($ph_dir.'/placeholder_800_800_808080.jpeg');
        $this->assertFileExists($ph_dir.'/placeholder_1000_1000_808080.jpeg');
    }

    /**
     * @depends testRefreshOperation
     */
    public function testCropOperation()
    {
        $path_resize4 = $this->pathResolver->getResizeDir().'/test_block_0_foto_face_res800.png';
        $resize4 = Image::make($path_resize4);
        $resize4->pixel('#ff0000', 9, 9);
        $resize4->pixel('#ff0000', 408, 308); //Внутри квадрата отсчитываем длины включая в отсчет первый пиксель квадрата
        $resize4->save($path_resize4, 100);

        $crop = new CropOperation($this->pathResolver, $this->taxonomy, $this->dbAgent, $this->phAgent);

        $blockType = $this->taxonomy->getType('test_block');
        $testBlockFields = $blockType->getOwns()->getTyped('image');
        $fotoFace = $testBlockFields->getField('foto_face');
        $imageFace = $this->imageSettngs->getImage($fotoFace);
        $cropFace = $imageFace->getCrop('crop400x300');
        $aRef = new ARef($blockType, 0);

        //Кроп внутри цели
        $crop->execute($aRef, $imageFace, $cropFace, ['x' => 9, 'y' => 9]);

        $path_crop = $this->pathResolver->getCropDir().'/test_block_0_foto_face_crop400x300.png';

        $color1 = Image::make($path_crop)->pickColor(0, 0, 'hex');
        $color2 = Image::make($path_crop)->pickColor(399, 299, 'hex');

        $this->assertEquals('#ff0000', $color1);
        $this->assertEquals('#ff0000', $color2);
        //----------------------------------------------------------------

        //Алгоритм кропа не покрыт тэстами полностью. Конкретно: цель внутри рамки, рамка не полностью внутри цели.
    }

    /**
     * @depends testSaveOperation
     */
    public function testDeleteOperation()
    {
        $delete = new DeleteOperation($this->pathResolver, $this->taxonomy, $this->dbAgent, $this->phAgent);

        $blockType = $this->taxonomy->getType('test_block');
        $testBlockFields = $blockType->getOwns()->getTyped('image');
        $fotoFace = $testBlockFields->getField('foto_face');
        $imageFace = $this->imageSettngs->getImage($fotoFace);
        $aRef = new ARef($blockType, 0);

        $delete->execute($aRef, $imageFace);

        $owner_name = $aRef->getType()->getName();
        $owner_id   = $aRef->getId();
        $image_name = $imageFace->getName();

        $image_exist = $this->dbAgent->imageExist($owner_name, $owner_id, $image_name);
        $resize_exist = $this->dbAgent->resizeExist($owner_name, $owner_id, $image_name, 'res100x100');
        $crop_exist = $this->dbAgent->cropExist($owner_name, $owner_id, $image_name, 'crop400x300');

        $this->assertFalse($image_exist);
        $this->assertFalse($resize_exist);
        $this->assertFalse($crop_exist);
    }

}
