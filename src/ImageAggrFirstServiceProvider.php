<?php

namespace Interpro\ImageAggr;

use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class ImageAggrFirstServiceProvider extends ServiceProvider {

    /**
     * @return void
     */
    public function boot(Dispatcher $dispatcher)
    {
        Log::info('Загрузка ImageAggrFirstServiceProvider');

        //-----------------------------------------------------------
        $this->publishes([__DIR__.'/config/images.php' => config_path('interpro/images.php')]);
        $this->publishes([__DIR__.'/config/modimages.php' => config_path('interpro/modimages.php')]);
        $this->publishes([__DIR__.'/config/resizes.php' => config_path('interpro/resizes.php')]);
        $this->publishes([__DIR__.'/config/crops.php' => config_path('interpro/crops.php')]);
        $this->publishes([__DIR__.'/config/imageaggr.php' => config_path('interpro/imageaggr.php')]);

        $this->publishes([
            __DIR__.'/migrations' => $this->app->databasePath().'/migrations'
        ], 'migrations');

        //Создание основных папок -----------------------------------
        if(!File::isDirectory(public_path('images')))
        {
            File::makeDirectory(public_path('images'));
        }

        if(!File::isDirectory(public_path('images/resizes')))
        {
            File::makeDirectory(public_path('images/resizes'));
        }

        if(!File::isDirectory(public_path('images/crops')))
        {
            File::makeDirectory(public_path('images/crops'));
        }

        if(!File::isDirectory(public_path('images/placeholders')))
        {
            File::makeDirectory(public_path('images/placeholders'));
        }

        //Создание папок временного хранения для обеспечения процесса выборка картинки в админ. панели -----
        if(!File::isDirectory(public_path('images/tmp')))
        {
            File::makeDirectory(public_path('images/tmp'));
        }

        if(!File::isDirectory(public_path('images/tmp/resizes')))
        {
            File::makeDirectory(public_path('images/tmp/resizes'));
        }


        //---------------------------------------для тэстов-------------

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

    }

    /**
     * @return void
     */
    public function register()
    {
        Log::info('Регистрация ImageAggrFirstServiceProvider');

        //Регистрируем имена, для интерпретации типов при загрузке
        $forecastList = $this->app->make('Interpro\Core\Contracts\Taxonomy\TypesForecastList');

        $forecastList->registerBTypeName('image');
        $forecastList->registerBTypeName('crop');
        $forecastList->registerBTypeName('resize');

        //Блок картинок модификаторов
        $forecastList->registerATypeName('modimages');
    }

}
