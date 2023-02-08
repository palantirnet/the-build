<?php

namespace TheBuild;

use Phing\Input\InputRequest;

/**
 * Input interface that prompts the user to select from a menu of options.
 */
class MenuInputRequest extends \InputRequest {

  /**
   * Prompt to display with the menu.
   *
   * @var string
   */
  protected $prompt;

  /**
   * Array of menu option labels.
   *
   * @var array
   */
  protected $options;

  /**
   * Default menu option to select.
   *
   * @var int
   */
  protected $defaultValue = 0;

  /**
   * Set the options to display in the menu.
   *
   * @param array $options
   *   Menu options to display.
   */
  public function setOptions(array $options) {
    $this->options = array_values($options);
  }

  /**
   * Generate the menu prompt.
   */
  public function getPrompt() {
    $prompt = $this->prompt . $this->getPromptChar() . "\r\n";
    foreach ($this->options as $i => $option) {
      $prompt .= "  {$i}: {$option}\r\n";
    }
    return $prompt;
  }

  /**
   * Validate the menu selection.
   */
  public function isInputValid() {
    return (isset($this->options[$this->input]));
  }

  /**
   * Return the menu selection.
   */
  public function getInput() {
    return $this->options[$this->input];
  }

}
