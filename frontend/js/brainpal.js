/* CONSTANTS */
const SYSTEM_PATH	= '/';
const FRONTEND_PATH = 'frontend';

const TEXT_LOW		= 'Normal';
const TEXT_MIDDLE	= 'Possibly Malicous';
const TEXT_HIGH		= 'Malicous'

/* COLORS */
const COLOR_GREEN		= [153, 203, 97];
const COLOR_LIGHTGREEN	= [217, 249, 66];
const COLOR_DARKGREEN	= [138, 196, 73];
const COLOR_BLUE		= [28, 40, 52];
const COLOR_LIGHTBLUE	= [72, 116, 151];
const COLOR_LIGHTBLACK	= [22, 31, 40];
const COLOR_RED			= [230, 29, 0];

/* GLOBALS */
web_root = window.location['protocol'] + '//' + window.location['host'] + '/'; // The web root of the system
system_root = web_root; // The links web root (web_root + index.php)
frontend_root = web_root + FRONTEND_PATH; // The front end web root


/**
 * Called when the page is loaded (using the body's onLoad event).
 * Responsible for all body-related initialization procedures.
 */
function mainStart() {	
	// Call all the initialization functions
	headerStart();
	contentStart();
	
	// Slide down the content DIV
	elementSlideDown($('#divContentContent'));
	
	// Fade in the content DIV
	elementFadeIn($('#divContentContent'));
}

/**
 * Responsible for executing an AJAX request.
 * If the returned data is in JSON format, the function automatically decodes it. 
 * 
 * @param string url The URL destination.
 * @param function callback A callback called when the server responds.
 * @param string type The request type (GET, POST). Defaults to 'GET'.
 * @param string data Additional HTTP parameters. Defaults to an empty string.
 * @return mixed In case of a sucessful request (200 OK), 
 * 				 the function will call the parameter callback
 * 				 using the parsed response data.
 * 				 In case the data has been JSON encoded, the function will decode it.
 */
function ajax(url, callback, type = 'get', data = '') {
	// Trim everything!
	url = url.trim();
	type = type.trim();

	// Make sure the data always has a 'submit' value appended to it
	if(data.indexOf('submit=') == -1) {
		// It doesn't, append the parameter
		data += '&submit=1';
	}
	if(data.indexOf('ajax=') == -1) {
		// It doesn't, append the parameter
		data += '&ajax=1';
	}
	if(data.indexOf('token=') == -1) {
		// It doesn't, append the parameter
		data += '&token=' + csrf_token;
	}
	
	// Send the AJAX request
	$.ajax({
		type: type,
		url: url,
		data: data,
	}).done( function(data, textStatus, request) { // Success
		// Check if we should redirect
		if( data['redirect'] ) {
			redirect(data['redirect']);
		}
		
		// Store the new CSRF token
		csrf_token = data['csrf_token'];
		
		// Call the requested callback
		callback(data, textStatus, request)
	}).fail(function(xhr, textStatus, error) { // Failure
		// Notify the developer his AJAX request failed
	    console.log('AJAX request to "' + url + '" failed, with error message "' + error);
	}).always(function() { // Always being called no matter the result
	});
}

/**
 * Responsible for redirecting the browser to a different URL.
 * 
 * @param string url The URL destination.
 */
function redirect(url) {
	// Make sure the URL always starts with an 'http' prefix
	if(url.indexOf('http') != 0) {
		// It doesn't, add our web root url to it
		url = web_root + url;
	}
	
	// Perform the redirect
	window.location.replace(url);
}