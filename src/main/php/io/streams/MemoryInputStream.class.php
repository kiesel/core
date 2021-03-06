<?php namespace io\streams;/* This file is part of the XP framework's experiments
 *
 * $Id$
 */



/**
 * InputStream that reads from a given string.
 *
 * @purpose  InputStream implementation
 */
class MemoryInputStream extends \lang\Object implements InputStream, Seekable {
  protected
    $pos   = 0,
    $bytes = '';

  /**
   * Constructor
   *
   * @param   string bytes
   */
  public function __construct($bytes) {
    $this->bytes= $bytes;
  }

  /**
   * Read a string
   *
   * @param   int limit default 8192
   * @return  string
   */
  public function read($limit= 8192) {
    $chunk= substr($this->bytes, $this->pos, $limit);
    $this->pos+= strlen($chunk);
    return $chunk;
  }

  /**
   * Returns the number of bytes that can be read from this stream 
   * without blocking.
   *
   */
  public function available() {
    return strlen($this->bytes) - $this->pos;
  }

  /**
   * Close this buffer
   *
   * Note: Closing a memory stream has no effect!
   *
   */
  public function close() {
  }
  
  /**
   * Seek to a given offset
   *
   * @param   int offset
   * @param   int whence default SEEK_SET (one of SEEK_[SET|CUR|END])
   * @throws  io.IOException in case of error
   */
  public function seek($offset, $whence= SEEK_SET) {
    switch ($whence) {
      case SEEK_SET: $this->pos= $offset; break;
      case SEEK_CUR: $this->pos+= $offset; break;
      case SEEK_END: $this->pos= strlen($this->bytes) + $offset; break;
    }
  }

  /**
   * Return current offset
   *
   * @return  int offset
   */
  public function tell() {
    return $this->pos;
  }
}
