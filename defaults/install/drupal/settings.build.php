<?php

/**
 * @file Drupal settings file template for use on development environments.
 */

$databases['default']['default'] = array(
  'driver' => 'mysql',
  'database' => '${drupal.site.database.database}',
  'username' => '${drupal.site.database.username}',
  'password' => '${drupal.site.database.password}',
  'host' => '${drupal.site.database.host}',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
);

$settings['config_sync_directory'] = '${drupal.site.config_sync_directory}';

$settings['hash_salt'] = '${drupal.site.hash_salt}';
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
$settings['container_yamls'][] = __DIR__ . '/services.build.yml';

$settings['file_public_path'] = '${drupal.site.settings.file_public_path}';
$settings['file_private_path'] = '${drupal.site.settings.file_private_path}';

// Enable/disable config_split configurations. To simulate other config split
// environments, change "development" to either "staging" or "production", then run:
//   drush cr && drush cim -y
$config['config_split.config_split.development']['status'] = TRUE;

// Disable the render cache.
$settings['cache']['bins']['render'] = 'cache.backend.null';

// Disable the dynamic page cache.
$settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';

// Disable CSS and JS aggregation.
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

// Don't chmod the sites subdirectory.
$settings['skip_permissions_hardening'] = TRUE;

// Turn errors up.
$config['system.logging']['error_level'] = 'verbose';
