<?php namespace unittest\actions;

use unittest\TestAction;
use lang\Runtime;


/**
 * Only runs this testcase if a given PHP extension is available
 *
 * @test  xp://net.xp_framework.unittest.tests.ExtensionAvailableTest
 * @see   xp://lang.Runtime#extensionAvailable
 */
class ExtensionAvailable extends \lang\Object implements TestAction {
  protected $extension= '';

  /**
   * Create a new ExtensionAvailable instance
   *
   * @param string extension The name of a PHP extension
   */
  public function __construct($extension) {
    $this->extension= $extension;
  }

  /**
   * Verify a the extension exists
   *
   * @return bool
   */
  public function verify() {
    return Runtime::getInstance()->extensionAvailable($this->extension);
  }

  /**
   * This method gets invoked before a test method is invoked, and before
   * the setUp() method is called.
   *
   * @param  unittest.TestCase $t
   * @throws unittest.PrerequisitesNotMetError
   */
  public function beforeTest(\unittest\TestCase $t) { 
    if (!$this->verify()) {
      throw new \unittest\PrerequisitesNotMetError('PHP Extension not available', null, array($this->extension));
    }
  }

  /**
   * This method gets invoked after the test method is invoked and regard-
   * less of its outcome, after the tearDown() call has run.
   *
   * @param  unittest.TestCase $t
   */
  public function afterTest(\unittest\TestCase $t) {
    // Empty
  }
}
