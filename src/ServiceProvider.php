<?php

namespace Goldie\PeakTools;

use Illuminate\Support\Facades\View;
use Statamic\Statamic;
use Statamic\Facades\GlobalSet;
use Statamic\Providers\AddonServiceProvider;
use Goldie\PeakTools\Widgets\ImagesMissingAlt;
use Goldie\PeakTools\Listeners\UpdateImagesMissingAltCacheListener;

class ServiceProvider extends AddonServiceProvider
{
    protected $subscribe = [
        UpdateImagesMissingAltCacheListener::class,
    ];

    protected $widgets = [
        ImagesMissingAlt::class
    ];

    protected $updateScripts = [
        \Goldie\PeakTools\Updates\UpdateFormJSDriver::class,
        \Goldie\PeakTools\Updates\UpdateFormFields::class,
        \Goldie\PeakTools\Updates\UpdateFormErrorHandling::class,
        \Goldie\PeakTools\Updates\UpdateButtonAttributeTags::class,
        \Goldie\PeakTools\Updates\UpdateImagesBlueprintWithExemptToggle::class,
        \Goldie\PeakTools\Updates\UpdateHoneypotField::class,
        \Goldie\PeakTools\Updates\AddTrackerEventsField::class,
    ];

    protected $vite = [
        'input' => [
            'resources/js/addon.js',
            'resources/css/addon.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function bootAddon()
    {
        $this->registerPublishableViews();

        // Provide custom field conditions global tracker data to hide/show the event field in the button partial.
        View::composer('statamic::layout', function ($view) {
            if (auth()->guest()) {
                return;
            }

            Statamic::provideToScript([
                'use_fathom' => GlobalSet::findByHandle('seogoldie')->inDefaultSite()->get('use_fathom'),
                'use_google' => GlobalSet::findByHandle('seogoldie')->inDefaultSite()->get('tracker_type') === 'gtm' || GlobalSet::findByHandle('seogoldie)->inDefaultSite()->get('tracker_type') === 'gtag',
            ]);
        });
    }

    protected function registerPublishableViews()
    {
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/statamic-vanilla-peak-tools'),
        ], 'statamic-vanilla-peak-tools-views');
    }
}
