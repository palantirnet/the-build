<?php

namespace TheBuild;

use Phing\Task;
use Phing\Exception\BuildException;
use Phing\Util\StringHelper;

/**
 * Copy properties matching a prefix to properties with a different prefix.
 */
class CopyPropertiesTask extends Task {

  /**
   * Prefix for properties to copy.
   *
   * @var string
   */
  protected $fromPrefix = '';

  /**
   * Prefix for properties to create/update.
   *
   * @var string
   */
  protected $toPrefix = '';

  /**
   * Whether to overwrite the existing properties.
   *
   * @var bool
   */
  protected $override = TRUE;

  /**
   * Internal method to use for creating/updating properties.
   *
   * @var string
   */
  protected $propertyMethod = 'setProperty';

  /**
   * Copy properties.
   */
  public function main() {
    $this->validate();

    // Use either Project::setProperty() or Project::setNewProperty() based on
    // whether we're overriding values or not.
    $this->propertyMethod = $this->override ? 'setProperty' : 'setNewProperty';

    $project = $this->getProject();
    foreach ($project->getProperties() as $name => $value) {
      if (strpos($name, $this->fromPrefix) === 0) {
        $new_name = $this->toPrefix . substr($name, strlen($this->fromPrefix));
        $project->{$this->propertyMethod}($new_name, $value);
      }
    }
  }

  /**
   * Verify that the required attributes are set.
   */
  public function validate() {
    if (empty($this->fromPrefix)) {
      throw new BuildException("fromPrefix attribute is required.", $this->getLocation());
    }

    if (empty($this->toPrefix)) {
      throw new BuildException("toPrefix attribute is required.", $this->getLocation());
    }
  }

  /**
   * Set the source property prefix.
   *
   * @param string $prefix
   *   Prefix to copy properties from.
   */
  public function setFromPrefix($prefix) {
    if (!StringHelper::endsWith(".", $prefix)) {
      $prefix .= ".";
    }

    $this->fromPrefix = $prefix;
  }

  /**
   * Set the destination property prefix.
   *
   * @param string $prefix
   *   Prefix to copy properties into.
   */
  public function setToPrefix($prefix) {
    if (!StringHelper::endsWith(".", $prefix)) {
      $prefix .= ".";
    }

    $this->toPrefix = $prefix;
  }

  /**
   * Set override.
   *
   * @param bool $override
   *   Whether to override existing values.
   */
  public function setOverride($override) {
    $this->override = $override;
  }

}
