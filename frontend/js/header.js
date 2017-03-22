/* CONSTANTS */
const LINKS_ICONS_PATH = frontend_root + '/images/navbar_icons/';
const LINKS_ICON_PREFIX = 'icon_';
const LINKS_ICON_EXT = '.png';

const MAX_HEADER_NAVBAR_LINK_HEIGHT = 10;
const NAVBAR_LINKS = {
		'HOME': 'Dashboard',
		'METERS': 'Meters',
		'USERS': 'Users',
		'PROFILE': 'User',
		'SETTINGS': 'Settings'
}

/* GLOBALS */
header_focused_navbar_item = null;
header_active_category = null;


/**
 * Called when the header is loaded (using the headers's onLoad event).
 * Responsible for all header-related initialization procedures.
 */
function headerStart() {
	// Populate the navigation bar
	headerPopulateNavbar();
}

/**
 * Populates the header's navigation bar links.
 */
function headerPopulateNavbar() {
	// Calculate the amount of width each link block should take
	// (according to the number of links there are), to a maximum of 25%.
	links_height = Math.min(100 / Object.keys(NAVBAR_LINKS).length, MAX_HEADER_NAVBAR_LINK_HEIGHT) + '%';
	
	// Loops through the navigation bar links array
	for(name in NAVBAR_LINKS) {
		// Create the link DIV
		link_div = document.createElement('div');
		
		// Set its CSS class to the navigation bar DIV class
		$(link_div).addClass('divHeaderNavbarDIV')
			.attr('id', 'header-navbar-' + name.toLowerCase())
			.height(links_height); // Set the DIV width
		
		// Create the link icon
		link_img = document.createElement('img');
		
		// Set its source
		$(link_img).addClass('divHeaderNavbarLinkIcon')
			.addClass('vertical-center') // The icon should be vertically centered as well
			.attr('src', LINKS_ICONS_PATH + // Set the image source according to the links dictionary
					name.toLowerCase().replace(' ', '-') +
					LINKS_ICON_EXT) 
			.appendTo($(link_div)); // Add the link to our crafted link's DIV

		// Create a link
		link_a = document.createElement('a');
		
		// Set its CSS class to the navigation bar links class
		$(link_a).addClass('divHeaderNavbarLink')
			.addClass('vertical-center') // The link should be vertically centered as well
			.text(name) // Set its text according to the links dictionary
			.attr('href', system_root + NAVBAR_LINKS[name]) // Set its HREF according to the links dictionary
			.on( 'click', headerLinkClick) // Add the onClick event
			.appendTo($(link_div)); // Add the link to our crafted link's DIV

		// Append the DIV to the main navigation bar DIV
		$('#divHeaderNavbar').append(link_div);
	}
}

/**
 * Set the active navigation bar link.
 * 
 * @param name The name of the link to focus.
 */
function headerSetActive(name) {
	// If there's an active menu item, un-focus it
	if( header_focused_navbar_item ) {
		// Reset its background color back to normal
		$(header_focused_navbar_item).css('background-color', 'var(--background-navbar)');
		$(header_focused_navbar_item).find('a').removeClass('active');
	} else {
		// No menu has been set, make sure we're being called only when the page is loaded
		if(document.readyState != 'complete') {
			// The document hasn't been loaded yet, run this function again when it will
			$( window ).ready(function () {
				headerSetActive(name);
			});
			
			return;
		}
		
	}
	
	// Get the activated navigation bar item DIV
	navbar_item = $('#header-navbar-' + name.toLowerCase().replace(' ', '-'));
	
	// Focus it
	$(navbar_item).css('background-color', 'var(--background-content)');
	$(navbar_item).find('a').addClass('active');
	
	// Set the focused element
	header_focused_navbar_item = navbar_item;
	header_active_category = name;
}

/**
 * Responsible for requesting the link 'href' property using an Ajax call.
 * Later, it reloads the content DIV using the returned HTML.
 */
function headerLinkClick() {
	// Get the link path (no host needed)
	url = this.pathname;
	
	// Update the URL
	window.history.pushState(url, 'Vaultra', url);
	
	// Sets the AJAX callback
	ajax_callback = function(data) {
		// Reload the content DIV HTML
		contentReload(data['content']);
	}

	// Sends a login request to the server
	ajax(url, ajax_callback, 'get');
	
	// Return false so we won't actually navigate from the page
	return false;
}