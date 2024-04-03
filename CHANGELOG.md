# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- add Craft 5 compatibility


## [3.1.1] - 2024-01-05

### Fixed

- fix a bug that prevented listing craft console commands


## [3.1.0] - 2024-01-03

### Added

- add the `MailerComponentConfiguratorModule` class

### Changed

- restore Craft 3 compatibility in addition to Craft 4


## [3.0.0] — 2022-06-27

### Changed

- [BREAKING]: Craft 4 compatibility


## [2.0.1] — 2022-06-27

### Fixed

- Fix a bug that caused system messages not to be registered


## [2.0.0] - 2022-06-03

### Added

- Allow declaring permissions nested under Craft core permissions
- Add the ability to register Yii components that do NOT override those
    defined in `app.php`

### Changed

- [BREAKING]: Method `getuserPermissions()` renamed to `getUserPermission()`
- Set translations `sourceLanguage` to english

### Fixed

- Load twig extensions [the recommanded way](https://craftcms.com/docs/3.x/extend/extending-twig.html#register-a-twig-extension)
    This fixes a bug when running functional tests on a site that uses twig
    extensions


## [1.1.0] - 2021-09-21

### Added

- Easily register system messages


## [1.0.0] - 2021-09-13

### Added

- Automatically register a translation category
- Automatically register CP template roots
- Automatically register site template roots
- Automatically set controller namespace (for web/console requests)
- Automatically register aliases
- Easily register custom twig extensions
- Easily register custom CP nav items
- Easily register custom user permissions
- Easily register custom Craft variables
- Easily register custom element types
- Easily register view hooks

[Unreleased]: https://github.com/nstCactus/craft-utils/compare/3.1.1...HEAD
[3.1.1]: https://github.com/nstCactus/craft-utils/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/nstCactus/craft-utils/compare/3.0.0...3.1.0
[3.0.0]: https://github.com/nstCactus/craft-utils/compare/2.0.1...3.0.0
[2.0.1]: https://github.com/nstCactus/craft-utils/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/nstCactus/craft-utils/compare/1.1.0...2.0.0
[1.1.0]: https://github.com/nstCactus/craft-utils/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/nstCactus/craft-utils/releases/tag/1.0.0
