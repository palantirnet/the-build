<?php

/**
 * @file
 * Drupal settings file template for use on Platform.sh environments.
 */

// Enable/disable config_split configurations.
if (isset($_ENV['PLATFORM_BRANCH'])) {
  if ($_ENV['PLATFORM_BRANCH'] == 'master') {
    $config['config_split.config_split.production']['status'] = TRUE;
  }
  else {
    $config['config_split.config_split.staging']['status'] = TRUE;
  }
}
