<?php namespace text\format;



/**
 * Choice formatter
 *
 * @purpose  Provide a Format wrapper for values depending on choices
 * @see      xp://text.format.IFormat
 */
class ChoiceFormat extends IFormat {

  /**
   * Get an instance
   *
   * @return  text.format.ChoiceFormat
   */
  public function getInstance() {
    return parent::getInstance('ChoiceFormat');
  }  

  /**
   * Apply format to argument
   *
   * @param   var fmt
   * @param   var argument
   * @return  string
   * @throws  lang.FormatException
   */
  public function apply($fmt, $argument) {
    foreach (explode('|', $fmt) as $choice) {
      list($cmp, $val)= explode(':', $choice);
      if ($argument == $cmp) {
        return $val;
      }
      if ('*' == $cmp) {
        return $val;
      }
    }
    throw new \lang\FormatException('Value is out of bounds');
  }
}
