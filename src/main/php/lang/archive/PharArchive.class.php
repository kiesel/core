<?php namespace lang\archive;

use lang\ElementNotFoundException;
use io\EncapsedStream;
use io\FileUtil;

// Only aliases for those in lang.archive.Archive - be sure to keep them at the same values
define('PHARCHIVE_READ',             0x0000);
define('PHARCHIVE_CREATE',           0x0001);


/**
 * Archives contain a collection of classes.
 *
 * Usage example (Creating):
 * <code>
 *   $a= new Archive(new File('soap.xar'));
 *   $a->open(ARCHIVE_CREATE);
 *   $a->addFile(
 *     'webservices/soap/SOAPMessage.class.php'
 *     new File($path, 'xml/soap/SOAPMessage.class.php')
 *   );
 *   $a->create();
 * </code>
 *
 * Usage example (Extracting):
 * <code>
 *   $a= new Archive(new File('soap.xar'));
 *   $bytes= $a->extract('webservices/soap/SOAPMessage.class.php');
 * </code>
 *
 * @test  xp://net.xp_framework.unittest.archive.ArchiveV1Test
 * @test  xp://net.xp_framework.unittest.archive.ArchiveV2Test
 * @test  xp://net.xp_framework.unittest.core.ArchiveClassLoaderTest
 * @see   http://java.sun.com/javase/6/docs/api/java/util/jar/package-summary.html
 */
class PharArchive extends \lang\Object {
  const
    ARCHIVE_READ   = PHARCHIVE_READ,
    ARCHIVE_CREATE = PHARCHIVE_CREATE;

  private
    $archive = null,
    $file     = null;

  /**
   * Constructor
   *
   * @param   io.File file
   */
  public function __construct(\io\File $file) {
    $this->file= $file;
  }
  
  /**
   * Get URI
   *
   * @return  string uri
   */
  public function getURI() {
    return $this->file->getURI();
  }

  /**
   * Add a file
   *
   * @param   io.File file
   * @param   string id the id under which this entry will be located
   * @return  bool success
   * @deprecated Use addFile() instead
   */
  public function add($file, $id) {
    try {
      $this->archive->addFile($file->getURI(), $id);
      return true;
    } catch (\PharException $e) {
      // TODO
      throw $e;
    }
  }

  /**
   * Add a file by its bytes
   *
   * @param   string id the id under which this entry will be located
   * @param   string path
   * @param   string filename
   * @param   string bytes
   * @deprecated Use addBytes() instead
   */
  public function addFileBytes($id, $path, $filename, $bytes) {
    $this->addBytes($id, $bytes);
  }


  /**
   * Add a file by its bytes
   *
   * @param   string id the id under which this entry will be located
   * @param   string bytes
   */
  public function addBytes($id, $bytes) {
    try {
      $this->archive->addFromString($id, $bytes);
    } catch (\PharException $e) {
      // TODO
      throw $e;
    }
  }

  /**
   * Add a file
   *
   * @param   string id the id under which this entry will be located
   * @param   io.File file
   */
  public function addFile($id, $file) {
    $this->add($id, $file);
  }

  /**
   * Create CCA archive
   *
   * @return  bool success
   */
  public function create() {
    $this->archive->stopBuffering();
  }

  /**
   * Check whether a given element exists
   *
   * @param   string id the element's id
   * @return  bool TRUE when the element exists
   */
  public function contains($id) {
    return isset($this->archive[$id]);
  }

  /**
   * Get entry (iterative use)
   * <code>
   *   $a= new Archive(new File('port.xar'));
   *   $a->open(ARCHIVE_READ);
   *   while ($id= $a->getEntry()) {
   *     var_dump($id);
   *   }
   *   $a->close();
   * </code>
   *
   * @return  string id or FALSE to indicate the pointer is at the end of the list
   */
  public function getEntry() {
    $key= key($this->archive);
    next($this->archive);
    return $key;
  }

  /**
   * Rewind archive
   *
   */
  public function rewind() {
    reset($this->archive);
  }

  /**
   * Extract a file's contents
   *
   * @param   string id
   * @return  string content
   * @throws  lang.ElementNotFoundException in case the specified id does not exist
   */
  public function extract($id) {
    if (!isset($this->archive[$id])) return false;

    return file_get_contents('phar://'.$this->file->getURI().DIRECTORY_SEPARATOR.$id);
  }

  /**
   * Fetches a stream to the file in the archive
   *
   * @param   string id
   * @return  io.Stream
   * @throws  lang.ElementNotFoundException in case the specified id does not exist
   */
  public function getStream($id) {
    return new File('phar://'.$this->file->getURI().DIRECTORY_SEPARATOR.$id);
  }

  /**
   * Open this archive
   *
   * @param   int mode default ARCHIVE_READ one of ARCHIVE_READ | ARCHIVE_CREATE
   * @return  bool success
   * @throws  lang.IllegalArgumentException in case an illegal mode was specified
   * @throws  lang.FormatException in case the header is malformed
   */
  public function open($mode) {
    switch ($mode) {
      case self::ARCHIVE_READ: {
        try {
          $this->archive= new \Phar($this->file->getURI());
          $this->archive->startBuffering();
          return true;
        } catch (\UnexpectedValueException $e) {
          // TODO
          throw $e;
        }
      }

      case self::ARCHIVE_CREATE: {
        try {
          $this->archive= new \Phar($this->file->getURI());
          $this->archive->setStub('<?php __HALT_COMPILER();');
          $this->archive->startBuffering();
          return true;
        } catch (\UnexpectedValueException $e) {
          // TODO
          throw $e;
        }
      }
    }
  }

  /**
   * Close this archive
   *
   * @return  bool success
   */
  public function close() {
    unset($this->archive);
    $this->archive= null;
  }

  /**
   * Checks whether this archive is open
   *
   * @return  bool TRUE when the archive file is open
   */
  public function isOpen() {
    return $this->archive instanceof \Phar;
  }

  /**
   * Returns a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    return sprintf(
      '%s(index size= %d) { %s }',
      $this->getClassName(),
      sizeof($this->archive),
      \xp::stringOf($this->file)
    );
  }

  /**
   * Destructor
   *
   */
  public function __destruct() {
    if ($this->isOpen()) $this->close();
  }
}
