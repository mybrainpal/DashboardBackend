/**
 * This file contains all the JS related to the user profile pages.
 */

/* CONSTANTS */
USER_CATEGORY_NAME = 'Profile';
USER_NAVBAR_LINKS = {
		'PROFILE': 'User/Profile',
		'BASIC INFORMATION': 'User/basicInfo',
		'ADDITIONAL INFORMATION': 'User/additionalInfo',
		'CONFIGURATION': 'User/Config',
		'SIGN OUT': 'User/Logout'
}

/**
 * Initialize the current JS file.
 */
function initUser() {
	// If the user switched category
	if(header_active_category != USER_CATEGORY_NAME) {
		// Set the navigation bar links
		contentReloadNavbar(USER_NAVBAR_LINKS);
		
		// Active the appropriate navigation bar links
		headerSetActive(USER_CATEGORY_NAME);
	}
}

$(function () {
	// Call the initializer function
	initUser();
});