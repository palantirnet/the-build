# Change Log

## LITE

The goal of this release is to remove some of the templating functionality that makes the-build and drupal-skeleton harder to use and maintain.

* Removed gitignore template
* Removed settings.php templating

## 3.0.3 - November 25, 2020

### Fixed

* Updated composer cache location in CircleCI config

### Changed

* Expanded Pantheon automated deployment example
* Updated default `.gitignore` for artifacts to exclude node dependencies

## 3.0.2 - August 17, 2020

### Changed

* The artifact process now runs `composer install` with the `--ignore-platform-reqs` flag so that not all PHP extensions need to be installed when building the artifact on a CI environment

### Fixed

* Updated default CI file to run `sudo apt-get update` before installing apt dependencies

## 3.0.1 - July 9, 2020

### Removed

* Removed default installation of the "kint" Drupal module, which has been removed from the 4.0 release of [devel](https://www.drupal.org/project/devel) ([see discussion here](https://gitlab.com/drupalspoons/devel/-/issues/223#note_365147798)).

### Updating

This change does not affect existing projects.

## 3.0.0 - July 8, 2020

This release introduces Drupal 9 compatibility by removing the dependency on the `drupal/config_installer` package. You will need to update existing sites manually (see the "Updating" section below).

### Added

* New example CircleCI configuration to deploy to Acquia or Pantheon in `defaults/install/.circleci`

### Changed

* `phing install` now uses `drush site-install --existing-config` instead of the [config_installer profile](https://www.drupal.org/project/config_installer) (#145)

### Removed

* The `drupal.site.profile` property is no longer used by the default `install` target in `build.xml`. It will be removed in the 3.1 release. (#145)

### Updating

When you update to Drupal 9, you will need to update your project to remove the dependency on the `drupal/config_installer` package. In Drupal 8, these steps are optional.

1. Change your site's profile to `minimal`, because the `--existing-config` install option only works with profiles that don't implement `hook_install()`. There's a new phing command in the-build to do this:

  ```
  phing drupal-change-profile -Dnew_profile=minimal
  ```
2. Export your config:

  ```
  drush cex
  ```
3. Update the `install` target in your `build.xml`:
 * Change `<param>${drupal.site.profile}</param>` to `<option name="existing-config" />`
 * See the [build.xml diff](https://github.com/palantirnet/the-build/pull/156/files#diff-3895e49cbca4d72f37a94c0656b0c772) for details
4. Remove the `drupal/config_installer` package from your project:

  ```
  composer remove drupal/config_installer
  ```

## 2.3.2

### Fixed

* Fix the-build installation for platformsh ([PR #154](https://github.com/palantirnet/the-build/pull/155))

## 2.3.1

### Changed

* Allow using Drush 9 or Drush 10 ([PR #150](https://github.com/palantirnet/the-build/pull/150))

### Fixed

* Fix drupal-check installation on CircleCI ([PR #153](https://github.com/palantirnet/the-build/pull/153), [PR #154](https://github.com/palantirnet/the-build/pull/154))

### Updating

* If you want to use Drush 10, you can now run `composer update palantirnet/the-build --with-dependencies`.
* This release changes the default `.circleci/config.yml` to install drupal-check. If you want this change in your project, compare your CircleCI config file to `defaults/install/.circleci/config.yml`.

## 2.3.0

### Fixed

* Added Drupal 8.8's `$settings['file_temp_path']` configuration to settings.*.php (#144)
* Updated phpcs usage to fix an issue where some Drupal code was no longer subject to review, because the latest release of Coder changed the default file extensions (#148)

### Updating

* This release has settings.php configuration specific to Drupal 8.8, but these changes will only be applied to new installations of Drupal.
* If your `build.xml` uses phpcs, make the following change:

   ```
         <!-- Run PHP Code Sniffer. -->
   -     <property name="phpcs.command" value="vendor/bin/phpcs --standard=${phpcs.standard} --ignore=${phpcs.ignore} ${phpcs.directories}" />
   +     <property name="phpcs.command" value="vendor/bin/phpcs --standard=${phpcs.standard} --extensions=${phpcs.extensions} ${phpcs.directories}" />
   ```

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
