<?php namespace io;



/**
 * This class provides functions for searching, seeking and replacing
 * strings and tokens in a stream.
 * A searchable stream is a stream that supports seeking to offsets
 * that aren't numerically known but must be searched. Mostly those
 * are streams that completely reside in memory.
 *
 * @purpose Search operations on stream
 */
class SearchableStream extends Stream {

  /**
   * Tokenizes the stream and moves the offset behind
   * the given token. The delimiter itself is returned
   * as part of the token
   *
   * @param   string lim delimiters to use
   * @return  string tokenizes token
   */
  public function getNextToken($lim) {
    $pos= false;  
    for ($i= 0; $i < strlen ($lim); $i++) {
      if ($npos= strpos ($this->buffer, $lim{$i}, $this->offset))
        $pos= (false !== $pos ? min ($pos, $npos) : $npos);
    }
    
    if (false === $pos)
      return false;
    
    $token= substr ($this->buffer, $this->offset, $pos-$this->offset+1);
    $this->offset+= strlen ($token);
    return $token;
  }

  /**
   * Tokenizes a string by a complex delimiter (multiple characters).
   *
   * @param   string* delimiters
   * @return  string token
   */
  public function getNextComplexToken() {
    $delims= func_get_args();
    $pos= false;
    
    foreach ($delims as $d) {
      if ($npos= strpos ($this->buffer, $d, $this->offset)) {
        if (false === $pos || $pos > $npos) {
          $pos= $npos;
          $match= $d;
        }
      }
    }
    
    if (false === $pos)
      return false;
  
    $token= substr ($this->buffer, $this->offset, $pos+strlen ($match)-$this->offset);
    $this->offset+= strlen ($token);
    return $token;
  }
  
  /**
   * Returns the offset of the searched string within the stream 
   * or FALSE if the string was not found.
   *
   * @param   string string to search
   * @return  int offset
   */    
  public function findNext($substring) {
    return strpos ($this->buffer, $substring, $this->offset);
  }
}

