<?php

namespace Interpro\ImageAggr;

use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Interpro\Core\Contracts\Mediator\RefConsistMediator;
use Interpro\Core\Contracts\Mediator\SyncMediator;
use Interpro\Core\Contracts\Taxonomy\Taxonomy;
use Interpro\Core\Taxonomy\Manifests\BTypeManifest;
use Interpro\Extractor\Contracts\Creation\CItemBuilder;
use Interpro\Extractor\Contracts\Creation\CollectionFactory;
use Interpro\Extractor\Contracts\Db\JoinMediator;
use Interpro\Extractor\Contracts\Db\MappersMediator;
use Interpro\Extractor\Contracts\Selection\Tuner;
use Interpro\ImageAggr\Contracts\Operation\SaveOperation;
use Interpro\ImageAggr\Contracts\Settings\PathResolver;
use Interpro\ImageAggr\Creation\CapGenerator;
use Interpro\ImageAggr\Creation\ImageItemFactory;
use Interpro\ImageAggr\Db\ImageBMapper;
use Interpro\ImageAggr\Db\ImageJoiner;
use Interpro\ImageAggr\Db\ImageQuerier;
use Interpro\ImageAggr\Db\ModImagesAMapper;
use Interpro\ImageAggr\Exception\ImageAggrException;
use Interpro\Core\Contracts\Mediator\DestructMediator;
use Interpro\Core\Contracts\Mediator\InitMediator;
use Interpro\Core\Contracts\Mediator\UpdateMediator;
use Interpro\ImageAggr\Executors\Destructor;
use Interpro\ImageAggr\Executors\Initializer;
use Interpro\ImageAggr\Executors\ModImagesDestructor;
use Interpro\ImageAggr\Executors\ModImagesInitializer;
use Interpro\ImageAggr\Executors\ModImagesRefConsistExecutor;
use Interpro\ImageAggr\Executors\ModImagesSynchronizer;
use Interpro\ImageAggr\Executors\ModImagesUpdateExecutor;
use Interpro\ImageAggr\Executors\Synchronizer;
use Interpro\ImageAggr\Executors\UpdateExecutor;
use Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet;
use Interpro\ImageAggr\Contracts\Operation\InitOperation;
use Interpro\ImageAggr\Contracts\Operation\Owner\OwnerDeleteOperationsCall;
use Interpro\ImageAggr\Service\DbCleaner;
use Interpro\ImageAggr\Service\FileCleaner;
use Interpro\Service\Contracts\CleanMediator;

class ImageAggrSecondServiceProvider extends ServiceProvider {

