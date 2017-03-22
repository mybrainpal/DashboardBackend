/**
 * This file contains all the JS related to the user login template.
 */

/**
 * Initialize the user login page.
 */
function initUserLogin() {
	// Set the dashboard tab as the active one
	headerSetActive('profile');
	
	// Hide the header DIV
	$('#divHeaderMain').hide(); 
	
	// Hide the log DIV
	$('#divContentLog').hide(); 
	
	// Hide the statistics DIV
	$('#divContentStatistics').hide();
	
	// Set the content header to fill the entire screen
	$('#divContentMain').width('100%')
		.css('left', '0px'); // And fix position on the left
	
	// Set the real content DIV to fill the entire screen
	$('#divContentContent').width('100%')
		.css('margin-left', '0px');
}

/**
 * Called when the login button is pressed.
 * Checks if the user has entered the correct credentials - if he is,
 * he loads the main dash board, otherwise it shows an error.
 */
function login() {
	// Sets the AJAX callback
	ajax_callback = function(data) {
		
		// Successful login!
		if( data['success'] ) {
			// @TODO add dashboard loading mechanism (maybe a redirect?)
			redirect('Dashboard/Home/');
		} else {
			// Login failed! display the error
			$('#labelUserLoginError').text(data['error_msg']);
		}
	}

	// Sends a login request to the server
	ajax(system_root + 'User/Login', ajax_callback, 'post', $( '#divUserLoginForm' ).serialize());
	
	// Prevent the browser from reloading the page
	return false;
}

$(function () {
	// Call the initializer function
	initUserLogin();
});