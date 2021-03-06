<?php namespace io\collections\iterate;



/**
 * Negation filter
 *
 * @purpose  Iteration Filter
 */
class NegationOfFilter extends \lang\Object implements IterationFilter {
  public
    $filter= null;
    
  /**
   * Constructor
   *
   * @param   io.collections.iterate.IterationFilter filter
   */
  public function __construct($filter) {
    $this->filter= $filter;
  }

  /**
   * Accepts an element
   *
   * @param   io.collections.IOElement element
   * @return  bool
   */
  public function accept($element) {
    return !$this->filter->accept($element);
  }

  /**
   * Creates a string representation of this iterator
   *
   * @return  string
   */
  public function toString() {
    return $this->getClassName().'<'.$this->filter->toString().'>';
  }

} 
