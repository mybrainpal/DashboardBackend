<?php

/**
 * The main class for the application.
 * 
 * @author Nati
 */
class BrainPal {
	/**
	 * The requested class.
	 * 
	 * @var string
	 */
	private $class;
	
	/**
	 * The requested method.
	 * 
	 * @var string
	 */
	private $method;
	
	/**
	 * The instance of the requested action.
	 * 
	 * @var Actions
	 */
	private $action_instance;
	
	/**
	 * Did the request came from ajax?
	 * In order for this flag to be true, an `ajax` parameter must be sent in the GET or POST parameters.
	 * 
	 * @var boolean
	 */
	public $json;
	
	/**
	 * A simple object containing every input that got into the application
	 * 
	 * @var stdClass
	 */
	public $input;
	
	/**
	 * The Database instance.
	 * 
	 * @var Database
	 */
	public $db;
	
	/**
	 * The Logger instance.
	 * 
	 * @var Logger
	 */
	public $logger;
	
	/**
	 * The Output instance.
	 * 
	 * @var Output
	 */
	public $output;
	
	/**
	 * The User instance.
	 * 
	 * @var User
	 */
	public $user;
	
	/**
	 * Initializes the class properties to default values.
	 */
	public function __construct() {
		// Initialize the properties related to the request itself
		$this->class = '';
		$this->method = '';
		$this->action_instance = null;
		
		// Initialize the logger
		$this->logger = new Logger();
		
		// Initialize the database
		$this->db = new Database(DB_CONNECTION_NAME, DB_USER, DB_PASS, DB_NAME);
		
		// Initialize the output instance
		$this->output = new Output();
		
		/* Initialize the input variables */
		$this->input = new stdClass(); // Initialize the input object
		$this->input->get = &$_GET; // Store the GET parameters
		$this->input->post = &$_POST; // Store the POST parameters
		$this->input->request = &$_REQUEST; // Store the POST parameters
		$this->input->files = &$_FILES; // Store the FILES parameters
		$this->input->cookie = &$_COOKIE; // Store the cookies
		$this->input->session = &$_SESSION; // Store the session parameters
		$this->input->server = &$_SERVER; // Store the server parameters
	}
	
	/**
	 * Initialize the application and sets the class and method from the URL path.
	 * 
	 * The path is used as in the following example:
	 * /BrainPal/Category/SubCategory/Method
	 * 
	 * Where the category and sub category correspond to the class name (with Camel Case),
	 * and the method correspond to the function in the requested class.
	 * Every part of the path will be lower cased, with the exception of the first letter
	 * in the class name which will be upper cased.
	 * 
	 * The example above will load the class `ActionsCategorySubcategory`
	 * and execute the function `method`.
	 */
	public function initialize() {
		// Initialize the user instance
		$this->user = new User();
		
		// Add default template arguments
		$this->output->setArguments(array(
		    '{:PATH:}' => substr(WEB_ROOT, 0, -1), // absolute application web path without the ending slash
		));
		
		// Parse the path and set the class and method
		$this->parsePath();
		
		// Set the JSON flag (if needed)
		$this->setJSON();
		
		// Check the request token
		$this->checkCSRFToken();
		
		// Create a new token
		$this->createCSRFToken();
	}
	
	/**
	 * Parse the request path and set the properties `class` and `method` according to it.
	 * 
	 * @example /BrainPal/class_name/method_name
	 */
	private function parsePath() {
		// Get the path from PATH_INFO
		if( $this->input('server', 'PATH_INFO') !== null
		    && !empty( $this->input('server', 'PATH_INFO') )
		    && trim($this->input('server', 'PATH_INFO'), '/') !== '' ) {
			$path = trim($this->input('server', 'PATH_INFO'), '/');
		} else {
		    // PATH_INFO does not exist, use index
			$path = 'index';
		}

		// Replace any dangerous characters and make everything lower case
		$path = strtolower(preg_replace('/[^a-z\/]/i', '', $path));
		
		// Remove the "api" prefix if there is one
		if(strpos($path, '/api') === 0) {
		    $path = substr($path, 4);
		}
		
		// Parse the class
		if( count(explode('/', $path)) === 1 ) { // If there's only the class
			$class = $path;
				
			$this->method = 'index';
		}
		else { // Otherwise both the class and method exist
			$class = substr($path, 0, strlen( basename($path) ) * -1); // Get the path only (without the method)
				
			// Set the method
			$this->method = basename($path);
		}
		
		// Replace '/' with capitalization for every first letter in the path
		foreach( explode('/', trim($class, '/')) as $value ) {
			$this->class .= ucfirst($value);
		}
			
		
		// 'Add 'Actions' prefix so there will be no mistakes including the class
		$this->class = 'Actions' . $this->class;
	}
	
	/**
	 * Sets the JSON flag.
	 *
	 * @param boolean $json The JSON value (Default is true).
	 */
	public function setJSON($json = true) {
	    // Check if the response should be JSON (it should by default)
	    if( intval($this->input('request', 'json')) === 1 ) {
	        // Unless we specifically decide no to use JSON
	        if( $json !== false ) {
	            // Set the JSON flag to true
                $json = true;
	        }
	    }
	    
	    // Set the AJAX flag at the $app object
	    $this->json = $json;
	    
	    // Set the AJAX flag on the output
	    $this->output->setJSON($json);
	}
	
