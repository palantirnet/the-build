<?php
/**
 * @file ForeachKeyTask.php
 *
 * Iterate over property values.
 *
 * @code
 *   <foreachkey prefix="drupal.sites" target="mytarget" keyParam="key" prefixParam="prefix" />
 * @endcode
 *
 * @copyright 2018 Palantir.net, Inc.
 */

namespace TheBuild;

use BuildException;
use StringHelper;


class ForeachKeyTask extends \Task {

  /**
   * @var string
   * Prefix of properties to iterate over.
   */
  protected $prefix = '';

  /**
   * @var string
   * Name of target to execute.
   */
  protected $target = '';

  /**
   * @var string
   * Name of parameter to use for the key.
   */
  protected $keyParam = '';

  /**
   * @var string
   * Name of parameter to use for the prefix.
   */
  protected $prefixParam = '';

  /**
   * @var \PhingCallTask
   */
  protected $callee;

  /**
   *
   */
  public function init() {
    parent::init();

    $this->callee = $this->project->createTask("phingcall");
    $this->callee->setOwningTarget($this->getOwningTarget());
    $this->callee->setTaskName($this->getTaskName());
    $this->callee->setLocation($this->getLocation());
    $this->callee->init();
  }

  /**
   * Copy properties.
   */
  public function main() {
    $this->validate();

    $this->callee->setTarget($this->target);
    $this->callee->setInheritAll(true);
    $this->callee->setInheritRefs(true);

    // Extract matching keys from the properties array.
    $keys = [];
    $project = $this->getProject();
    foreach ($project->getProperties() as $name => $value) {
      if (strpos($name, $this->prefix) === 0) {
        $property_children = substr($name, strlen($this->prefix));
        list($key, $property_grandchildren) = explode('.', $property_children, 2);
        $keys[$key] = $this->prefix;
      }
    }

    // Iterate over each extracted key.
    foreach ($keys as $key => $prefix) {
      $prop = $this->callee->createProperty();
      $prop->setOverride(true);
      $prop->setName($this->keyParam);
      $prop->setValue($key);

      $prop = $this->callee->createProperty();
      $prop->setOverride(true);
      $prop->setName($this->prefixParam);
      $prop->setValue($prefix);

      $this->callee->main();
    }
  }


  /**
   * Verify that the required attributes are set.
   */
  public function validate() {
    foreach (['prefix', 'target', 'keyParam', 'prefixParam'] as $attribute) {
      if (empty($this->$attribute)) {
        throw new BuildException("$attribute attribute is required.", $this->location);
      }
    }
  }


  /**
   * @param string $value
   */
  public function setPrefix($value) {
    if (!StringHelper::endsWith(".", $value)) {
      $value .= ".";
    }

    $this->prefix = $value;
  }

  /**
   * @param string $value
   */
  public function setTarget($value) {
    $this->target = $value;
  }

  /**
   * @param string $value
   */
  public function setKeyParam($value) {
    $this->keyParam = $value;
  }

  /**
   * @param string $value
   */
  public function setPrefixParam($value) {
    $this->prefixParam = $value;
  }

}
