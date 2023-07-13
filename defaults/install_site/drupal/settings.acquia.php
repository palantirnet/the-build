<?php

/**
 * @file
 * Drupal settings file for use with Acquia hosted sites.
 *
 * This file should be loaded on all environments, Acquia or not, since the settings here
 * will affect the behavior of the site.
 */

$config['acquia_connector.settings']['hide_signup_messages'] = TRUE;

$settings['cache']['default'] = 'cache.backend.database';

// Include the Acquia database connection and other config.
if (file_exists('/var/www/site-php')) {
  require "/var/www/site-php/{$_ENV['AH_SITE_GROUP']}/{$_ENV['AH_SITE_GROUP']}-settings.inc";
}
