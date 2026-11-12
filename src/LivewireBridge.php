<?php

declare(strict_types=1);

namespace IuriGustavo\LivewireBridge;

use CraftCms\Cms\Plugin\Plugin;
use CraftCms\Cms\Support\Facades\Path;
use CraftCms\Cms\Support\Facades\Template;
use CraftCms\Cms\View\TemplateEngine;
use IuriGustavo\LivewireBridge\Synthesizers\ElementSynth;
use IuriGustavo\LivewireBridge\View\LivewireAwareBladeRenderer;
use Livewire\Livewire;

/**
 * Livewire Bridge for Craft CMS.
 *
 * - Templates routed by Craft that are Livewire single-file components are mounted
 *   as full-page Livewire components (see {@see LivewireAwareBladeRenderer}).
 * - Craft elements can be used as Livewire component properties (see {@see ElementSynth}).
 */
class LivewireBridge extends Plugin
{
    /** Livewire namespace that points at Craft's site templates folder. */
    public const string LIVEWIRE_NAMESPACE = 'craft';

    public function boot(): void
    {
        // Lets Livewire resolve "craft::news._entry" to <site templates>/news/_entry.blade.php,
        // both on the initial render and on subsequent Livewire update requests.
        Livewire::addNamespace(self::LIVEWIRE_NAMESPACE, viewPath: Path::siteTemplates());

        // Lets Livewire (de)hydrate Craft elements ($entry, $category, …) as component properties.
        Livewire::propertySynthesizer(ElementSynth::class);

        // Swap Craft's Blade renderer for one that knows about Livewire components.
        Template::extend(TemplateEngine::Blade, static fn ($app) => $app->make(LivewireAwareBladeRenderer::class));
    }
}
