<?php

$databases = array();
$databases['default']['default'] = array(
  'driver' => 'mysql',
  'database' => '${drupal.database.database}',
  'username' => '${drupal.database.username}',
  'password' => '${drupal.database.password}',
  'host' => '${drupal.database.host}',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
);

// Memcache configuration.
$settings['memcache']['servers'] = ['127.0.0.1:11211' => 'default'];
$settings['memcache']['bins'] = ['default' => 'default'];
$settings['memcache']['key_prefix'] = '';

// Use memcache as the default cache backend.
// Uncomment this **after** the site is installed.
// $settings['cache']['default'] = 'cache.backend.memcache';

$config_directories = array();
$config_directories[CONFIG_SYNC_DIRECTORY] = '${drupal.config_sync_directory}';

$settings['hash_salt'] = '${drupal.hash_salt}';
$settings['update_free_access'] = FALSE;
$settings['container_yamls'][] = __DIR__ . '/services.yml';

$settings['file_public_path'] = '${drupal.settings.file_public_path}';
$settings['file_private_path'] = '${drupal.settings.file_private_path}';
