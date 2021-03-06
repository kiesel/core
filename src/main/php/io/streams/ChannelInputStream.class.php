<?php namespace io\streams;

use io\IOException;


/**
 * Input stream that reads from one of the "stdin", "input" channels
 * provided as PHP input/output streams.
 *
 * @test     xp://net.xp_framework.unittest.io.streams.ChannelStreamTest
 * @see      php://wrappers
 * @see      xp://io.streams.ChannelOutputStream
 * @purpose  Inputstream implementation
 */
class ChannelInputStream extends \lang\Object implements InputStream {
  protected
    $name = null,
    $fd   = null;
  
  /**
   * Constructor
   *
   * @param   string name
   */
  public function __construct($name) {
    static $allowed= array('stdin', 'input');

    if (!in_array($name, $allowed) || !($this->fd= fopen('php://'.$name, 'rb'))) {
      throw new IOException('Could not open '.$name.' channel for reading');
    }
    $this->name= $name;
  }

  /**
   * Read a string
   *
   * @param   int limit default 8192
   * @return  string
   */
  public function read($limit= 8192) {
    if (false === ($bytes= fread($this->fd, $limit))) {
      $e= new IOException('Could not read '.$limit.' bytes from '.$this->name.' channel');
      \xp::gc(__FILE__);
      throw $e;
    }
    return $bytes;
  }

  /**
   * Returns the number of bytes that can be read from this stream 
   * without blocking.
   *
   */
  public function available() {
    return feof($this->fd) ? 0 : 1;
  }

  /**
   * Close this input stream
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
