<?php

/**
 * The main database class.
 * It uses mysqli as an interface to the mysql DB.
 * 
 * @author Nati
 */
class Database {
	/**
	 * The connection to the database.
	 *
	 * @var mysqli
	 */
	private $dbh;
	
	/**
	 * The current query to execute.
	 *
	 * @var string
	 */
	private $query;
	
	/**
	 * Initialize a connection to the database using the given credentials.
	 *
	 * @param string $db_host The host of the database.
	 * @param string $db_user The username for the database.
	 * @param string $db_pass The password for the database.
	 * @param string $db_name The name of the database.
	 */
	public function __construct($db_connection_name, $db_user, $db_pass, $db_name) {
	    // Add the database to the connection name
	    $db_connection_name .= "dbname=$db_name;";
	    
	    // Add the default character set to the connection name
	    $db_connection_name .= 'charset=utf8;';
	    
	    try {
    		// Create a new DB connection
    		$this->dbh = new PDO ( $db_connection_name, $db_user, $db_pass);
	    } catch (PDOException $e) {
	        try {
    	        // Maybe we need to login without a password (for staging deployments)
    	        $this->dbh = new PDO ( $db_connection_name, $db_user, '');
	        } catch (PDOException $e) {
    			// There are errors!
    			die('DB connection Error (' . $e->getMessage() . ')');
	        }
		}
		
		/* Initialize SQL modes */
		// Remove ONLY_FULL_GROUP_BY
		$this->dbh->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
	}
	
	/**
	 * Add another part of the query using the function being called.
	 *
	 * @example $app->db->select('*')->from('`users`')->where('`id`=:id', array('id' => 1);
	 * @param string $name The name of the function.
	 * @param string $args The function arguments.
	 * @return Database The modified database object
	 */
	public function __call($name, $args) {
	    /* Parse the query command (i.e 'select', 'insert', 'from', 'where') */
	    $name = str_replace('_', ' ', $name); // Replace any dashes with space (for multi-word commands)
	    $name = strtoupper($name); // Upper case the command
		$this->query .= "$name "; // Add the command to the query 
		
		// Get the value to insert after the name (if it exists)
		if ( isset($args[0]) && !empty($args[0]) ) {
			$content_part = array_shift ( $args ); // Get the value
			$args = array_shift($args); // Get the arguments
			
			// Check if there are arguments to parse
			if( isset($args) ) {
				// Now, parse the arguments
				foreach ( $args as $index => $cur_arg ) {
					// Replace the index with the quoted value
					$content_part = str_replace ($index, $this->quote( $cur_arg ), $content_part);
				}
			}
			
			// Now add everything to the final query
			$this->query .= "$content_part ";
		}
        
		// And return the object so we can use another function on it
		return $this;
	}
	
	/**
	 * Quote a variable so it'll be SQL safe.
	 * Currently supports: strings, arrays, numbers, booleans, objects, and NULLs.
	 * 
	 * @param mixed $value.
	 * @return string The value as a quoted string.
	 */
	public function quote($value) {
		// Switch according to the argument type
		switch ( strtolower(gettype($value)) ) {
			// It's a string, escape it
			case 'string' :
				return $this->dbh->quote($value);
			
			// It's an array, concat it
			case 'array' :
				// Create the return string
				$return = "(";
				
				// Loop through the array and concat its values
				foreach($value as $arg) {
					// Quote the current value
					$return .= $this->quote($arg) . ", ";
				}
					
				// Replace the last comma with a closing bracket and return the string
				return substr($return, 0, -2) . ") ";
				
			// It's a number, just return it
			case 'integer':
			case 'double':
				return sprintf("'%s'", strval($value));
				                                                
			// It's a boolean, treat it as a TinyInt
			case 'boolean':
				// Set the value either 1 or 0 (there's no boolean values in MySQL)
				return ($value ? '1' : '0');
				
			// If it's an object, try to call its __toString method, otherwise raise an error
			case 'object':
				// Check if the object has a callable __toString method
				if(is_callable(array($value, '__toString'))) {
					// It does! call it and return the escaped string
					return $this->quote($value->__toString());
				}
				
				// Otherwise, raise an error
				else {
					error('Can\'t append the object requested into the query' .
					    'As it has not __toString() method!');
				}
				
			// The value is null, so just return NULL
			case 'NULL':
				return 'NULL';
				
			// We're not supporting this type! Raise an error!
			default:
				error("Unsupported variable type '$value'");
				
		}
	}
	
	/**
	 * Executes the query and return the result.
	 * 
	 * @param boolean $raw_result Mark as true in order to get the raw 'mysqli_result' object.
	 * @return mixed An array of rows for 'select', 'show', 'describe' or 'explain' or 'true' for other query types.
	 */
	public function execute($raw_result = false) {
		// Execute the query and get the result
		$result = $this->dbh->query($this->query, PDO::FETCH_ASSOC);

		// Check for errors
		if( $result === false ) {
		    // An error occurred! Parse the error message
		    $error_msg = sprintf('SQL Error %d - %s.', $this->dbh->errorCode(), print_r($this->dbh->errorInfo(), true));
		    	
		    // Add the current query
		    $error_msg .= sprintf('Failed query: %s', $this->query);
		    	
		    // Display the error
		    error($error_msg);
		}
		
		// Reset the query
		$this->query = '';
		
		// If 'raw_result' flag is on, just return the result object
		if($raw_result) return $result;

		// If the result is true, just return it (it happens on successful queries that are not 'select', 'show', 'describe' or 'explain')
		if( $result === true ) {
			return $result;
		}
		
		/* The result is not a boolean so it's a 'mysqli_result' object */
		// Create an array for storing the results
		$results_arr = array();
		
		/* Loop through the result object and fetch everything */
		$row = $result->fetch(); // Get the first row
		
		// Loop through the rows
		while($row) {
			$results_arr[] = $row; // Save the row
			$row = $result->fetch(); // Get another row
		}
		
		// Free the result object
		$result = null;
		
		// Done!
		return $results_arr;
	}
	
	/**
	 * Return the current query when the object is used as a string.
	 *
	 * @return string
	 */
	public function __toString() {
		// Just return the current query
		return $this->query;
	}
	
	/**
	 * This function is being called whenever the user tries to get a property that does not exist.
	 * If the property exist in the `dbh` object, return it.
	 * 
	 * @param string $name The name of the property to get.
	 * @return mixed If the property exists at the `dbh` object, return it, otherwise return null.
	 */
	public function __get($name) {
		if( isset($this->dbh->$name) ) {
		    // If the property exist in the `dbh` object, return it
			return $this->dbh->$name;
		} elseif( method_exists($this->dbh, $name)) {
		    // If the property exist as a method in the `dbh` object, execute it
		    return $this->dbh->$name();
		} else {
		    // Otherwise return null
			return null;
		}
	} 
	
	/**
	 * Close the DB connection when the object is destroyed.
	 */
	public function __destruct() {
		// Close the connection
		$this->dbh = null;
	}
}

?>