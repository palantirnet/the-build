<?php
/**
 * @file SelectOneTask.php
 *
 * Interactively select one item from a comma-separated list.
 *
 * - If the propertyName property is already set, the task does nothing
 * - If there is only one item in the list, the user is not prompted
 * - If the list is empty, validation fails
 * - If there are multiple items in the list, the user will be prompted to
 *   select one using a multiple choice menu
 *
 * @code
 *   <taskdef name="selectone" classname="TheBuild\SelectOneTask" />
 *   <selectone list="red,green,blue" propertyName="color" />
 *   <selectone list="red:green:blue" propertyName="color" delimiter=":" message="Choose a color:" />
 * @endcode
 *
 * @copyright 2018 Palantir.net, Inc.
 */

namespace TheBuild;

use BuildException;
use Project;


class SelectOneTask extends \Task {

  /**
   * @var string
   * Required. List of values to select among.
   */
  protected $list = '';

  /**
   * @var string
   * String to split the list by.
   */
  protected $delimiter = ',';

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
   * Select menu.
   */
  public function main() {
    $this->validate();
    
    $project = $this->getProject();

    if ($existing_value = $this->project->getProperty($this->propertyName)) {
      $this->log("Using {$this->propertyName} = '{$existing_value}' (existing value)", Project::MSG_INFO);
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
      $this->log("Using {$this->propertyName} = '{$value}' (one value found)", Project::MSG_INFO);
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
        throw new BuildException("$attribute attribute is required.", $this->location);
      }
    }
  }

  /**
   * @param string $value
   */
  public function setList($value) {
    $this->list = $value;
  }

  /**
   * @param string $value
   */
  public function setDelimiter($value) {
    $this->delimiter = $value;
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

}
