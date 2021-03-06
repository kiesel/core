<?php namespace io\streams;



/**
 * OuputStream that writes to the console
 *
 * Usage:
 * <code>
 *   $out= new ConsoleOutputStream(STDOUT);
 *   $err= new ConsoleOutputStream(STDERR);
 * </code>
 *
 * @purpose  OuputStream implementation
 */
class ConsoleOutputStream extends \lang\Object implements OutputStream {
  protected
    $descriptor= null;
  
  /**
   * Constructor
   *
   * @param   resource descriptor one of STDOUT, STDERR
   */
  public function __construct($descriptor) {
    $this->descriptor= $descriptor;
  }

  /**
   * Creates a string representation of this output stream
   *
   * @return  string
   */
  public function toString() {
    return $this->getClassName().'<'.$this->descriptor.'>';
  }

  /**
   * Write a string
   *
   * @param   var arg
   */
  public function write($arg) { 
    fwrite($this->descriptor, $arg);
  }

  /**
   * Flush this buffer.
   *
   */
  public function flush() { 
    fflush($this->descriptor);
  }

  /**
   * Close this buffer.
   *
   */
  public function close() {
    fclose($this->descriptor);
  }
}
