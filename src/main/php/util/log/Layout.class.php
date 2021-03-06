<?php namespace util\log;





/**
 * Takes care of formatting log entries
 *
 */
abstract class Layout extends \lang\Object {
  
  /**
   * Formats a logging event according to this layout
   *
   * @param   util.log.LoggingEvent event
   * @return  string
   */
  public abstract function format(LoggingEvent $event);
}
