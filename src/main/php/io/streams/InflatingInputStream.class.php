<?php namespace io\streams;



/**
 * InputStream that inflates 
 *
 * @ext      zlib
 * @test     xp://net.xp_framework.unittest.io.streams.InflatingInputStreamTest
 * @purpose  InputStream implementation
 */
class InflatingInputStream extends \lang\Object implements InputStream {
  protected $in = null;
  
  /**
   * Constructor
   *
   * @param   io.streams.InputStream in
   */
  public function __construct(InputStream $in) {
    $this->in= Streams::readableFd($in);
    if (!stream_filter_append($this->in, 'zlib.inflate', STREAM_FILTER_READ)) {
      throw new \io\IOException('Could not append stream filter');
    }
  }

  /**
   * Read a string
   *
   * @param   int limit default 8192
   * @return  string
   */
  public function read($limit= 8192) {
    return fread($this->in, $limit);
  }

  /**
   * Returns the number of bytes that can be read from this stream 
   * without blocking.
   *
   */
  public function available() {
    return feof($this->in) ? 0 : 1;
  }

  /**
   * Close this buffer.
   *
   */
  public function close() {
    if (!$this->in) return;
    fclose($this->in);
    $this->in= null;
  }
  
  /**
   * Destructor. Ensures output stream is closed.
   *
   */
  public function __destruct() {
    $this->close();
  }
}
