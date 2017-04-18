<?php

/**
 * This class is responsible for everything experiment related - such as:
 * - Clients Retrieval
 * - Conversion rates
 * - Groups analyzing
 * - Other experiment related activity
 * 
 * @author Nati
 */
class Experiment {
	
	/**
	 * The experiment ID.
	 * 
	 * @var int
	 */
	private $id;
	
	/**
	 * The experiment tracker's ID.
	 *
	 * @var int
	 */
	private $tracker_id;
	
	/**
	 * The number of groups this experiment has.
	 * 
	 * @var string
	 */
	private $groups;
	
	/**
	 * The JavaScript code for this experiment.
	 * @TODO We are not inserting/using the JS right now.
	 *
	 * @var string
	 */
	private $code;

    /**
	 * Initialize the default properties for the experiment.
	 * 
	 * @param int $experiment_id Optional - The experiment ID to load.
	 * @param bool $init Optional - Should the class load the information of the specified experiment?
	 */
	public function __construct($experiment_id = 0, $init = true) {
		// Set the tracker ID
		$this->id = intval($experiment_id);
		
		// If we should load the tracker information from the DB
		if( $this->id && $init ) {
    		// Load it
    		$this->loadExperiment();
		}
	}
	
	/**
	 * Loads an experiment into the object's instance.
	 * In case no experiment ID is entered, the function takes the experiment ID from the ID property instead.
	 * 
	 * @param int $experiment_id Optional. The experiment ID to load.
	 */
	public function loadExperiment($experiment_id = 0) {
		global $app;
        
		// If no username is provided, attempt to get one from the session
		if( empty($experiment_id) && !empty( $this->id ) ) {
			// There's a user logged in, use it
			$experiment_id = $this->id;
		}
		
		// Make sure the tracker ID is an integer
		$experiment_id = intval($experiment_id);
		
		// Make sure the tracker ID is a valid number
		if($experiment_id <= 0) {
		    // It isn't, throw an exception
		    error('Invalid experiment ID at Experiment::loadExperiment()!');
		}
		
		// Now that we have a valid tracker ID, we can grab its info from the DB
		$result = $app->db->select('*')->from('`experiments`')->where(
		    '`id` = :id', array(
		        ':id' => $experiment_id
		    ))->execute();
		
		// If the experiment was found in the DB
		if( count($result) === 1 ) {
			// Use it
			$experiment = $result[0];

			// Set the tracker ID
			$this->id = intval($experiment['id']);

			// The ID of this experiment tracker
			$this->tracker_id = intval($experiment['tracker_id']);
			
			// The number of groups this experiment has
			$this->groups = intval($experiment['groups']);
			
			// The experiment JS code
			$this->url = $experiment['code'];
		// Otherwise raise an error
		} else {
			error('Experiment does not exists at Experiment::loadExperiment()!');
		}
	}
	
	/**
	 * Get the experiment ID.
	 * 
	 * @return int The experiment ID.
	 */
	public function getId()
	{
	    return $this->id;
	}
	
	/**
	 * Get the experiment's tracker ID.
	 * 
	 * @return int The tracker ID.
	 */
	public function getTrackerId()
	{
	    return $this->tracker_id;
	}
	
	/**
	 * Get the number of groups this experiment has.
	 * 
	 * @return int The number of groups.
	 */
	public function getGroups()
	{
	    return $this->groups;
	}
	
	/**
	 * Get the experiment JavaScript code.
	 * 
	 * @return string The experiment's JavaScript code.
	 */
	public function getCode()
	{
	    return $this->code;
	}
	
	/**
	 * Set the experiment's tracker ID.
	 * 
	 * @param int The tracker ID.
	 */
	public function setTrackerId($tracker_id)
	{
	    $this->tracker_id = $tracker_id;
	}
	
	/**
	 * Set the number of groups this experiment has.
	 * 
	 * @param int The number of groups.
	 */
	public function setGroups($groups_amount)
	{
	    $this->groups = $groups_amount;
	}
	
	/**
	 * Set the experiment JavaScript code.
	 * 
	 * @param string The experiment's JS code.
	 */
	public function setCode($code)
	{
	    $this->code = $code;
	}
}

?>