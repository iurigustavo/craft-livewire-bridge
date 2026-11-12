# Release Notes for Livewire Bridge

## 1.0.0 - 2026-11-12

### Added
- Craft-routed Blade templates that are Livewire single-file components are now mounted as full-page Livewire components.
- Craft template variables (`entry`, `category`, …) are passed to the component as public properties or `mount()` arguments.
- Page layout support through `#[Layout]`, `->layout()` and `livewire.component_layout`.
- `ElementSynth`, which lets Craft elements be used as Livewire component properties.
