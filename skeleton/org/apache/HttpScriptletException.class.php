<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('org.apache.HttpScriptletResponse');

  /**
   * Defines an exception which is thrown inside an HttpScriptlet
   * when it encounters a fatal error situation.
   *
   * These might be
   * <ul>
   *   <li>Session initialization fails</li>
   *   <li>HTTP method is not supported (e.g., DELETE)</li>
   *   <li>Request processing fails with an Exception</li>
   * </ul>
   *
   * @see      xp://org.apache.HttpScriptlet#process
   * @purpose  Exception
   * @deprecated
   */  
  class HttpScriptletException extends Exception {
    var
      $response;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string message
     * @param   int statusCode default HTTP_INTERNAL_SERVER_ERROR
     */
    function __construct($message, $statusCode= HTTP_INTERNAL_SERVER_ERROR) {
      parent::__construct($message);
      $this->_response($statusCode);
    }
    
    /**
     * Retrieve response
     *
     * @access  public
     * @return  org.apache.HttpScriptletResponse response object
     */
    function &getResponse() {
      return $this->response;
    }
    
    /**
     * Create the response object
     *
     * @access  private
     * @param   int statusCode
     */
    function _response($statusCode) {
      $this->response= &new HttpScriptletResponse();
      $this->response->setStatus($statusCode);
      $this->response->setContent(sprintf(
        "<h1>Internal Server Error</h1>\n<xmp>\n%s</xmp>\n",
        $this->toString()
      ));
    }
  }
?>
