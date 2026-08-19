---
name: testing-laravel-text-extractor
description: How to verify the tamirrental/laravel-text-extractor Laravel package locally (clean composer install, security audit, Pest suite, Pint) — use when testing dependency bumps, lockfile updates, or any PR in this repo.
---

# Testing laravel-text-extractor

This is a Laravel **package** — there is no runnable app, UI, or dev server. All verification is CLI-only,
so do not start a browser or a screen recording for it.

## Environment
- Requires PHP 8.4 (`composer.json` requires `php: ^8.4`) and Composer 2.x. Both are preinstalled on the
  standard Devin box (`php -v` → 8.4.x, `composer --version` → 2.x).
- Network access to `repo.packagist.org` is needed for `composer install` and `composer audit`.
- No secrets/credentials are needed for the test suite (HTTP calls are faked by Pest).

## Standard verification sequence (mirrors .github/workflows/pr-ci.yml)
```bash
cd /home/ubuntu/repos/laravel-text-extractor
rm -rf vendor
composer install --no-interaction --no-scripts --prefer-dist   # expect "Installing dependencies from lock file"
composer validate --no-check-publish --check-lock              # only the harmless "version field" warning
vendor/bin/pest --colors=never                                 # expect all tests passing (49 tests / 102 assertions as of TR-992)
vendor/bin/pint --test                                         # expect PASS, 21 files
```
`composer validate` always emits `- The version field is present, ...`; that warning is expected and not a failure.

## Extra checks for lockfile / dependency-bump PRs
- `composer audit` and `composer audit --no-dev` → expect "No security vulnerability advisories found."
- Differential control (proves the bump actually did something): copy `composer.json` + `composer.lock`
  from the base branch into a scratch dir and run `composer audit --locked --format=summary` there;
  the base should report advisories while the PR lock reports none.
- Cross-check installed versions against the lock by diffing `composer show --format=json` against the
  `packages` + `packages-dev` entries of `composer.lock` (catches silent resolution drift).
- Confirm `git status --porcelain composer.json composer.lock` is empty after installing — `composer install`
  must never rewrite the lock.

## Devin Secrets Needed
None.
