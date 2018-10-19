<?php
/**
 * @file AcquiaTask.php
 *

 will use acquia.cloud.endpoint
 will use acquia.cloud.email and acquia.cloud.key if available
 
 credentialsFile = ~/.acquia/cloudapi.conf
 email = acquia.cloud.email
 key = acquia.cloud.key
 endpoint = https://cloudapi.acquia.com/v1

 *
 * @copyright 2018 Palantir.net, Inc.
 */

namespace TheBuild\Acquia;

use BuildException;
use HTTP_Request2;
use PhingFile;

abstract class AcquiaTask extends \Task {

  /**
   * @var \PhingFile
   */
  protected $credentialsFile;
  protected $email;
  protected $key;
  protected $endpoint = 'https://cloudapi.acquia.com/v1';

  protected $_required_keys = ['email', 'key'];
  protected $setup = FALSE;

  public function setCredentialsFile(\PhingFile $file) {
    $this->credentialsFile = new \PhingFile($file);
  }

  public function setEmail($value) {
    $this->email = $value;
  }

  public function setKey($value) {
    $this->key = $value;
  }

  public function setEndpoint($value) {
    $this->endpoint = rtrim($value, '/');
  }

  public function main() {
    $this->loadCredentials();
    $this->validate();
  }

  protected function loadCredentials() {
    if (empty($this->email) || empty($this->key)) {
      if (empty($this->credentialsFile)) {
        $this->credentialsFile = new PhingFile($_SERVER['HOME'] . '/.acquia/cloudapi.conf');
      }

      if (!file_exists($this->credentialsFile) || !is_readable($this->credentialsFile)) {
        throw new BuildException("Acquia Cloud credentials file '{$this->credentialsFile}' is not available.");
      }

      $contents = file_get_contents($this->credentialsFile);
      $creds = json_decode($contents, TRUE);

      $this->setEmail($creds['email']);
      $this->setKey($creds['key']);
    }
  }

  protected function validate() {
    foreach ($this->_required_keys as $attribute) {
      if (empty($this->$attribute)) {
        throw new BuildException("$attribute attribute is required.", $this->location);
      }
    }
  }

  /**
   * @param $path
   * @return HTTP_Request2
   */
  protected function createRequest($path) {
    $uri = $this->endpoint . '/' . ltrim($path, '/');

    $request = new HTTP_Request2($uri);
    $request->setConfig('follow_redirects', TRUE);
    $request->setAuth($this->email, $this->key);

    return $request;
  }

  protected function get($path) {
    $request = $this->createRequest($path);

    $this->log('GET ' . $request->getUrl());
    $response = $request->send();
    return $response->getBody();
  }
}
