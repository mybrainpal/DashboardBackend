/**
 * This file contains all the JS related to the dashboard pages.
 */

/* CONSTANTS */
DASHBOARD_CATEGORY_NAME = 'Home';
DASHBOARD_NAVBAR_LINKS = {
		'STATUS': 'Dashboard/Home',
		'ATTACKS': 'Dashboard/Attacks',
		'METERS': 'Dashboard/Meters'
}

LOG_TEXT = {0: ['info', 'Fraud Attempt', '789P2Y4QDL'],
		1: ['warning', 'Malformed Authentication', 'K4OIJL9EIP'],
		2: ['info', 'Data Leak', '1LCZPMXIXD'],
		3: ['info', 'Broadcast Discovery', 'N7CVM3MRHG'],
		4: ['critical', 'Appliance Injection', '0UMMU0ZC3W'],
		5: ['info', 'Unauthorized Appliance', 'FUXY4OJZYO'],
		6: ['info', 'Fraud Attempt', 'N21TNJ729Q'],
		7: ['warning', 'Malformed Headers', 'DO25NLZV94'],
		8: ['info', 'Invalid MID', '0HTPF3XE69'],
		9: ['info', 'Appliance Discovery', '63HIFCG81P'],
		10: ['critical', 'Privileged Execution', 'SZX94ZW8L9'],
		11: ['info', 'Data Leak', '7Z25QSLCNM'],
		12: ['warning', 'Overflowing Packet', '4XEQ73UV34'],
		13: ['info', 'Unauthorized Appliance', 'RGJ1NR7R93'],
		14: ['info', 'Invalid MID', 'RP9MNF1B1F'],
		15: ['warning', 'Malformed Authentication', '7TD7X0XRAU'],
		16: ['critical', 'Overflowing Packet', 'TV0X20ACP9'],
		17: ['info', 'Broadcast Discovery', '9YJKO2TG3I'],
};

/**
 * Initialize the current JS file.
 */
function initDashboard() {
	// If the user switched category
	if(header_active_category != DASHBOARD_CATEGORY_NAME) {
		// Set the navigation bar links
		contentReloadNavbar(DASHBOARD_NAVBAR_LINKS);
		
		// Active the appropriate navigation bar links
		headerSetActive(DASHBOARD_CATEGORY_NAME);
		
		// Initialize the log DIV
		initializeLog();
		
		// Initialize the statistics DIV
		initStatistics();
	}
}

/**
 * Initializes and creates the detected attacks graph.
 */
function createAttacksGraph(canvas_id) {
	// The labels (X axis) for the graph
	labels = ["17:50", "17:55", "18:00", "18:05", "18:10", "18:15", "18:20", "18:25"];
	
	// Create the chart
	my_chart = new VChart(document.getElementById(canvas_id), labels, 'bar');

	// @TODO actually implement this. For now we're using a prepared data set
	// Add the data sets
	my_chart.addDataset(TEXT_HIGH, [1256, 1378, 1465, 1254, 1178, 1279, 1732, 1876, 1643], COLOR_RED);
	my_chart.addDataset(TEXT_MIDDLE, [2344, 2486, 2245, 2498, 2467, 2678, 2247, 2145, 2345], COLOR_LIGHTBLUE);
	my_chart.addDataset(TEXT_LOW, [4578, 4236, 4479, 4312, 4983, 4722, 4521, 4564, 4487], COLOR_GREEN);

	// Add the graph to the canvas
	my_chart.addToCanvas();
}

/**
 * Initializes and creates the online meters graph.
 */
function createMetersGraph(canvas_id) {
	// The labels (X axis) for the graph
	labels = ["17:50", "17:55", "18:00", "18:05", "18:10", "18:15", "18:20", "18:25"];
	
	// Create the chart
	my_chart = new VChart(document.getElementById(canvas_id), labels, 'bar');

	// @TODO actually implement this. For now we're using a prepared data set
	// Add the data sets
	my_chart.addDataset('Online Meters', [2345, 2159, 2578, 2234, 2479, 2365, 2098, 2678, 2345], [118, 185, 197]);

	// Add the graph to the canvas
	my_chart.addToCanvas();
}

/**
 * Initialized the log DIV with the text that returned from the server.
 */
