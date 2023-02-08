<?php

namespace TheBuild;

use Phing\Task;
use Phing\Exception\BuildException;
use Phing\Io\File;

/**
 * Copy or symlink a file or directory, depending on a flag.
 */
class IncludeResourceTask extends Task {

  /**
   * Either 'symlink' or 'copy'.
   *
   * @var string
   */
  protected $mode = 'symlink';

  /**
   * The source file or directory to include.
   *
   * @var \Phing\Io\File
   */
  protected $source;

  /**
   * The location to link the file to.
   *
   * @var \Phing\Io\File
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
        throw new BuildException("Failed to delete existing destination '$this->dest'");
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
      /** @var \Phing\Task\System\SymlinkTask $symlink_task */
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
      throw new BuildException("mode attribute must be either 'symlink' or 'copy'", $this->getLocation());
    }

    if (empty($this->source) || empty($this->dest)) {
      throw new BuildException("Both the 'source' and 'dest' attributes are required.");
    }
  }

  /**
   * Set the artifact mode.
   *
   * @param string $mode
   *   Use 'symlink' to link resources, and 'copy' to copy them.
   */
  public function setMode(string $mode) {
    $this->mode = $mode;
  }

  /**
   * Set the source of the resource to include.
   *
   * @param \Phing\Io\File $source
   *   Source file.
   */
  public function setSource(File $source) {
    if (!$source->exists()) {
      throw new BuildException("resource '$source' is not available'");
    }

    $this->source = $source;
  }

  /**
   * Set the destination for the resource.
   *
   * @param \Phing\Io\File $dest
   *   File destination.
   */
  public function setDest(File $dest) {
    $this->dest = $dest;
  }

  /**
   * See SymlinkTask.
   *
   * @param bool $relative
   *   Whether to make relative symlinks.
   */
  public function setRelative($relative) {
    $this->relative = $relative;
  }

}
