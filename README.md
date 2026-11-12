# Livewire Bridge for Craft CMS

Use [Livewire 4](https://livewire.laravel.com) single-file components as Craft CMS 6 templates.

When Craft routes a request to a Blade template (an entry's URI, a section template, a custom route…) and that template is a Livewire single-file component, Livewire Bridge mounts it as a **full-page Livewire component** instead of rendering it as a plain view. Craft's template variables such as `$entry` are handed to the component, and Craft elements work as Livewire properties across requests.

## Requirements

- Craft CMS 6.0.0-beta.1 or later
- Livewire 4.0 or later
- PHP 8.4 or later

## Installation

```bash
composer require iurigustavo/craft-livewire-bridge
php artisan craft:plugin:install livewire-bridge
```

You can also install it from the Plugin Store in the control panel.

## Usage

1. In **Settings → Sections**, set the section's template to a Blade file, e.g. `news/_entry.blade.php`.
2. Write that template as a Livewire single-file component:

```php
<?php

use CraftCms\Cms\Entry\Elements\Entry;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component {
    public Entry $entry;

    public int $likes = 0;

    public function rendering($view): void
    {
        $view->title($this->entry->title);
    }

    public function like(): void
    {
        $this->likes++;
    }
};
?>

<article>
    <h1>{{ $entry->title }}</h1>

    <button wire:click="like">Like ({{ $likes }})</button>
</article>
```

That's it. Visiting the entry's URL renders the component inside the layout, and `wire:click`, `wire:model`, validation, `wire:navigate`, etc. work as usual.

### Passing Craft variables

Craft variables are passed to the component only when the component asks for them, either as a **public property** with the same name or as a **`mount()` argument**:

```php
public Entry $entry;                       // filled automatically

public function mount(Entry $entry): void  // or received here
{
    // ...
}
```

Variables the component doesn't declare are ignored, so they don't leak into the HTML as attributes.

### Layouts

The outermost component is wrapped in a layout, resolved the same way as Livewire's own page components:

1. the `#[Layout('...')]` attribute,
2. `$view->layout('...')` in `render()`/`rendering()`,
3. `config('livewire.component_layout')` (default `layouts::app`).

The layout receives the component output in `$slot`, and `$title` when you use `#[Title]` or `$view->title()`. Livewire components rendered from inside another Craft template aren't wrapped in a layout.

### Craft elements as properties

Any Craft element (entries, categories, assets, users…) can be a Livewire property. Only a reference (`class`, `id`, `siteId`) is stored in the snapshot, and the element is fetched again on every Livewire request, the same way Livewire handles Eloquent models.

### Component names

Templates are registered under the `craft::` Livewire namespace, which points to Craft's site templates folder. For example, `resources/views/news/_entry.blade.php` becomes `craft::news._entry`, so you can also embed it elsewhere:

```blade
<livewire:craft::news._entry :entry="$entry" />
```

## Known limitations

- Collections or arrays of elements (e.g. an `ElementCollection` property) aren't synthesized. Store IDs in a `#[Locked]` property or use a computed property instead.
- During Live Preview, unsaved changes are lost after the first Livewire request, because the element is fetched again from the database.
- Only single-file components are supported as Craft-routed templates.

## License

MIT. See [LICENSE.md](LICENSE.md).
