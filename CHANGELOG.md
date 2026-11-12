# Release Notes for Livewire Bridge

## 1.0.2 - 2026-11-12

### Changed
- Plugin Store screenshots now use an English UI.

## 1.0.1 - 2026-11-12

### Changed
- New plugin icon.
- Moved classes into `View` and `Synthesizers` namespaces.
- Added screenshots to the README.

## 1.0.0 - 2026-11-12

### Added
- Craft-routed Blade templates that are Livewire single-file components are now mounted as full-page Livewire components.
- Craft template variables (`entry`, `category`, …) are passed to the component as public properties or `mount()` arguments.
- Page layout support through `#[Layout]`, `->layout()` and `livewire.component_layout`.
- `ElementSynth`, which lets Craft elements be used as Livewire component properties.