function initializeLog() {
	// @TODO actually implement this. For now we're using a prepared text
	
	// Clear the log
	$('#divContentLogText').html('');
	
	// Loop through the text logs
	for(id in LOG_TEXT) {
		// Create a new text area
		log_textarea = document.createElement('textarea');
		
		// Get the text meta info 
		severity = LOG_TEXT[id][0];
		text = LOG_TEXT[id][1];
		meter_id = LOG_TEXT[id][2]
		
		// Decide which color to use
		switch(severity) {
			case 'info':
				color = 'var(--color-log-info)';
				font_weight = 'normal';
				break;
			case 'warning':
				color = 'var(--color-log-warning)';
				font_weight = 'normal';
				break;
			case 'critical':
				color = 'var(--color-log-critical)';
				font_weight = 'bold';
				break;
			default:
				color = 'var(--color-text)';
				font_weight = 'normal';
		}

		// Add the log class
		$(log_textarea).addClass('textareaContentLog')
			.attr('readonly', '1') // Force it to be read only
			.css('color', color) // Set its color
			.css('font-weight', font_weight) // Should it be bold?
			.text('{0}: {1}'.format(meter_id, text)) // Set its text
			.appendTo($('#divContentLogText')); // Add the link to our crafted link's DIV
	}
}

/**
 * Initialize the statistics DIV, containing the attack types chart,
 * the network status and the tips section.
 */
function initStatistics() {
	// First of all, initialize the DIVS
	initStatisticsDivs();
	
	// Initialize the attack types chart
	initAttackTypesChart();
	
	// Initialize the network status chart
	initNetworkStatusChart();
	
	// Initialize the tips DIV
	initTipsDiv();
}

/**
 * Initializes the different elements being used in the statistics panel.
 */
function initStatisticsDivs() {
	// Reset the statistics HTML
	$('#divContentStatistics').html('');
	
	// Create the Attack Types chart DIV
	div_attack_types = document.createElement('div');
	$(div_attack_types).height('95%') // Set its height
		.width('30%') // Set its width
		.css('display', 'inline-block') // Set it to be an in line block
		.css('float', 'left') // Set it to be an in line block
		.appendTo($('#divContentStatistics')); // Append it to the main statistics DIV

	// Create the Network Status chart DIV
	div_network_status = document.createElement('div');
	$(div_network_status).height('95%') // Set its height
		.width('30%') // Set its width
		.css('display', 'inline-block') // Set it to be an in line block
		.css('float', 'left') // Set it to be an in line block
		.appendTo($('#divContentStatistics')); // Append it to the main statistics DIV
	
	// Create the Tips & Info DIV
	div_tips = document.createElement('div');
	$(div_tips).attr('id', 'dashbaord-home-tips-div') // Set its ID
		.width('25%') // Set its width
		.height('90%')
		.css('display', 'inline-block') // Set it to be an in line block
		.css('float', 'left') // Set it to be an in line block
		.css('margin-top', '1%')
		.css('margin-left', '5%')
		.css('text-align', 'center')
		.css('background-color', 'var(--background-content)') // Set it to be an in line block
		.appendTo($('#divContentStatistics')); // Append it to the main statistics DIV
	
	// Add a canvas to each DIV
	canvas_attack_types = document.createElement('canvas'); // The attack types canvas
	$(canvas_attack_types).attr('id', 'dashbaord-home-attack-types-canvas') // Set its ID
		.appendTo(div_attack_types); // Append it to the DIV

	canvas_network_status = document.createElement('canvas'); // The network status canvas
	$(canvas_network_status).attr('id', 'dashbaord-home-network-status-canvas') // Set its ID
		.appendTo(div_network_status); // Append it to the DIV
	
	span_netowrk_status_percentage = document.createElement('span'); // The network status percentage status
	$(span_netowrk_status_percentage) // Set its ID
		.attr('id', 'spanContentNetworkStatusPercentage')
		.text('93%')
		.appendTo(div_network_status); // Append it to the DIV
}

/**
 * Initializes the attack types chart.
 * 
 * @TODO Actually implement this. For the moment we're using fake data.
 */
function initAttackTypesChart() {
	// Add the attack types chart
	// The labels (X axis) for the graph
	labels = ["Injections", "Auth Bypasses", "Malformed Packets", "Data Leaks", "Invalid Credentials"];
	canvas = document.getElementById('dashbaord-home-attack-types-canvas');
	
	// Custom options for the chart
	options = {
			title: {
                display:true,
                text:"Attack Types"
            },
            legend:  {
            	display: false
            },
            tooltips: {
            	callbacks: {
					// Set a custom tool tip template
					label: function(tooltipItem, data) {
						// Just return the value with a percentage sign
						return tooltipItem.yLabel + '%';
					}
				}
            },
            showPercentage: true,
	}
	
	// Create the chart
	my_chart = new VChart(canvas, labels, 'bar', options);
	
	// Add the data sets
	my_chart.addDataset('Attack Types', [15, 7, 32, 26, 20], COLOR_LIGHTGREEN, 'bar', COLOR_GREEN.concat([0.4]));
	
	// Add the graph to the canvas
	my_chart.addToCanvas();
}

