<?php namespace net\xp_framework\unittest\util;

use unittest\TestCase;
use util\Observable;


/**
 * Test Observable class
 *
 * @see  xp://util.Observable
 */
class ObservableTest extends TestCase {
  protected static $observable;

  /**
   * Creates observable
   */
  #[@beforeClass]
  public static function defineObservable() {
    self::$observable= \lang\ClassLoader::defineClass('net.xp_framework.unittest.util.ObservableFixture', 'util.Observable', array(), '{
      private $value= 0;

      public function setValue($value) {
        $this->value= $value;
        $this->setChanged();
        $this->notifyObservers();
      }

      public function getValue() {
        return $this->value;
      }
    }');
  }

  /**
   * Tests hasChanged() method
   */
  #[@test]
  public function originally_unchanged() {
    $o= self::$observable->newInstance();
    $this->assertFalse($o->hasChanged());
  }

  /**
   * Tests setChanged() method
   */
  #[@test]
  public function changed() {
    $o= self::$observable->newInstance();
    $o->setChanged();
    $this->assertTrue($o->hasChanged());
  }

  /**
   * Tests clearChanged() method
   */
  #[@test]
  public function change_cleared() {
    $o= self::$observable->newInstance();
    $o->setChanged();
    $o->clearChanged();
    $this->assertFalse($o->hasChanged());
  }

  /**
   * Tests addObserver() method
   */
  #[@test]
  public function add_observer_returns_added_observer() {
    $observer= newinstance('util.Observer', array(), array(
      'update' => function($self, $obs, $arg= null) {
        /* Intentionally empty */
      }
    ));
    $o= self::$observable->newInstance();
    $this->assertEquals($observer, $o->addObserver($observer));
  }

  /**
   * Tests notifyObservers() method
   */
  #[@test]
  public function observer_gets_called_with_observable() {
    $observer= newinstance('util.Observer', array(), array(
      'calls' => array(),
      'update' => function($self, $obs, $arg= null) {
        $self->calls[]= array($obs, $arg);
      }
    ));
    $o= self::$observable->newInstance();
    $o->addObserver($observer);
    $o->setValue(5);
    $this->assertEquals(array(array($o, null)), $observer->calls);
  }
}
