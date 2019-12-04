# Change Log

## 2.2.1

### Changed

* Updated and loosened the version requirement for `drupal/coder`

## 2.2.0

### Changed

* Updated from Drush 8 to Drush 9
* Changed how the `config_sync_directory` is handled for Drupal 8.8 - see [drupal.org/node/3018145](https://www.drupal.org/node/3018145)

### Updating from 2.1

Your Drupal site must be running Drupal 8.8 to use the-build version 2.2 and newer.

The Drush update requires a lot of dependency math! The easiest way to resolve it is to remove the-build from the requirements, and then re-add it.

```
composer remove --dev palantirnet/the-build
composer require --dev palantirnet/the-build
```

Afterwards, you'll need to update the drush configuration in your project:

* Remove `drush/drushrc.php`
* Optional: copy `vendor/palantirnet/the-build/defaults/install/drush/drush.yml` to `drush/drush.yml`
* Migrate your site aliases by running `drush site:alias-convert` from within your VM (otherwise you'll get any global drush aliases you have set up)

## Release 2.1.2

### Changed

* Preserve file permissions when building the artifact (#130 / #138)
* Hide internal phing targets from `phing -l`, and update the default target in the build.xml template to run `phing -l` (#139)

## Release 2.1.1

### Added

* drupal-check is now part of the default code reviews (#133 / #129)

### Fixed

* New mysql package name in the default CircleCI configuration (#134)

### Updating from 2.0

These changes affect `build.xml` and `.circleci/config.yml`, which means that in addition to updating the package itself, you will either need to re-run the-build's installer script, or apply the changes manually to your copies of those files:

* [Changes to apply to your build.xml](https://github.com/palantirnet/the-build/compare/2.0.1...2.1.0#diff-3895e49cbca4d72f37a94c0656b0c772)
* [Changes to apply to your .the-build/build.circleci.yml](https://github.com/palantirnet/the-build/compare/2.0.1...2.1.0#diff-a01d410fdee850d48c771170d3205a38)
* [Changes to apply to your .the-build/build.yml](https://github.com/palantirnet/the-build/compare/2.0.1...2.1.0#diff-32200cbbe6a158d0d84a425f49b2cbef) (make sure you've updated to the-vagrant 2.4.0 so that drupal-check is installed globally on the VM)

## Release 2.0.1

### Fixed

* Fixed `settings.php` and environment configuration for Platform.sh (#126, #131)
* Fixed bugs with first runs of `phing acquia-get-backup` (#132)
* Removed deprecated config for testing the `palantirnet/drupal-skeleton` project (#128)


## Release 2.0

This release has several core architectural changes:

* Property files are now formatted as YAML
* Default property values and full documentation are now centralized in the `defaults.properties.yml` file within the-build
* Configuration for the-build is now stored in its own directory, `.the-build/`
* The build, install, and test functionality is now fully enumerated in the template `build.xml` file that gets installed into projects, so that it's easier for developers to understand and modify these targets
* Drupal multisite configurations are supported by default. Site-specific properties moved from `drupal.*` to `drupal.sites.default.*`, and there is now boilerplate for loading properties from one "active" site into `drupal.site.*`.
* Drupal config exports have moved from `conf/drupal/config/` to `config/sites/default/`, which also supports exporting config for multisites.
* Default splits for the `config_split` module are included.
* the-build now provides a default `settings.php` for Drupal, plus a host-specific `settings.HOST.php`, and running `phing build` compiles environment-specific settings to `settings.build.php`

Additional changes include:

* Rewritten install process. This code is now all in one location, and configuration prompts for values that are not generally changed from the defaults have been removed. Templates for Behat and CircleCI are more dynamic, and templates for configuring Pantheon and Platform.sh hosting have been added.
* Rewritten artifact build process. This code should now be more readable, reliable, and adaptable. Artifacts are available from the default `build.xml` file, and are suitable for deployment on Acquia, Pantheon, or generic hosting environments.
* Rewritten Acquia targets, including downloading a recent database backup.
* Applying the MIT license to the project.

[Here's the list of issues associated with the 2.0 milestone.](https://github.com/palantirnet/the-build/issues?utf8=%E2%9C%93&q=milestone%3A2.0)

### Updating to this release from 1.x

**There is no automated upgrade path for this release.** However, you can remove the previous version of the-build and install this one instead -- especially if you haven't added any heavy customization to your project's build process.

1. The install will overwrite the following files:
   * `.circleci/config.yml`
   * `.gitignore`
   * `behat.yml`
   * `build.xml`
   * `web/sites/default/settings.php` (if present)
   * `web/sites/default/settings.acquia.php` (if present)
   * `web/sites/default/settings.pantheon.php` (if present)
   * `web/sites/default/settings.platformsh.php` (if present)
   * `web/sites/default/services.yml` (if present)
   * `web/sites/default/settings.build.php` (if present)
   * `web/sites/default/services.build.yml` (if present)
   * `drush/drushrc.php` (if present)
4. Run `vendor/bin/the-build-installer`
   * You must enter the URL you're already using
   * If you select the host 'acquia', your `web/` directory will be moved to `docroot/`
   * When it offers to install Drupal for you, respond `N`
5. You've installed the new version! Now, you need to reconcile your previous configurations and integrate any customizations.
6. If the install moved your `web/` directory to `docroot/`, go ahead and add this change to git:
   ```
   git rm -f web/
   git add docroot/
   git add composer.*
   git ci -m "Moved the Drupal root in order to match Acquia (the-build 2.0 update)"
   ```
7. Move your Drupal config into the new location:
   ```
   mkdir -p config/sites
   git mv conf/drupal/config config/sites/default
   git ci -m "Moved the exported Drupal config (the-build 2.0 update)"
   ```
8. Review and re-incorporate customizations you had made to the following files:
   * `.circleci/config.yml`
   * `.gitignore`
   * `behat.yml`
   * `build.xml`
   * `conf/build.*.properties` --> `.the-build/build.*.yml` (see `docs/configuration.md`)
   * `conf/drupal/services.yml` --> `.the-build/drupal/services.build.yml`
   * `conf/drupal/settings.php` --> `.the-build/drupal/settings.build.php`
   * `conf/drupal/settings.acquia.php` --> `.the-build/drupal/settings.build-acquia.php` + `docroot/sites/default/settings.acquia.php`
   * `conf/drushrc.php` --> `drush/drushrc.php`
9. **If you're using artifact-based deployment**, review the documentation at `docs/artifacts.md`
10. **If you're using Drupal multisites**, review the documentation at `docs/drupal_multisite.md`
11. Test with `phing install`, `phing test`

----
Copyright 2019 Palantir.net, Inc.
