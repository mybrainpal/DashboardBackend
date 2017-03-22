/**
 * This class is responsible for displaying the different graphs used in the system.
 * 
 * @author Nati
 */

/* CONSTANTS */
FONT_COLOR = '#ffffff';

BORDER_WIDTH = 2;

POINT_HOVER_RADIUS = 4;
POINT_HOVER_BORDER_WIDTH = 2.5;
POINT_HOVER_BORDER_COLOR = 'rgba(255, 255, 255, 0.5)';

GRADIENT_MAX_OPACITY = 1;
GRADIENT_MIN_OPACITY = 0.1;

/* GLOBALS */
//Initialize global configurations
Chart.defaults.global.defaultFontColor = FONT_COLOR;

class VChart {
	/**
	 * Construct the graph object and initialize its basic properties.
	 * 
	 * @param canvas Element The canvas object to fill.
	 * @param labels Array The labels of the graph.
	 * @param type String Optional. Specifies the type of the graph (bar, line, etc.)
	 * @param options Dictionary Optional. Specifies custom options for the graph
	 */
	constructor(canvas, labels, type = 'bar', options = {}) {
		// Initialize basic graph properties
		this.labels = labels;
		this.type = type;
		this.ctx = canvas.getContext("2d")
		this.height = $(canvas).parent().height();
		
		// Initialize data properties
		this.data = {'labels': this.labels};
		this.datasets = [];
		
		// Initialize default options
		this.options = {
				animation: { // Sets the animation complete hook
	            	onComplete: VChart.animationComplete
	            },
	            hover: { // Remove hover animation duration (it's not needed anyway)
	            	animationDuration: 0
	            },
				onClick: VChart.getOnClickElement, // Sets the default onClick handler
				maintainAspectRatio: false, // Don't maintain aspect ratio (width <=> height ratio isn't fixed)
				showPercentage: false, // By default we're not showing percentages
		}
		
		// Add custom settings
		for (var key in options){
			this.options[key] = options[key];
		}
	}
	
	/**
	 * Add data to the graph (a bar, a line, etc.)
	 * 
	 * @param label String The label for the data.
	 * @param data Array The actual data.
	 * @param color Array An RGB array specifying the graph color.
	 * @param type String Optional. Specifies the type of the graph (bar, line, etc.)
	 * @param gradient Boolean Optional. Specifies whether the graph background should be a gradient.
	 */
	addDataset(label, data, color, type = 'line', gradient = true, options = {}) {
		/* Initialize variables */
		var bg_color = null; // The background color object
		var new_dataset = {'label': label, // Create the new data set
				'data': data,
				'type': type
				}
		
		// If that's a pie chart, the color variable will contain array of RGB arrays
		// (multidimensional array)
		if(type == 'pie') {
			// Initialize the BG color variable as an array
			bg_color = [];
			
			// Loop through the colors
			for(var i = 0 ; i < color.length ; i++) {
				// Parse the current color
				bg_color.push(this.parseColor(color[i], gradient[i]));
			}
		} else {
			
			// It's not, just parse the color
			bg_color = this.parseColor(color, gradient);
		}
		
		// Set the color properties
		new_dataset['backgroundColor'] = bg_color;
		new_dataset['hoverBackgroundColor'] = bg_color;
		new_dataset['borderColor'] = bg_color;
		new_dataset['pointHoverBorderColor'] = POINT_HOVER_BORDER_COLOR;
		
		// Add default properties
		new_dataset['borderWidth'] = BORDER_WIDTH;
		new_dataset['pointHoverRadius'] = POINT_HOVER_RADIUS;
		new_dataset['pointHoverBorderWidth'] = POINT_HOVER_BORDER_WIDTH;
		
		// Add custom settings
		for (var key in options){
			new_dataset[key] = options[key];
		}
		
		// Add the data set
		this.datasets.push(new_dataset);
	}
	
