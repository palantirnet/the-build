<?php

$databases = array();
$databases['default']['default'] = array(
  'driver' => 'mysql',
  'database' => '@database.database@',
  'username' => '@database.username@',
  'password' => '@database.password@',
  'host' => '@database.host@',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
);

$config_directories = array();
$config_directories[CONFIG_SYNC_DIRECTORY] = '@drupal.config_sync_directory@';

$settings['hash_salt'] = '@hash_salt@';
$settings['update_free_access'] = FALSE;
$settings['container_yamls'][] = __DIR__ . '/services.yml';

$settings['file_public_path'] = '@settings.file_public_path@';
$settings['file_private_path'] = '@settings.file_private_path@';
