<?php namespace security\password;

use text\StringUtil;


/**
 * Generates random codes that can be used for coupons etc.
 * The codes are not guaranteed to be unique although they usually
 * will:)
 *
 * @see      php://uniqid
 * @see      php://microtime
 * @purpose  Generator
 */
class RandomCodeGenerator extends \lang\Object {
  public
    $length   = 0;
    
  /**
   * Constructor
   *
   * @param   int length default 16
   * @throws  lang.IllegalArgumentException if length is not greater than zero
   */
  public function __construct($length= 16) {
    if ($length <= 0) {
      throw new \lang\IllegalArgumentException('Length must be greater than zero');
    }
    $this->length= $length;
  }
  
  /**
   * Generate
   *
   * @return  string
   */
  public function generate() {

    // Result from generation will always be 44 characters in length, 
    // 21 from microtime() used as prefix, plus 23 from uniqid() with 
    // more_entropy set to TRUE.
    $uniq= '';
    for ($l= 0; $l < $this->length; $l+= 44) {
      $uniq.= str_shuffle(strtr(uniqid(microtime(), true), ' .', 'gh'));
    }
    while (strlen($uniq) > $this->length) {
      $uniq= StringUtil::delete($uniq, rand(0, strlen($uniq)));
    }
    
    return $uniq;
  }
}
