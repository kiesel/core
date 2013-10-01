<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'scriptlet.HttpScriptlet',
    'scriptlet.Preference',
    'webservices.rest.RestFormat',
    'webservices.rest.srv.RestContext',
    'webservices.rest.srv.Response',
    'webservices.rest.srv.RestDefaultRouter',
    'util.log.Traceable'
  );
  
  /**
   * REST scriptlet
   *
   * @test  xp://net.xp_framework.unittest.webservices.rest.srv.RestScriptletTest
   */
  class RestScriptlet extends HttpScriptlet implements Traceable {
    protected 
      $cat     = NULL,
      $router  = NULL,
      $context = NULL,
      $base    = '';

    /**
     * Constructor
     * 
     * @param  string package The package containing handler classes
     * @param  string base The base URL (will be stripped off from request url)
     * @param  string context The context class to use
     * @param  string router The router class to use
     */
    public function __construct($package, $base= '', $context= '', $router= '') {
      $this->base= rtrim($base, '/');

      // Context class
      $class= XPClass::forName('' === (string)$context ? 'webservices.rest.srv.RestContext' : $context); 
      $this->context= $class->newInstance();

      // Create router
      if ('' === (string)$router) {
        $this->router= new RestDefaultRouter();
      } else {
        $this->router= XPClass::forName($router)->newInstance();
      }
      $this->router->configure($package, $this->base);
      $this->router->setInputFormats(array('*json', '*xml', 'application/x-www-form-urlencoded'));
      $this->router->setOutputFormats(array('application/json', 'text/json', 'text/xml', 'application/xml'));
    }

    /**
     * Set a log category for tracing
     *
     * @param  util.log.LogCategory cat
     */
    public function setTrace($cat) {
      $this->cat= $cat;
      $this->context->setTrace($this->cat);
    }

    /**
     * Sets a router
     *
     * @param  webservices.rest.srv.AbstractRestRouter router
     */
    public function setRouter(AbstractRestRouter $router) {
      $this->router= $router;
    }

    /**
     * Gets the router
     *
     * @return webservices.rest.srv.AbstractRestRouter
     */
    public function getRouter() {
      return $this->router;
    }

    /**
     * Sets a context
     *
     * @param  webservices.rest.srv.RestContext context
     */
    public function setContext(RestContext $context) {
      $this->context= $context;
    }

    /**
     * Gets the context
     *
     * @return webservices.rest.srv.RestContext
     */
    public function getContext() {
      return $this->context;
    }

    /**
     * Calculate method to invoke
     *
     * @param   scriptlet.HttpScriptletRequest request 
     * @return  string
     */
    public function handleMethod($request) {
      parent::handleMethod($request);
      return 'doProcess';
    }

    /**
     * Returns content-type of string if a payload is available, NULL otherwise.
     *
     * @see    http://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.3
     * @see    http://www.w3.org/Protocols/rfc2616/rfc2616-sec7.html#sec7.2.1
     * @param  scriptlet.HttpScriptletRequest request The request
     * @return string
     */
    public function contentTypeOf($request) {
      if ($request->getHeader('Content-Length') || $request->getHeader('Transfer-Encoding')) {
        return $request->getHeader('Content-Type', 'application/octet-stream');
      }
      return NULL;
    }

    /**
     * Process request and handle errors
     * 
     * @param  scriptlet.HttpScriptletRequest request The request
     * @param  scriptlet.HttpScriptletResponse response The response
     */
    public function doProcess($request, $response) {
      $url= $request->getURL();
      $type= $this->contentTypeOf($request);
      $accept= new Preference($request->getHeader('Accept', '*/*'));
      $this->cat && $this->cat->info(
        $request->getMethod(),
        $type ?: '(null)',
        $url->getURL(),
        $accept
      );

      // Iterate over all applicable routes
      $ctx= clone $this->context;
      foreach ($this->router->targetsFor(
        $request->getMethod(), 
        $url->getPath(), 
        $type,
        $accept
      ) as $target) {
        if ($ctx->process($target, $request, $response)) return;
      }

      // No route
      $response->setStatus(HttpConstants::STATUS_NOT_FOUND);
      $format= $accept->match($this->router->getOutputFormats());
      $response->setContentType($format);
      RestFormat::forMediaType($format)->write($response->getOutputStream(), new Payload(
        array('message' => 'Could not route request to '.$url->getURL()), array('name' => 'error')
      ));
    }
  }
?>
