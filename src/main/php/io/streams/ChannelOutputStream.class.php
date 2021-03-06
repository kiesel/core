<?php namespace io\streams;

use io\IOException;


/**
 * Output stream that writes to one of the "stdout", "stderr", "output"
 * channels provided as PHP input/output streams.
 *
 * @test     xp://net.xp_framework.unittest.io.streams.ChannelStreamTest
 * @see      php://wrappers
 * @see      xp://io.streams.ChannelInputStream
 * @purpose  Outputstream implementation
 */
class ChannelOutputStream extends \lang\Object implements OutputStream {
  protected
    $name = null,
    $fd   = null;
  
  /**
   * Constructor
   *
   * @param   string name
   */
  public function __construct($name) {
    static $allowed= array('stdout', 'stderr', 'output');

    if (!in_array($name, $allowed) || !($this->fd= fopen('php://'.$name, 'wb'))) {
      throw new IOException('Could not open '.$name.' channel for writing');
    }
    $this->name= $name;
  }

  /**
   * Write a string
   *
   * @param   var arg
   */
  public function write($arg) { 
    if (false === fwrite($this->fd, $arg)) {
      $e= new IOException('Could not write '.strlen($arg).' bytes to '.$this->name.' channel');
      \xp::gc(__FILE__);
      throw $e;
    }
  }

  /**
   * Flush this stream.
   *
   */
  public function flush() {
    fflush($this->fd);
  }

  /**
   * Close this stream
   *
   */
  public function close() {
    fclose($this->fd);
  }

  /**
   * Creates a string representation of this input stream
   *
   * @return  string
   */
  public function toString() {
    return $this->getClassName().'(channel='.$this->name.')';
  }
}
