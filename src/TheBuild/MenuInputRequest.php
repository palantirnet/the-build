<?php

namespace TheBuild;

/**
 *
 */
class MenuInputRequest extends \InputRequest {

  /**
   * @var string
   */
  protected $prompt;

  /**
   * @var array
   */
  protected $options;

  /**
   * @var int
   */
  protected $defaultValue = 0;

  /**
   *
   */
  public function setOptions($options) {
    $this->options = array_values($options);
  }

  /**
   *
   */
  public function getPrompt() {
    $prompt = $this->prompt . $this->getPromptChar() . "\r\n";
    foreach ($this->options as $i => $option) {
      $prompt .= "  {$i}: {$option}\r\n";
    }
    return $prompt;
  }

  /**
   *
   */
  public function isInputValid() {
    return (isset($this->options[$this->input]));
  }

  /**
   *
   */
  public function getInput() {
    return $this->options[$this->input];
  }

}
