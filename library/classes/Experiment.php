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
class Experiment extends Queryable {
    
    /**
     * @see Queryable::$table
     */
    protected $table = '`experiments`';
    
    /**
     * @see Queryable::$update_fields
     */
    protected $update_fields = array('tracker_id', 'groups', 'code');
    
	/**
	 * The experiment tracker's ID.
	 *
	 * @var int
	 */
	protected $tracker_id;
	
	/**
	 * The number of groups this experiment has.
	 * 
	 * @var string
	 */
	protected $groups;
	
	/**
	 * The JavaScript code for this experiment.
	 * @TODO We are not inserting/using this JS code right now.
	 *
	 * @var string
	 */
	protected $code;
	
	/**
	 * Returns the clients information for the loaded tracker.
	 *
	 * @param bool $data Optional. Should the function return the clients data or just their amount?
	 * @param int $days Optional. The amount of days to query. Defaults to DEFAULT_QUERY_DAYS.
	 * @param int $limit Optional. The query limit. Defaults to DEFAULT_QUERY_LIMIT.
	 * @return array A two dimensional array containing the clients and their information.
	 */
	public function getClients($data = true, $days = DEFAULT_QUERY_DAYS, $limit = DEFAULT_QUERY_LIMIT) {
	    global $app;
	     
	    // Make sure that the current instance can support querying,
	    // and validate the user input
	    $this->validateQueryInput($days, $limit);
	     
	    // Check if we should return the data or just the clients amount
	    $select = ($data) ? 'DISTINCT client_id' : 'count(DISTINCT client_id)';
	     
	    // Get all the unique clients for that tracker
	    $result = $app->db->select_distinct($select . ', DATE(created)')->from('`events`')->where(
	        '`tracker_id`=:tracker_id AND
            (`created` BETWEEN DATE_SUB(SUBDATE(CURDATE(),1), INTERVAL :days DAY) AND CURDATE())', array(
                ':tracker_id' => $this->id,
                ':days' => $days,
        ))->group_by('DATE(created)')->limit($limit)->execute();

        // Organize the query result
        $result = $this->organizeResult($result);

        // Success! Return the clients information
        return $result;
	}
}

?>