<?php
/**
 * @file ExpandPrefixedProperties.php
 *
 * A Phing filter based on the default ExpandProperties filter, except that this
 * expands only properties with a specific prefix.
 *
 * This is useful for dereferencing nested properties, and potentially running
 * the same task multiple times with different properties.
 *
 * For example, you might have the following properties:
 *
 * drupal:
 *   sites:
 *     first_multisite:
 *       uri: http://first_multisite.local
 *     second_multisite:
 *        uri: http://second_multisite.local
 *
 * Then, you might want to generate the settings.php file for each site:
 *
 *    <target name="build-default-settings">
 *       <copy file="${build.thebuild.dir}/defaults/templates/drupal.settings.build.php" tofile="artifacts/test.php" overwrite="true">
 *           <filterchain>
 *               <filterreader classname="TheBuild\ExpandPrefixedProperties">
 *                   <param name="prefix" value="drupal.sites.default." />
 *               </filterreader>
 *           </filterchain>
 *       </copy>
 *
 *       <loadfile property="out" file="artifacts/test.php" />
 *       <echo>${out}</echo>
 *   </target>
 *
 * @copyright 2018 Palantir.net, Inc.
 */

namespace TheBuild;

require_once 'phing/parser/ProjectConfigurator.php';

use ExpandProperties;
use ProjectConfigurator;
use Parameterizable;
use ConfigurationException;

class ExpandPrefixedProperties extends ExpandProperties implements Parameterizable {

  /**
   * @var string
   * The prefix to look for.
   */
  protected $prefix = "";

  /**
   * @param $prefix
   */
  public function setPrefix($prefix) {
    $this->prefix = $prefix;
  }

  /**
   * @param null $len
   * @return int|mixed|string
   */
  public function read($len = null) {
    $buffer = $this->in->read($len);

    if ($buffer === -1) {
      return -1;
    }

    $project = $this->getProject();

    $properties = [];
    foreach ($project->getProperties() as $name => $value) {
      if (strpos($name, $this->prefix) === 0) {
        $newprop = substr($name, strlen($this->prefix));
        $properties[$newprop] = $value;
      }
    }

    $buffer = ProjectConfigurator::replaceProperties($project, $buffer, $properties, $this->logLevel);

    return $buffer;
  }

  /**
   * @param $parameters
   * @return mixed|void
   * @throws ConfigurationException
   */
  public function setParameters($parameters) {
    /** @var \Parameter $param */
    foreach ($parameters as $param) {
      $name = $param->getName();
      $method = 'set' . ucfirst($name);
      if (method_exists($this, $method) && $method != 'setParameters') {
        $this->$method($param->getValue());
      }
    }

    $this->validate();
  }

  /**
   * @throws ConfigurationException
   */
  protected function validate() {
    if (empty($this->prefix)) {
      throw new ConfigurationException('TheBuild\ExpandPrefixedProperties filter is missing a value for the required "prefix" parameter.');
    }
  }

}
