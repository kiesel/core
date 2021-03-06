<?php namespace text\format;



/**
 * Money formatter
 *
 * @purpose  Provide a Format wrapper for money_format
 * @see      php://money_format
 * @see      xp://text.format.IFormat
 */
class MoneyFormat extends IFormat {

  /**
   * Get an instance
   *
   * @return  text.format.MoneyFormat
   */
  public function getInstance() {
    return parent::getInstance('MoneyFormat');
  }  

  /**
   * Apply format to argument
   *
   * @param   var fmt
   * @param   var argument
   * @return  string
   */
  public function apply($fmt, $argument) {
    if (!function_exists('money_format')) {
      throw new \lang\FormatException('money_format requires PHP >= 4.3.0');
    }
    return money_format($fmt, $argument);
  }
}