	/**
	 * Creates a new CSRF token for the current request and delete the old one.
	 * 
	 * @TODO Maybe move this to Security?
	 */
	private function createCSRFToken() {
	   
	    // Check that the token is still valid
	    if( empty($this->input('session', 'token_creation')) ||
	        (time() - $this->input('session', 'token_creation')) > TOKEN_KEEPALIVE ) {
    		// The token needs to be refreshed, delete every previous request token for the user
    		Security::deleteToken();
    		
    		// Create a new token
    		$token = Security::generateToken();
    		
    		// Store the token in the session
    		$this->input('session', 'token', $token);
    		
    		// Set the token creation time to the current time
    		$this->input('session', 'token_creation', (string)time());
	    }
		
		// Set the token in the template
		$this->output->setArguments(array(VAR_CSRF_TOKEN => $this->input('session', 'token')));
	}
	
	/**
	 * Check the token sent if a form has been submitted.
	 * This is used in order to protect against CSRF kind of attacks.
	 * 
	 * @TODO Maybe move this to Security?
	 */
	private function checkCSRFToken() {
		// Check if a form has been sent, and this is not a client init request 
		if( $this->input('request', 'submit') &&
		    !($this->getClass() === 'ActionsClient' && $this->getMethod() === 'init')) {
			// It did, check the token
			if( $this->input('session', 'token') !== $this->input('request', 'token') ) {
				// The token isn't valid! Raise an error but don't log it
				error('Invalid CSRF token!', false);
			}
		}
	}
	
	/**
	 * Returns the CSRF token stored in the user's session.
	 *
	 * @return string The CSRF token.
	 */
	public function getCSRFToken() {
	    // The CSRF token should always be stored in the user's session.
	    // If it doesn't, the user is not logged in.
	    return $this->input('session', 'token');
	}
	
	/**
	 * Launch the requested class and method.
	 */
	public function launch() {
		// Check if the requested class exists
		if(class_exists($this->class)) {
			// It is, create a new instance
			$this->action_instance = new $this->class($this);
			
			// Check for action authentication
			if( !$this->action_instance->checkAuth() ) {
			    // Authentication failed! Terminate the script
			    error('no_auth');
			}
			
			// Check if the requested method exists
			if( is_callable(array($this->action_instance, $this->method))) {
				// It is, launch it
				$this->action_instance->{$this->method}();
			}
			else {
				// The requested method does not exist, display an error
				return error('The requested method does not exist!', false);
			}
		}
		else {
			// The requested class does not exist, display an error
			return error('The requested controller does not exist!', false);
		}
	}
	
	/**
	 * Get the action class.
	 * 
	 * @return string The current action class.
	 */
	public function getClass() {
		return $this->class;
	}
	
	/**
	 * Get the action method.
	 * 
	 * @return string The current method.
	 */
	public function getMethod() {
		return $this->method;
	}
	
	/**
	 * This function get or sets the value of the requested request parameter.
	 *
	 * @param string $type The input type (GET, POST, REQUEST, FILES, COOKIE, SESSION, SERVER). Defaults to GET.
	 * @param string $name The name of the input parameter. Defaults to 'submit'.
	 * @param string $raw_value If that parameter is boolean true, return parameter value 'as is',
	 *                          without converting it to a string. In case the parameter
	 *                          is some other value, set it to the appropriate parameter name.
	 *                          Currently, The only variable types allowed as parameter values are strings and arrays.
	 * @example $app->input('get', 'param_name'); For retrieving the value of the `param_name` GET parameter as a string.
	 *                                            This should be the default usage.
	 * @example $app->input('get', 'param_name', true); For retrieving the value of the `param_name` GET parameter
	 *                                                  exactly as we got it (as an array, for example).
	 * @example $app->input('get', 'param_name', 'param_value'); For setting the value of the `param_name` GET parameter.
	 * @return mixed The input parameter or null if it does not exist.
	 */
	public function input($type = 'get', $name = 'submit', $raw_value = false) {
		// Get the input type
		$type = (!empty($type) && is_string($type)) ? strtolower($type) : 'get';
		
		// Get the input name
		$name = (!empty($name) && is_string($name)) ? $name : 'submit';
		
		// Get the optional value. If the value exist and not `true`, set it to the parameter,
		// if it's `true`, return the parameter without converting it to string
		$raw_value = !empty($raw_value) ? $raw_value : false;

		// Check if we need to set the parameter value
		if( !empty($raw_value) && (is_string($raw_value) || is_array($raw_value)) ) {
			// We do, so just set its value
			$this->input->{$type}[$name] = $raw_value;
		} elseif( !is_bool($raw_value) ) {
		    // The parameter value specified is neither a string nor an array,
		    // and it's also not a boolean (for the raw parameter value return),
		    // thus not allowed to be set as an input value
		    error( sprintf('Setting input parameter values as type "%s" is not allowed!', gettype($raw_value)) );
		}
		else {
			// We doesn't, so check if the parameter exist
			if( isset($this->input->$type) && array_key_exists($name, $this->input->$type) ) {
				// It is! Return it as a string, or if the user specifically requested - as is
				return ( $raw_value === true ) ? $this->input->{$type}[$name] : (string)($this->input->{$type}[$name]);
			} else {
			    // It doesn't! Return null
			    return null;
			}
		}
	}
}
?>