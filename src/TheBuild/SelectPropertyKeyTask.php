<?php

namespace TheBuild;

/**
 *
 */
class SelectPropertyKeyTask extends \Task {

  /**
   * @var string
   * Required. Prefix for properties to copy.
   */
  protected $prefix = '';

  /**
   * @var string
   * Required. Property to populate with the selected value.
   */
  protected $propertyName = '';

  /**
   * @var string
   * Message to display to the user when more than one key is available.
   */
  protected $message = 'Select one:';

  /**
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
        [$key, $property_grandchildren] = explode('.', $property_children, 2);
        $keys[$key] = $key;
      }
    }

    // Remove keys based on the 'omitKeys' attribute.
    $keys = array_diff($keys, $this->omitKeys);

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
        throw new \BuildException("$attribute attribute is required.", $this->location);
      }
    }
  }

  /**
   * @param string $value
   */
  public function setPrefix($value) {
    if (!\StringHelper::endsWith(".", $value)) {
      $value .= ".";
    }

    $this->prefix = $value;
  }

  /**
   * @param string $value
   */
  public function setPropertyName($value) {
    $this->propertyName = $value;
  }

  /**
   * @param string $value
   */
  public function setMessage($value) {
    $this->message = $value;
  }

  /**
   * @param string $value
   */
  public function setOmitKeys($value) {
    $this->omitKeys = array_map('trim', explode(',', $value));
  }

}
