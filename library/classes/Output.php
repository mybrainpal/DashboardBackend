<?php

/**
 * A class responsible for the output of the application,
 * including template handling and themes.
 *
 * @author Nati
 *        
 */
class Output {
	
	/**
	 * The HTML template to use.
	 * 
	 * @var string
	 */
	private $template;
	
	/**
	 * A dictionary of strings containing the arguments for the template.
	 * 
	 * @var array
	 * @example array(':arg_name' => 'arg_value');
	 */
	private $template_args;
	
	/**
	 * Should the response be JSON encoded or not?
	 * If it does, the class just JSON encodes the template arguments and prints them.
	 * 
	 * @var boolean
	 */
	private $json;
	
	/**
	 * Initialize the properties of the class.
	 */
	public function __construct() {
		// Set the template as the default one
		$this->template = 'index';
		
		// Set the arguments to none
		$this->template_args = array();
	}
	
	/**
	 * Get the template currently being used.
	 *
	 * @return string The template name.
	 */
	public function getTemplate() {
		return $this->template;
	}
	
	/**
	 * Set a different template.
	 * 
	 * @param string $new_template.
	 */
	public function setTemplate($new_template) {
		// Check if the template exist first
		if( file_exists(TPL_PATH . $new_template . '.tpl') ) {
			// It does! use it
			$this->template = $new_template;
		}
		else {
			// It doesn't! log the error and print it
			error(sprintf('The template \'%s\' could not be found!', $new_template));
		}
	}
	
	/**
	 * Add arguments for the template.
	 *
	 * @param array $args The arguments for the template.
	 * @param boolean $override Override the `template_args` property with the given $args array.
	 */
	public function setArguments($args, $override = false) {
		// If we should override the arguments property
		if( $override === true ) {
			// Just set the arguments
			$this->template_args = $args;
		}
		else {
			// Otherwise, just merge the arguments property and the $args parameter
			$this->template_args = array_merge($this->template_args, $args);
		}
		
	}
	
	/**
	 * Set the JSON flag. If the flag is true, the template arguments will be JSON encoded
	 * and printed as is, without the template itself.
	 *
	 * @param boolean $json The JSON flag.
	 */
	public function setJson($json = true) {
		// Just set the JSON property
		$this->json = $json;
	}
	
	/**
	 * Parse the template and the variables and print the calculated output.
	 * If the JSON flag is on, don't use a template file and just return set template arguments
	 * as a JSON encoded string.
	 * 
	 * @param boolean $json Is the request came using AJAX?
	 */
	public function display($json = false) {
		// Check for the JSON flag
		if( $this->json === true ) {
		    // The JSON flag been set, set the content type header
		    header('Content-Type: application/json');
		    
		    // Remove any default template arguments
		    $json_parameters = array();
		    foreach($this->template_args as $key => $value) {
		        // Built in template parameters start with '{'
		        if( strpos($key, '{') !== 0 ) {
		            // Add the parameter and remove any additional colons (used to identify template arguments)
		            $json_parameters[trim($key, ':')] = $value;
		        }
		    }
		    
			// JSON encode the template arguments and print them
			echo json_encode($json_parameters);
		} elseif($json) {
		    // The AJAX flag has been set, but without the JSON flag, meaning the response should
		    // contain the specific template HTML only.
		    $content = $this->getTemplateContents($this->template);
		    
		    // Replace the arguments in the template with their corresponding values
		    $content = $this->parseArguments($content, $this->template_args);
		    
		    // We're sending a JSON response
		    header('Content-Type: application/json');
		    
		    echo json_encode(array('content' => $content,
		        'csrf_token' => $this->template_args['{:CSRF_TOKEN:}']));
		}
		else {
			// It hasn't been set, Get the content of the template
			$content = $this->getTemplateContents($this->template);
			
			// If the request hasn't came from AJAX, get the entire page
			if( !$json) {
				$content = $this->parsePage($content);
			}
			
			// Replace the arguments in the template with their corresponding values
			$content = $this->parseArguments($content, $this->template_args);
			
			// @TODO echo? really?
			// Print the parsed content
			echo $content;
		}
	}
	
	/**
	 * Display an error message.
	 * 
	 * @param string $message The error message to display.
	 */
	public function error($message) {
		global $app;
		
		// Set the error template
		$this->setTemplate(TPL_ERROR);
		
		// Set the arguments
		$this->setArguments(array(':success' => false));
		$this->setArguments(array(':error_msg' => $message));
        
		// Display the template
		$this->display($app->json);
	}
	
	/**
	 * Try to get the contents of the used template.
	 * 
	 * @param string $template The template to retrieve.
	 * @return string The raw content of the template file.
	 */
	private function getTemplateContents($template) {
		// Get the path of the template
		$template_path = TPL_PATH . $template . '.tpl';
		
		// Check if it exists
		if( file_exists($template_path) ) {
			// It is! Check if it's readable
			if( is_readable($template_path) ) {

				// It is! Retrieve its content
				return file_get_contents($template_path);
			}
			// It isn't! log and print an error
			else {
				error(sprintf('The template \'%s\' is not readable by the server!', $template));
			}
		}
		// It isn't! log and print an error
		else {
			error(sprintf('The template \'%s\' could not be found!', $template));
		}
	}
	
	/**
	 * Insert the template argument values into the raw template content.
	 * 
	 * @param string $content The template contents.
	 * @param array $args The arguments to parse.
	 * @param bool $escape Determine whether the arguments should be escaped. Default to true.
	 * @return string The template contents parsed with its arguments.
	 */
	private function parseArguments($content, $args, $escape = true) {
		// Escape the arguments if we need to
		if( $escape ) {
			// Escape the values from XSS attacks
			array_walk($args, function(&$value, $key){ $value = htmlspecialchars($value); });
		}

		// Replace the array keys with their matching values
		$content = str_replace(array_keys($args), array_values($args), $content);
		
		// Return the content
		return $content;
	}
	
	private function parsePage($content) {
		/* Get the main templates */
		$main_tpl = $this->getTemplateContents(TPL_MAIN); // Get the main template
		$head_tpl = $this->getTemplateContents(TPL_HEAD); // Get the head template
		$header_tpl = $this->getTemplateContents(TPL_HEADER); // Get the header template
		
		// Join them together
		$page_content = $this->parseArguments($main_tpl, array(
			':head_tag'      => $head_tpl,
			':header'        => $header_tpl,
			':content'       => $content,
		    ':csrf_token'    => $this->template_args['{:CSRF_TOKEN:}'],
		), false);
		
		// That's it!
		return $page_content;
	}
}

?>