    /**
     * @return void
     */
    public function boot(Dispatcher $dispatcher,
                         Taxonomy $taxonomy,
                         InitMediator $initMediator,
                         SyncMediator $syncMediator,
                         UpdateMediator $updateMediator,
                         DestructMediator $destructMediator,
                         RefConsistMediator $refConsistMediator,
                         MappersMediator $mappersMediator,
                         JoinMediator $joinMediator,
                         CollectionFactory $collectionFactory,
                         CItemBuilder $cItemBuilder,
                         InitOperation $initOperation,
                         SaveOperation $saveOperation,
                         ImageSettingsSet $settingsSet,
                         OwnerDeleteOperationsCall $deleteOperationsCall,
                         Tuner $tuner,
                         CleanMediator $cleanMediator,
                         PathResolver $pathResolver)
    {
        //Log::info('Загрузка ImageAggrSecondServiceProvider');

        //Картинки
        $initializer = new Initializer($initOperation, $settingsSet);
        $initMediator->registerBInitializer($initializer);

        $synchronizer = new Synchronizer($initOperation, $settingsSet);
        $syncMediator->registerOwnSynchronizer($synchronizer);

        $updateExecutor = new UpdateExecutor($saveOperation, $settingsSet);
        $updateMediator->registerBUpdateExecutor($updateExecutor);

        $destructor = new Destructor($deleteOperationsCall);
        $destructMediator->registerBDestructor($destructor);

        //Блок картинок-модификаторов
        $modImagesInitializer = new ModImagesInitializer($initMediator);
        $initMediator->registerAInitializer($modImagesInitializer);

        $modImagesSynchronizer = new ModImagesSynchronizer($syncMediator);
        $syncMediator->registerASynchronizer($modImagesSynchronizer);

        $modImagesUpdateExecutor = new ModImagesUpdateExecutor($updateMediator);
        $updateMediator->registerAUpdateExecutor($modImagesUpdateExecutor);

        $modImagesDestructor = new ModImagesDestructor($refConsistMediator, $destructMediator);
        $destructMediator->registerADestructor($modImagesDestructor);

        $modImagesRCExecutor = new ModImagesRefConsistExecutor();
        $refConsistMediator->registerRefConsistExecutor($modImagesRCExecutor);

        //Для Extractor'a
        $capGenerator = new CapGenerator($settingsSet);
        $itemFactory = new ImageItemFactory($collectionFactory, $cItemBuilder, $capGenerator);
        $capGenerator->setFactory($itemFactory);//Взаимная зависимость

        $imageQuerier = new ImageQuerier();
        $mapper = new ImageBMapper($itemFactory, $capGenerator, $imageQuerier, $tuner);
        $mappersMediator->registerBMapper($mapper);

        $joiner = new ImageJoiner();
        $joinMediator->registerJoiner($joiner);

        //Маппер модификатора
        $mIAmapper = new ModImagesAMapper($collectionFactory, $cItemBuilder, $mappersMediator);
        $mappersMediator->registerAMapper($mIAmapper);


        //Для сервиса
        $cleanerdb = new DbCleaner($taxonomy, $settingsSet);
        $cleanMediator->registerCleaner($cleanerdb);

        //Для сервиса
        $cleanerfile = new FileCleaner($settingsSet, $pathResolver);
        $cleanMediator->registerCleaner($cleanerfile);
    }

    /**
     * @return void
     */
    public function register()
    {
        //Log::info('Регистрация ImageAggrSecondServiceProvider');

        $forecastList = App::make('Interpro\Core\Contracts\Taxonomy\TypesForecastList');
        $typeRegistrator = App::make('Interpro\Core\Contracts\Taxonomy\TypeRegistrator');

        $cNames = $forecastList->getCTypeNames();

        $message = 'Ошибка регистрации пакета imageaggr.';
        $err = false;

        if(!in_array('string', $cNames))
        {
            $err = true;
            $message .= PHP_EOL.'Не зарегестрировано имя типа string.';
        }

        if(!in_array('int', $cNames))
        {
            $err = true;
            $message .= PHP_EOL.'Не зарегестрировано имя типа int.';
        }

        if(!in_array('bool', $cNames))
        {
            $err = true;
            $message .= PHP_EOL.'Не зарегестрировано имя типа bool.';
        }

        if($err)
        {
            $message .= PHP_EOL.'Интерпретация предопределенных полей агрегатных типов image, resize, crop не возможна!';
            throw new ImageAggrException($message);
        }

        //-----------------------------------------------------------

        $imageMan  = new BTypeManifest('imageaggr', 'image',
            ['name' => 'string',
                'alt' => 'string',
                'link' => 'string',
                'width' => 'int',
                'height' => 'int',
                'cache_index' => 'int'],
            []);

        $resizeMan = new BTypeManifest('imageaggr', 'resize',
            ['name' => 'string',
                'alt' => 'string',
                'link' => 'string',
                'width' => 'int',
                'height' => 'int',
                'cache_index' => 'int'],
            ['original' => 'image']);

        $cropMan   = new BTypeManifest('imageaggr', 'crop',
            ['name' => 'string',
                'alt' => 'string',
                'link' => 'string',
                'cache_index' => 'int',
                'x' => 'int',
                'y' => 'int',
                'width' => 'int',
                'height' => 'int'],
            ['original' => 'image',
                'man' => 'resize',
                'target' => 'resize']);

        $typeRegistrator->registerType($imageMan);
        $typeRegistrator->registerType($resizeMan);
        $typeRegistrator->registerType($cropMan);

        //Регистрация блока картинок модификаторов
        $configInterpreter = new ModImagesConfigInterpreter($forecastList);
        $mi_config = config('interpro.modimages', []);
        $manifest = $configInterpreter->interpretConfig($mi_config);
        $typeRegistrator->registerType($manifest);

    }

}
