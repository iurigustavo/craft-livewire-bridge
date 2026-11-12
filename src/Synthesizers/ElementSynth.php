<?php

declare(strict_types=1);

namespace IuriGustavo\LivewireBridge\Synthesizers;

use CraftCms\Cms\Element\Contracts\ElementInterface;
use CraftCms\Cms\Support\Facades\Elements;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

/**
 * Stores Craft elements in the Livewire snapshot as a reference (class + id + site)
 * and re-queries them on every Livewire request, like Livewire does for Eloquent models.
 */
class ElementSynth extends Synth
{
    public static $key = 'craft-el';

    public static function match($target): bool
    {
        return $target instanceof ElementInterface;
    }

    public function dehydrate(ElementInterface $target): array
    {
        return [null, [
            'class' => $target::class,
            'id' => $target->id,
            'siteId' => $target->siteId,
        ]];
    }

    public function hydrate($value, array $meta): ?ElementInterface
    {
        $class = $meta['class'] ?? null;

        // The snapshot checksum already prevents tampering, but double-check anyway.
        if (! is_string($class) || ! is_a($class, ElementInterface::class, true)) {
            throw new \RuntimeException('Livewire: invalid Craft element class.');
        }

        if (empty($meta['id'])) {
            return null;
        }

        return Elements::getElementById((int) $meta['id'], $class, $meta['siteId'] ?? null);
    }
}
