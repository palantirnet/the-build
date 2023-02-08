<?php

namespace TheBuild\Acquia;

use Phing\Task;
use Phing\Exception\BuildException;
use Phing\Io\File;

/**
 * Phing task for making queries against the Acquia Cloud v1 API.
 */
abstract class AcquiaTask extends Task {

  /**
   * Required. The Acquia Cloud credentials file.
   *
   * This file can be downloaded from your Acquia user account area and contains
   * a json array with 'mail' and 'key' values.
   *
   * @var Phing\Io\File
   */
  protected $credentialsFile;

  /**
   * Email address associated with the Acquia Cloud access.
   *
   * This value is set from the credentials file.
   *
   * @var string
   */
  protected $mail;

  /**
   * Secure key associated with the Acquia Cloud access.
   *
   * This value is set from the credentials file.
   *
   * @var string
   */
  protected $key;

  /**
   * The Acquia Cloud API v1 endpoint.
   *
   * @var string
   */
  protected $endpoint = 'https://cloudapi.acquia.com/v1';

  /**
   * Load the Acquia Cloud credentials from the cloudapi.conf JSON file.
   *
   * @throws \Phing\Io\IOException;
   *
   * @SuppressWarnings(PHPMD.Superglobals)
   */
  protected function loadCredentials() {
    if (empty($this->mail) || empty($this->key)) {
      if (empty($this->credentialsFile)) {
        $this->credentialsFile = new File($_SERVER['HOME'] . '/.acquia/cloudapi.conf');
      }

      if (!file_exists($this->credentialsFile) || !is_readable($this->credentialsFile)) {
        throw new BuildException("Acquia Cloud credentials file '{$this->credentialsFile}' is not available.");
      }

      $contents = file_get_contents($this->credentialsFile);
      $creds = json_decode($contents, TRUE);

      $this->mail = $creds['mail'];
      $this->key = $creds['key'];
    }

    if (empty($this->mail) || empty($this->key)) {
      throw new BuildException('Missing Acquia Cloud API credentials.');
    }
  }

  /**
   * Build an HTTP request object against the Acquia Cloud API.
   *
   * @param string $path
   *   Acquia Cloud API path.
   *
   * @return \HTTP_Request2
   *   Request object.
   *
   * @throws \Phing\Io\IOException
   * @throws \HTTP_Request2_LogicException
   */
  protected function createRequest(string $path) : \HTTP_Request2 {
    $this->loadCredentials();

    $uri = $this->endpoint . '/' . ltrim($path, '/');

    $request = new \HTTP_Request2($uri);
    $request->setConfig('follow_redirects', TRUE);
    $request->setAuth($this->mail, $this->key);

    return $request;
  }

  /**
   * Example of how to query the Acquia Cloud API.
   *
   * @param string $path
   *   Acquia Cloud API path.
   *
   * @return string
   *   API response.
   *
   * @throws \HTTP_Request2_Exception
   * @throws \Phing\Io\IOException
   */
  protected function getApiResponseBody(string $path) : string {
    $request = $this->createRequest($path);

    $this->log('GET ' . $request->getUrl());
    $response = $request->send();
    return $response->getBody();
  }

  /**
   * Set the Acquia credentials file.
   *
   * @param Phing\Io\File $file
   *   Acquia credentials file.
   *
   * @throws \Phing\Io\IOException
   */
  public function setCredentialsFile(Phing\Io\File $file) {
    $this->credentialsFile = new File($file);
  }

}
