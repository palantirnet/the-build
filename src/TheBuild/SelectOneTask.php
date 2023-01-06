<?php

namespace TheBuild;

/**
 * Allow the user to select one option from a list.
 */
class SelectOneTask extends \Task {

  /**
   * Required. List of values to select among.
   *
   * @var string
   */
  protected $list = '';

  /**
   * String to split the list by.
   *
   * @var string
   */
  protected $delimiter = ',';

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
   * Select menu.
   */
  public function main() {
    $this->validate();

    $project = $this->getProject();

    if ($existing_value = $this->project->getProperty($this->propertyName)) {
      $this->log("Using {$this->propertyName} = '{$existing_value}' (existing value)", \Project::MSG_INFO);
      return;
    }

    $keys = array_map('trim', explode($this->delimiter, $this->list));

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

    if ($value) {
      $project->setNewProperty($this->propertyName, $value);
    }
  }

  /**
   * Verify that the required attributes are set.
   */
  public function validate() {
    foreach (['list', 'propertyName'] as $attribute) {
      if (empty($this->$attribute)) {
        throw new \BuildException("$attribute attribute is required.", $this->location);
      }
    }
  }

  /**
   * Set the list of options.
   *
   * @param string $value
   *   List of options.
   */
  public function setList($value) {
    $this->list = $value;
  }

  /**
   * Set the options delimiter.
   *
   * @param string $value
   *   A delimiter.
   */
  public function setDelimiter(string $value) {
    $this->delimiter = $value;
  }

  /**
   * Set the name of the result property.
   *
   * @param string $value
   *   Property name for the result.
   */
  public function setPropertyName(string $value) {
    $this->propertyName = $value;
  }

  /**
   * Set the message.
   *
   * @param string $value
   *   Message to present with the options.
   */
  public function setMessage(string $value) {
    $this->message = $value;
  }

}