/**
 * Initializes the network status chart.
 * 
 * @TODO Actually implement this. For the moment we're using fake data.
 */
function initNetworkStatusChart() {
	// @TODO Actually implement this. For the moment we're using fake data
	
	// Add the attack types chart
	// The labels (X axis) for the graph
	labels = [TEXT_LOW, TEXT_MIDDLE, TEXT_HIGH];
	canvas = document.getElementById('dashbaord-home-network-status-canvas');
	
	// Custom options for the chart
	options = {
			title: {
                display:true,
                text:"Network Security"
            },
            legend:  {
            	display: false
            },
            tooltips: {
            	enabled: false
            },
            cutoutPercentage: 0,
	};
	
	data_option = {
			borderWidth: [0],
	};
	
	// Create the chart
	my_chart = new VChart(canvas, labels, 'doughnut', options);
	
	// Add the data sets
	my_chart.addDataset('Attack Types', [97, 3], [COLOR_GREEN, COLOR_LIGHTBLACK],
			'pie', [COLOR_GREEN.concat(0.4)], data_option);
	my_chart.addDataset('Attack Types', [26, 74], [COLOR_LIGHTBLUE, COLOR_LIGHTBLACK],
			'pie'), [true], data_option;
	my_chart.addDataset('Attack Types', [10, 90], [COLOR_RED, COLOR_LIGHTBLACK],
			'pie', [true], data_option);
	
	// We're adding these data sets just so we'll have a blue circle in the middle of the chart
	// so we could add the percentage
	my_chart.addDataset('Attack Types', [1], [COLOR_BLUE],
			'pie', [false], data_option);
	my_chart.addDataset('Attack Types', [1], [COLOR_BLUE],
			'pie', [false], data_option);
	
	// Add the graph to the canvas
	my_chart.addToCanvas();
}

/**
 * Displays a random tip for using the system.
 * 
 * @TODO Actually implement this. For the moment we're using a static tip.
 */
function initTipsDiv() {
	title_text = 'TIPS & INFO';
	tip_text = 'Click on the <img src="' + frontend_root + 'images/navbar_icons/settings.png"/> sign in order to \
		modify the system settings.';
	button_text = 'TRY IT NOW';
	more_tips_text = 'Need more tips? Try our <a href="https://vaultra.com">Info Center</a>';
	
	// The tips title text
	span_netowrk_status_percentage = document.createElement('span');
	$(span_netowrk_status_percentage) // Set its ID
		.text(title_text)
		.css('font-size', '16px')
		.css('margin-top', '4%')
		.css('margin-left', '0px')
		.addClass('vaultra-title')
		.appendTo($('#dashbaord-home-tips-div')); // Append it to the DIV
	
	// The tip itself
	span_netowrk_status_percentage = document.createElement('span');
	$(span_netowrk_status_percentage) // Set its ID
		.html(tip_text)
		.css('font-size', '14px')
		.css('margin-left', '0px')
		.css('padding', '3%')
		.css('padding-left', '8%')
		.css('padding-right', '8%')
		.addClass('vaultra-subtitle')
		.appendTo($('#dashbaord-home-tips-div')); // Append it to the DIV
	
	// The "Try Now" button
	span_netowrk_status_percentage = document.createElement('button');
	$(span_netowrk_status_percentage) // Set its ID
		.text(button_text)
		.css('margin-top', '6%')
		.width('40%')
		.height('20%')
		.css('border-radius', '5px')
		.css('border', '1px')
		.addClass('btn-vaultra')
		.appendTo($('#dashbaord-home-tips-div')); // Append it to the DIV
	
	// The more tips text
	span_netowrk_status_percentage = document.createElement('span');
	$(span_netowrk_status_percentage) // Set its ID
		.html(more_tips_text)
		.css('position', 'relative')
		.css('font-size', '14px')
		.css('top', '10%')
		.addClass('vaultra-subtitle')
		.appendTo($('#dashbaord-home-tips-div')); // Append it to the DIV
}

$(function () {
	// Call the initializer function
	initDashboard();
});