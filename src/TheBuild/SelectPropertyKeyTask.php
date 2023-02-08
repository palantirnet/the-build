<?php

namespace TheBuild;

use Phing\Task;
use Phing\Exception\BuildException;
use Phing\Util\StringHelper;
use Phing\Project;

/**
 * Interactively select an option from an array of property keys.
 */
class SelectPropertyKeyTask extends Task {

  /**
   * Required. Prefix for properties to copy.
   *
   * @var string
   */
  protected $prefix = '';

  /**
   * Required. Property to populate with the selected value.
   *
   * @var string
   */
  protected $propertyName = '';

  /**
   * Message to display to the user when more than one key is available.
   *
   * @var string
   */
  protected $message = 'Select one:';

  /**
   * Keys to ignore.
   *
   * @var array
   */
  protected $omitKeys = [];

  /**
   * Copy properties.
   */
  public function main() {
    $this->validate();
    $project = $this->getProject();

    if ($existing_value = $this->project->getProperty($this->propertyName)) {
      $this->log("Using {$this->propertyName} = '{$existing_value}' (existing value)", \Project::MSG_INFO);
      return;
    }

    // Extract matching keys from the properties array.
    $keys = [];
    foreach ($project->getProperties() as $name => $value) {
      if (strpos($name, $this->prefix) === 0) {
        $property_children = substr($name, strlen($this->prefix));
        // phpcs:ignore
        [$key] = explode('.', $property_children, 2);
        $keys[$key] = $key;
      }
    }

    // Remove keys based on the 'omitKeys' attribute.
    $keys = array_diff($keys, $this->omitKeys);

    $value = NULL;

    if (count($keys) > 1) {
      // Prompt for input.
      $request = new MenuInputRequest($this->message);
      $request->setOptions($keys);

      $this->project->getInputHandler()->handleInput($request);

      $value = $request->getInput();
    }
    elseif (count($keys) == 1) {
      $value = current($keys);
      $this->log("Using {$this->propertyName} = '{$value}' (one value found)", \Project::MSG_INFO);
    }
    else {
      $this->log("No properties found with prefix '{$this->prefix}'", \Project::MSG_WARN);
    }

    if ($value) {
      $project->setNewProperty($this->propertyName, $value);
    }
  }

  /**
   * Verify that the required attributes are set.
   */
  public function validate() {
    foreach (['prefix', 'propertyName'] as $attribute) {
      if (empty($this->$attribute)) {
        throw new BuildException("$attribute attribute is required.", $this->getLocation());
      }
    }
  }

  /**
   * Set the prefix for which options will be shown.
   *
   * @param string $value
   *   Keys with this prefix will be provided as options.
   */
  public function setPrefix($value) {
    if (!\StringHelper::endsWith(".", $value)) {
      $value .= ".";
    }

    $this->prefix = $value;
  }

  /**
   * Set the destination property.
   *
   * @param string $value
   *   Property name for the selection result.
   */
  public function setPropertyName($value) {
    $this->propertyName = $value;
  }

  /**
   * Set the message.
   *
   * @param string $value
   *   Message to display with the options.
   */
  public function setMessage($value) {
    $this->message = $value;
  }

  /**
   * Exclude some of the property keys from the options.
   *
   * @param string $value
   *   A comma-separated list of keys to exclude.
   */
  public function setOmitKeys($value) {
    $this->omitKeys = array_map('trim', explode(',', $value));
  }

}
