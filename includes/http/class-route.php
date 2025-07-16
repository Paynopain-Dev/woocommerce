<?php

/**
 * A Route describes a route and its parameters.
 *
 * @author Carl Alexander <contact@carlalexander.ca>
 */
class Route {
	/**
	 * The hook called when this route is matched.
	 *
	 * @var string
	 */
	private $hook;

	/**
	 * The URL path that the route needs to match.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * The template that the route wants to load.
	 *
	 * @var string
	 */
	private $template;

	/**
	 * The HTTP Verb request (GET/POST)
	 *
	 * @var string
	 */
	private $verb;

	/**
	 * The HTTP Request is ascycron?
	 *
	 * @var boolean
	 */
	private $is_ajax;

	/**
	 * Constructor.
	 *
	 * @param string $path
	 * @param string $hook
	 * @param string $template
	 */
	public function __construct( $path, $template = '', $verb = 'GET', $hook = '', $is_ajax = false ) {
		$this->hook         = $hook;
		$this->path         = $path;
		$this->template     = $template;
		$this->verb 		= $verb;
		$this->is_ajax		= $is_ajax;
	}

	/**
	 * Get the hook called when this route is matched.
	 *
	 * @return string
	 */
	public function get_hook() {
		return $this->hook;
	}

	/**
	 * Get the URL path that the route needs to match.
	 *
	 * @return string
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * Get the HTTP verb registered.
	 *
	 * @return string GET|POST
	 */
	public function get_verb() {
		return $this->verb;
	}

	/**
	 * Get the template that the route wants to load.
	 *
	 * @return string
	 */
	public function get_template() {
		return $this->template;
	}

	/**
	 * Get the ajax action validation.
	 *
	 * @return boolean
	 */
	public function is_ajax_request() {
		return $this->is_ajax;
	}

	/**
	 * Checks if this route want to call a hook when matched.
	 *
	 * @return bool
	 */
	public function has_hook() {
		return ! empty( $this->hook );
	}

	/**
	 * Checks if this route want to load a template when matched.
	 *
	 * @return bool
	 */
	public function has_template() {
		return ! empty( $this->template );
	}
}
