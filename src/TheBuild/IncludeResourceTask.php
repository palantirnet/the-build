<?php

namespace TheBuild;

/**
 *
 */
class IncludeResourceTask extends \Task {

  /**
   * @var string
   * Either 'symlink' or 'copy'.
   */
  protected $mode = 'symlink';

  /**
   * @var \PhingFile
   * The source file or directory to include.
   */
  protected $source;

  /**
   * @var \PhingFile
   * The location to link the file to.
   */
  protected $dest = NULL;

  /**
   * Whether to create relative symlinks.
   *
   * @var bool
   */
  protected $relative = TRUE;

  /**
   * Init tasks.
   *
   * Inherits the mode from the project's includeresource.mode property. This
   * can be overridden by setting the "mode" attribute.
   */
  public function init() {
    $mode = $this->getProject()->getProperty('includeresource.mode');
    if (!is_null($mode)) {
      $this->setMode($mode);
    }
    $relative = $this->getProject()->getProperty('includeresource.relative');
    if (!is_null($relative)) {
      $this->setRelative($relative);
    }
  }

  /**
   * Copy or link the resource.
   */
  public function main() {
    $this->validate();

    // Remove existing destination first.
    if ($this->dest->exists()) {
      $this->log("Replacing existing resource '" . $this->dest->getPath() . "'");

      if ($this->dest->delete(TRUE) === FALSE) {
        throw new \BuildException("Failed to delete existing destination '$this->dest'");
      }
    }

    // Link or copy the source artifact.
    $this->dest->getParentFile()->mkdirs();
    if ($this->mode == 'copy') {
      $this->log(sprintf("Copying '%s' to '%s'", $this->source->getPath(), $this->dest->getPath()));
      $this->source->copyTo($this->dest);
    }
    else {
      $this->log(sprintf("Linking '%s' to '%s'", $this->source->getPath(), $this->dest->getPath()));
      $symlink_task = $this->project->createTask("symlink");
      $symlink_task->setTarget($this->source->getPath());
      $symlink_task->setLink($this->dest->getPath());
      $symlink_task->setRelative($this->relative);
      $symlink_task->main();
    }
  }

  /**
   * Verify that the required attributes are set.
   */
  public function validate() {
    if (!in_array($this->mode, ['symlink', 'copy'])) {
      throw new \BuildException("mode attribute must be either 'symlink' or 'copy'", $this->location);
    }

    if (empty($this->source) || empty($this->dest)) {
      throw new \BuildException("Both the 'source' and 'dest' attributes are required.");
    }
  }

  /**
   * Set the artifact mode.
   *
   * @param $mode
   *   Use 'symlink' to link resources, and 'copy' to copy them.
   */
  public function setMode($mode) {
    $this->mode = $mode;
  }

  /**
   * Set the source of the resource to include.
   *
   * @param \PhingFile $source
   */
  public function setSource(\PhingFile $source) {
    if (!$source->exists()) {
      throw new \BuildException("resource '$source' is not available'");
    }

    $this->source = $source;
  }

  /**
   * Set the destination for the resource.
   *
   * @param \PhingFile $dest
   */
  public function setDest(\PhingFile $dest) {
    $this->dest = $dest;
  }

  /**
   * @param bool $relative
   */
  public function setRelative($relative) {
    $this->relative = $relative;
  }

}
