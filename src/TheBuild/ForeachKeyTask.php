<?php

namespace TheBuild;

use Phing\Task;
use Phing\Exception\BuildException;
use Phing\Util\StringHelper;

/**
 * Phing task to run a target for each property in an array.
 */
class ForeachKeyTask extends Task {

  /**
   * Prefix of properties to iterate over.
   *
   * @var string
   */
  protected $prefix = '';

  /**
   * Name of target to execute.
   *
   * @var string
   */
  protected $target = '';

  /**
   * Name of parameter to use for the key.
   *
   * @var string
   */
  protected $keyParam = '';

  /**
   * Name of parameter to use for the prefix.
   *
   * @var string
   */
  protected $prefixParam = '';

  /**
   * Keys to ignore.
   *
   * @var array
   */
  protected $omitKeys = [];

  /**
   * Instance of PhingCallTask to use/run.
   *
   * @var \Phing\Task\System\PhingCallTask
   */
  protected $callee;

  /**
   * {@inheritdoc}
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
    $this->callee->setInheritAll(TRUE);
    $this->callee->setInheritRefs(TRUE);

    // Extract matching keys from the properties array.
    $keys = [];
    $project = $this->getProject();
    foreach (array_keys($project->getProperties()) as $name) {
      if (strpos($name, $this->prefix) === 0) {
        $property_children = substr($name, strlen($this->prefix));
        // phpcs:ignore
        [$key] = explode('.', $property_children, 2);
        $keys[$key] = $key;
      }
    }

    // Remove keys based on the 'omitKeys' attribute.
    $keys = array_diff($keys, $this->omitKeys);

    // Iterate over each extracted key.
    foreach (array_keys($keys) as $key) {
      $prop = $this->callee->createProperty();
      $prop->setOverride(TRUE);
      $prop->setName($this->keyParam);
      $prop->setValue($key);

      $prop = $this->callee->createProperty();
      $prop->setOverride(TRUE);
      $prop->setName($this->prefixParam);
      $prop->setValue($this->prefix);

      $this->callee->main();
    }
  }

  /**
   * Verify that the required attributes are set.
   */
  public function validate() {
    foreach (['prefix', 'target', 'keyParam', 'prefixParam'] as $attribute) {
      if (empty($this->$attribute)) {
        throw new BuildException("$attribute attribute is required.", $this->getLocation());
      }
    }
  }

  /**
   * Use only keys with a certain prefix.
   *
   * @param string $value
   *   The key prefix.
   */
  public function setPrefix($value) {
    if (!StringHelper::endsWith(".", $value)) {
      $value .= ".";
    }

    $this->prefix = $value;
  }

  /**
   * Set the target to run for each item.
   *
   * @param string $value
   *   Name of the target to run for each item.
   */
  public function setTarget($value) {
    $this->target = $value;
  }

  /**
   * Set the parameter name to pass to the target.
   *
   * @param string $value
   *   Name of the parameter to pass to the target.
   */
  public function setKeyParam($value) {
    $this->keyParam = $value;
  }

  /**
   * Name of the parameter where we can find the prefix.
   *
   * @param string $value
   *   The parameter name.
   */
  public function setPrefixParam($value) {
    $this->prefixParam = $value;
  }

  /**
   * Remove a list of keys from the set of properties.
   *
   * @param string $value
   *   A comma-separated list of keys to remove from the array.
   */
  public function setOmitKeys($value) {
    $this->omitKeys = array_map('trim', explode(',', $value));
  }

}
