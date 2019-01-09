<?php

/**
 * @file
 * Drupal settings file template for use on Acquia environments.
 */

$settings['trusted_host_patterns'][] = "^{$_ENV['AH_SITE_ENVIRONMENT']}dev.prod.acquia-sites.com";
$settings['trusted_host_patterns'][] = "^{$_ENV['AH_SITE_ENVIRONMENT']}stg.prod.acquia-sites.com";

$settings['file_public_path'] = '${drupal.site.settings.file_public_path}';
$settings['file_private_path'] = "/mnt/gfs/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/files-private";
$config['system.file']['path']['temporary'] = $_ENV['TEMP'];

$config_directories = [];
$config_directories[CONFIG_SYNC_DIRECTORY] = '${drupal.site.config_sync_directory}';

// Enable/disable config_split configurations.
if (isset($_ENV['AH_PRODUCTION']) && $_ENV['AH_PRODUCTION']) {
  $config['config_split.config_split.production']['status'] = TRUE;
}
elseif (isset($_ENV['AH_NON_PRODUCTION']) && $_ENV['AH_NON_PRODUCTION']) {
  $config['config_split.config_split.staging']['status'] = TRUE;
}

//// Add an htaccess prompt on dev and staging environments.
//// @see https://docs.acquia.com/articles/password-protect-your-non-production-environments-acquia-hosting#phpfpm

// Make sure Drush keeps working.
// Modified from function drush_verify_cli()
$cli = (php_sapi_name() == 'cli');

// PASSWORD-PROTECT NON-PRODUCTION SITES (i.e. staging/dev)
if (!$cli && (isset($_ENV['AH_NON_PRODUCTION']) && $_ENV['AH_NON_PRODUCTION'])) {
  $username = 'palantir';
  $password = 'some mediocre password';
  if (!(isset($_SERVER['PHP_AUTH_USER']) && ($_SERVER['PHP_AUTH_USER']==$username && $_SERVER['PHP_AUTH_PW']==$password))) {
    header('WWW-Authenticate: Basic realm="This site is protected"');
    header('HTTP/1.0 401 Unauthorized');
    // Fallback message when the user presses cancel / escape
    echo 'Access denied';
    exit;
  }
}
