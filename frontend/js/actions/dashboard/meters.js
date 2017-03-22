/**
 * This file contains all the JS related to the dashboard's Meters page.
 */

/* CONSTANTS */
TITLE 		= 'ONLINE METERS';
SUBTITLE	= 'In the last 30 minutes';

/**
 * Initialize the current JS file.
 */
function initDashboardMeters() {
	// Set the active content navigation bar item
	contentSetActive('Meters');
	
	// Set the page title
	contentSetTitle(TITLE, SUBTITLE);
	
	// Create the attacks graph
	createMetersGraph('dashbaord-meters-canvas');
	
	// Initialize the log DIV
	initializeLog();
}

$(function () {
	// Call the initializer function
	initDashboardMeters();
});