# Changelog

All notable changes to `tamirrental/laravel-text-extractor` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- This changelog.

### Changed
- Development dependencies moved to Pest 5 (`pestphp/pest`, `pestphp/pest-plugin-laravel`, `pestphp/pest-plugin-type-coverage`).
- `composer.json` homepage and README badges now point to the `Tamir-Rental-Ltd` GitHub organization.

## [1.0.4] - 2026-10-30

### Added
- Images over 10 MB are shrunk (re-encoded as JPEG, then resized if needed) before uploading to Koncile AI; PDFs and smaller files are sent unchanged ([#23](https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/pull/23)).

### Changed
- Requires `intervention/image` 4.x and the `ext-gd` and `ext-exif` extensions.

## [1.0.3] - 2026-08-20

### Changed
- HTTP 402 usage-limit responses from Koncile AI fail the extraction with a stable message (`USAGE_LIMIT_ERROR_MESSAGE`) and are not retried ([#22](https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/pull/22)).

## [1.0.2] - 2026-08-19

### Added
- Koncile AI uploads are throttled to a shared per-second budget (`requests_per_second` config) via the `ThrottlesRequests` trait ([#14](https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/pull/14)).

### Security
- Updated `composer.lock` to resolve open Dependabot security alerts ([#15](https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/pull/15)).
- GitHub Actions workflows use least-privilege `GITHUB_TOKEN` permissions ([#19](https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/pull/19), [#20](https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/pull/20)).

## [1.0.1] - 2026-04-15

### Added
- Laravel 13 support ([#12](https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/pull/12)).

## [1.0.0] - 2026-03-05

### Added
- Initial release: `DocumentExtraction` facade and fluent `PendingExtraction` builder, `DocumentExtractionService`, `DocumentExtractionProvider` contract, `DocumentExtraction` model and factory, `DocumentExtractionRequested` event, and the Koncile AI provider.

[Unreleased]: https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/compare/v1.0.4...HEAD
[1.0.4]: https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/Tamir-Rental-Ltd/laravel-text-extractor/releases/tag/v1.0.0
