<?php
// Include the main initialization file
require_once 'library/includes/init.php';

// Create the application
$app = new BrainPal();

// Initialize the application
$app->initialize();

// Launch the application
$app->launch();

// Display the output
$app->output->display($app->json);


?>
