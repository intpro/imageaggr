<?php

namespace Interpro\ImageAggr;

use Illuminate\Bus\Dispatcher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Interpro\ImageAggr\Settings\GenSettingsSetFactory;
use Interpro\ImageAggr\Settings\ImageSettingsSetFactory;
use Interpro\ImageAggr\Settings\PathResolver;

class ImageAggrUseServiceProvider extends ServiceProvider {

    /**
     * @return void
     */
    public function boot(Dispatcher $dispatcher)
    {
        //Log::info('Загрузка ImageAggrUseServiceProvider');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //Log::info('Регистрация ImageAggrUseServiceProvider');

        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Db\ImageAggrDbAgent',
            'Interpro\ImageAggr\Db\ImageAggrDbAgent'
        );

        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Settings\PathResolver',
            function($app)
            {
                $test = $app->runningUnitTests();
                return new PathResolver(config('interpro.imageaggr.dirs', []), config('interpro.imageaggr.paths', []), $test);
            }
        );

        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Settings\GenSettingsSetFactory',
            function($app)
            {
                $taxonomy = $app->make('Interpro\Core\Contracts\Taxonomy\Taxonomy');
                $modImagesType = $taxonomy->getType('modimages');
                return new GenSettingsSetFactory($modImagesType, config('interpro.resizes', []), config('interpro.crops', []));
            }
        );

        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Settings\ImageSettingsSetFactory',
            function($app)
            {
                $taxonomy = $app->make('Interpro\Core\Contracts\Taxonomy\Taxonomy');
                $factory = $app->make('Interpro\ImageAggr\Contracts\Settings\GenSettingsSetFactory');

                return new ImageSettingsSetFactory($taxonomy, config('interpro.images', []), $factory);
            }
        );

        //----------------------------------------------------------------
        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Operation\CleanOperation',
            'Interpro\ImageAggr\Operation\CleanOperation'
        );
        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Operation\CleanPhOperation',
            'Interpro\ImageAggr\Operation\CleanPhOperation'
        );
        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Operation\CropOperation',
            'Interpro\ImageAggr\Operation\CropOperation'
        );
        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Operation\DeleteOperation',
            'Interpro\ImageAggr\Operation\DeleteOperation'
        );
        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Operation\InitOperation',
            'Interpro\ImageAggr\Operation\InitOperation'
        );
        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Operation\RefreshOperation',
            'Interpro\ImageAggr\Operation\RefreshOperation'
        );
        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Operation\SaveOperation',
            'Interpro\ImageAggr\Operation\SaveOperation'
        );
        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Operation\UploadOperation',
            'Interpro\ImageAggr\Operation\UploadOperation'
        );
        //---------------------------------------------------------
        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Operation\Owner\OwnerDeleteOperationsCall',
            'Interpro\ImageAggr\Operation\Owner\OwnerDeleteOperationsCall'
        );
        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Operation\Owner\OwnerInitOperationsCall',
            'Interpro\ImageAggr\Operation\Owner\OwnerInitOperationsCall'
        );
        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Operation\Owner\OwnerSaveOperationsCall',
            'Interpro\ImageAggr\Operation\Owner\OwnerSaveOperationsCall'
        );
        //---------------------------------------------------------

        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Settings\Collection\ImageSettingsSet',
            function($app)
            {
                $factory = $app->make('Interpro\ImageAggr\Contracts\Settings\ImageSettingsSetFactory');
                return $factory->create();
            }
        );

        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\Placeholder\PlaceholderAgent',
            'Interpro\ImageAggr\Placeholder\PlaceholderAgent'
        );

        $this->app->singleton(
            'Interpro\ImageAggr\Contracts\CommandAgents\OperationsAgent',
            'Interpro\ImageAggr\CommandAgents\OperationsAgent'
        );

        $this->app->make('Interpro\ImageAggr\Http\ImageOperationController');


        include __DIR__ . '/Http/routes.php';
    }

}