	/**
	 * Parse an RGB array into an 'RGBA' specific string.
	 * 
	 * @param color Array An array containing the RGB values.
	 * @param gradient An array containing RGB values for gradient manipulations.
	 */
	parseColor(color, gradient) {
		var bg_color = null; // The background color object
		
		// Apply the background color
		if(gradient) {
			// The background should be a gradient, initialize the gradient target color
			if( !Array.isArray(gradient) ) {
				// By default, just lower the opacity for the gradient target
				gradient = 'rgba({0}, {1}, {2}, {3})'.format(
						color[0], color[1], color[2], GRADIENT_MIN_OPACITY)
			} else {
				// The user has specified a gradient target, check that its valid
				if(gradient.length < 3) {
					// The user hasn't specified enough RGB values, raise an error
					throw 'Gradient color values should always contain at least 3 arguments!';
				}
				
				// Parse the requested color. If no opacity is specified, just assume we should
				// use the minimum
				gradient = 'rgba({0}, {1}, {2}, {3})'.format(
						gradient[0], gradient[1],
						gradient[2], gradient[3] ? gradient[3] : GRADIENT_MIN_OPACITY)
			}
			
			// Create the gradient object
			bg_color = this.ctx.createLinearGradient(0, 0, 0, this.height);

			// Add the specified color as the gradient base
			bg_color.addColorStop(0, 'rgba({0}, {1}, {2}, {3})'.format( 
					color[0], color[1],
					color[2], color[3] ? color[3] : GRADIENT_MAX_OPACITY - (this.datasets.length * 0.2)));
			
			// Lower the opacity for the gradient target
			bg_color.addColorStop(1, gradient);
			
		} else {
			// The background shouldn't be a gradient (just a color)
			bg_color = 'rgba({0}, {1}, {2}, {3})'.format( // Lower the opacity for the gradient target
					color[0], color[1], color[2], GRADIENT_MAX_OPACITY)
		}
		
		// Return the parsed color
		return bg_color;
	}
	
	/**
	 * Adds the current graph to the requested canvas.
	 * 
	 * @return Chart The created Chart object.
	 */
	addToCanvas() {
		// Add the data sets to the data array
		this.data['datasets'] = this.datasets;
		
		// Create the new Chart object and return it
		return new Chart(this.ctx, {
			type:		this.type,
			data:		this.data,
			options:	this.options,
		});
	}
	
	/**
	 * Handles if an onClick event is triggered in the chart.
	 * Successfully detects if a data point has been clicked.
	 * 
	 * @param event Event The onClick event.
	 * @TODO Should we do something if a data point has been clicked?
	 */
	static getOnClickElement(event) {
		// Get the clicked on element
		var clicked_element = this.getElementAtEvent(event);
	}
	
	/**
	 * Called when the chart animation has finished.
	 */
	static animationComplete() {
		// Show bar percentage if required
		if( this.config.options.showPercentage == true ) {
			VChart.addBarPercentage(this);
		}
	}
	
	/**
	 * Display percentage values on top of the chart bars.
	 * 
	 * @param chart Chart The Chart object.
	 */
	static addBarPercentage(chart) {
		// Initialize the variables
	    var chartInstance = chart.chart;
	    var ctx = chartInstance.ctx;
	    var height = chartInstance.height / 3; 
	    
	    // If we've already stored the height somewhere, use it
	    if(chart.config.options.showPercentageHeight) {
	    	height = chart.config.options.showPercentageHeight;
	    } else {
	    	chart.config.options.showPercentageHeight = height - 10;
	    }

	    // Initialize the text style
	    ctx.textAlign = 'center';
	    ctx.fillStyle = 'white';
	    
	    // Loop through the chart bars
	    Chart.helpers.each(chart.data.datasets.forEach(function (dataset, i) {
	      var meta = chartInstance.controller.getDatasetMeta(i);
	      Chart.helpers.each(meta.data.forEach(function (bar, index) {
	        ctx.fillText(dataset.data[index] + '%', bar._model.x, height );
	      }),chart)
	    }),chart);
	}
}









