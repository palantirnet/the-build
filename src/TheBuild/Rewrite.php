<?php

namespace TheBuild;

use Composer\Script\Event;

/**
 * Rewrite placeholders in files.
 */
class Rewrite {

  /**
   * Rewrite placeholders in scaffolding files.
   */
  public static function replacePlaceholder(Event $event) {
    $io = $event->getIO();

    $substitutions = self::getSubstitutions($event);
    $io->write("<info>JSON data: " . print_r($substitutions, TRUE) . "</info>");

    $files = self::getFiles($event);
    foreach ($files as $file) {
      $io->write("<info>Updating file: " . $file . "</info>");

      if (!file_exists($file)) {
        $io->write("<info>File not found: " . $file . "</info>");
        continue;
      }

      try {
        $contents = file_get_contents($file);
        $contents = str_replace(array_keys($substitutions), array_values($substitutions), $contents);
        file_put_contents($file, $contents);
      }
      catch (\Exception $e) {
        $io->error($e->getMessage());
        // Error.
        return 1;
      }
    }
  }

  protected static function getSubstitutions($event) {
    $substitutions = [
      'PLACEHOLDER_DRUPAL_ROOT' => $event->getComposer()->getPackage()->getExtra()['drupal-scaffold']['locations']['web-root'],
      'PLACEHOLDER_PROJECT_ROOT' => $event->getComposer()->getConfig()->get('vendor-dir') . '/../',
      'PLACEHOLDER_PROJECT_NAME' => explode('/', $event->getComposer()->getPackage()->getName())[1],
      'PLACEHOLDER_HOST' => 'acquia',
    ];

    $substitutions['PLACEHOLDER_URL'] = "https://{$substitutions['PLACEHOLDER_PROJECT_NAME']}.ddev.site";

    return $substitutions;
  }

  protected static function getFiles($event) {
    $fileMapping = $event->getComposer()->getPackage()->getExtra()['drupal-scaffold']['file-mapping'];

    $substitutions = self::getSubstitutions($event);
    $file_substitutions = [
      '[web-root]' => $substitutions['PLACEHOLDER_DRUPAL_ROOT'],
      '[project-root]' => $substitutions['PLACEHOLDER_PROJECT_ROOT'],
    ];

    $files = [];
    foreach ($fileMapping as $file => $source) {
      $files[] = str_replace(array_keys($file_substitutions), array_values($file_substitutions), $file);
    }

    return $files;
  }

}
