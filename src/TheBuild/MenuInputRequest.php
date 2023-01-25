<?php
/**
 * @file MenuInputRequest.php
 *
 * Handles input as a multiple choice menu.
 *
 * @copyright 2018 Palantir.net, Inc.
 */

namespace TheBuild;

use Phing\Input\InputRequest;


class MenuInputRequest extends InputRequest {

  /**
   * @var string $prompt
   */
  protected $prompt;

  /**
   * @var array $options
   */
  protected $options;

  /**
   * @var int $defaultValue
   */
  protected $defaultValue = 0;

  public function setOptions($options) {
    $this->options = array_values($options);
  }

  public function getPrompt() {
    $prompt = $this->prompt . $this->getPromptChar() . "\r\n";
    foreach ($this->options as $i => $option) {
      $prompt .= "  {$i}: {$option}\r\n";
    }
    return $prompt;
  }

  public function isInputValid() {
    return (isset($this->options[$this->input]));
  }

  public function getInput() {
    return $this->options[$this->input];
  }

}
