<?php

declare(strict_types=1);

namespace IuriGustavo\LivewireBridge;

use CraftCms\Cms\Blade\BladeRenderer;
use CraftCms\Cms\Support\Facades\Path;
use CraftCms\Cms\View\TemplateMode;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportPageComponents\PageComponentConfig;
use Livewire\Features\SupportPageComponents\SupportPageComponents;
use Livewire\Livewire;
use ReflectionMethod;

/**
 * When Craft routes a request to a Blade template that is a Livewire single-file
 * component (`<?php new class extends Component { ... } ?>`), mount it with Livewire
 * instead of rendering it as a plain view. Craft's template variables ($entry, ...)
 * are passed to the component as mount params / public properties.
 *
 * The outermost component is wrapped in the Livewire page layout
 * (#[Layout] attribute, ->layout() or config('livewire.component_layout')).
 */
class LivewireAwareBladeRenderer extends BladeRenderer
{
    private static int $depth = 0;

    /**
     * @param  string                                $template
     * @param  array<string, mixed>                  $variables
     * @param  \CraftCms\Cms\View\TemplateMode|null  $templateMode
     * @param  string|null                           $resolvedTemplate
     *
     * @return string
     * @throws \Illuminate\View\ViewException
     * @throws \Livewire\Features\SupportPageComponents\MissingLayoutException
     */
    public function renderTemplate(
        string $template,
        array $variables = [],
        ?TemplateMode $templateMode = null,
        ?string $resolvedTemplate = null,
    ): string {
        $name = $this->componentName($resolvedTemplate, $templateMode);

        if ($name === null) {
            return parent::renderTemplate($template, $variables, $templateMode, $resolvedTemplate);
        }

        $params = $this->filterParams($name, $variables);

        self::$depth++;

        try {
            // A Livewire component rendered from inside another Craft template: no layout.
            if (self::$depth > 1) {
                return Livewire::mount($name, $params);
            }

            $html = '';

            $layoutConfig = SupportPageComponents::interceptTheRenderOfTheComponentAndRetreiveTheLayoutConfiguration(
                function () use (&$html, $name, $params) {
                    $html = Livewire::mount($name, $params);
                },
            ) ?: new PageComponentConfig;

            $layoutConfig->normalizeViewNameAndParamsForBladeComponents();

            return SupportPageComponents::renderContentsIntoLayout($html, $layoutConfig);
        } finally {
            self::$depth--;
        }
    }

    /**
     * Returns the Livewire name ("craft::noticias._entry") if the template is a Livewire component.
     */
    private function componentName(?string $resolvedTemplate, ?TemplateMode $templateMode): ?string
    {
        if ($resolvedTemplate === null || ($templateMode ?? TemplateMode::get()) !== TemplateMode::Site) {
            return null;
        }

        $root = rtrim(Path::siteTemplates(), '/\\').DIRECTORY_SEPARATOR;
        $file = realpath($resolvedTemplate) ?: $resolvedTemplate;
        $root = (realpath($root) ?: rtrim($root, '/\\')).DIRECTORY_SEPARATOR;

        if (! str_starts_with($file, $root) || ! str_ends_with($file, '.blade.php')) {
            return null;
        }

        $relative = substr($file, strlen($root), -strlen('.blade.php'));
        $name = LivewireBridge::LIVEWIRE_NAMESPACE.'::'.str_replace(['/', '\\'], '.', $relative);

        // Livewire::exists() only matches files that actually are single-file components.
        return Livewire::exists($name) ? $name : null;
    }

    /**
     * Keep only the Craft variables the component asks for (public properties or mount() args);
     * anything else would otherwise end up as HTML attributes on the root element.
     *
     * @param  array<string, mixed>  $variables
     * @return array<string, mixed>
     */
    private function filterParams(string $name, array $variables): array
    {
        $component = Livewire::new($name);
        $accepted = array_keys(Utils::getPublicPropertiesDefinedOnSubclass($component));

        if (method_exists($component, 'mount')) {
            foreach (new ReflectionMethod($component, 'mount')->getParameters() as $parameter) {
                $accepted[] = $parameter->getName();
            }
        }

        return array_intersect_key($variables, array_flip($accepted));
    }
}
