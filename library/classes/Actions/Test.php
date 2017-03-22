<?php 

class ActionsTest extends Actions{
    
    protected $actions = array(
        'lolzor' => false,
        'index' => false,
    );
    
	public function lolzor() {
		$this->app->output->setArguments(array(':content' => 'Test content!'));
	}
	
	public function index() {
		$this->app->output->setArguments(array(':content' => 'Test index content!'));
	}
}

?>