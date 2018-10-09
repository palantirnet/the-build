<?php
/**
 * @file CopyPropertiesTask.php
 *
 * Copy properties matching a prefix to another prefix.
 *
 * @code
 *   <copyproperties fromPrefix="drupal.sites.default" toPrefix="drupal.site" override="true" />
 * @endcode
 *
 * @copyright 2018 Palantir.net, Inc.
 */

namespace TheBuild;

use BuildException;
use StringHelper;


class CopyPropertiesTask extends \Task {

  /**
   * @var string
   * Prefix for properties to copy.
   */
  protected $fromPrefix = '';

  /**
   * @var string
   * Prefix for properties to create/update.
   */
  protected $toPrefix = '';
  
  /**
   * @var bool
   * Whether to overwrite the existing properties.
   */
  protected $override = true;

  /**
   * @var string
   */
  protected $propertyMethod = 'setProperty';


  /**
   * Use either Project::setProperty() or Project::setNewProperty() based on
   * whether we're overriding values or not.
   */
  public function init() {
    parent::init();
    $this->propertyMethod = $this->override ? 'setProperty' : 'setNewProperty';
  }

  /**
   * Copy properties.
   */
  public function main() {
    $this->validate();

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
      throw new BuildException("fromPrefix attribute is required.", $this->location);
    }

    if (empty($this->toPrefix)) {
      throw new BuildException("toPrefix attribute is required.", $this->location);
    }
  }


  /**
   * @param string $prefix
   */
  public function setFromPrefix($prefix) {
    if (!StringHelper::endsWith(".", $prefix)) {
      $prefix .= ".";
    }

    $this->fromPrefix = $prefix;
  }

  /**
   * @param string $prefix
   */
  public function setToPrefix($prefix) {
    if (!StringHelper::endsWith(".", $prefix)) {
      $prefix .= ".";
    }

    $this->toPrefix = $prefix;
  }

  /**
   * @param bool $override
   */
  public function setOverride($override) {
    $this->override = $override;
  }

}
