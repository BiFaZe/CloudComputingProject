<?php
// backend.php

require 'vendor/autoload.php'; // Include the Composer autoload file

use Aws\DynamoDb\DynamoDbClient;
use Aws\Credentials\Credentials;

// AWS credentials and configuration
$credentials = new Credentials('AKIAWEJEYE5NAQF5KLK6', 'qkYJKVLxi86lAI8zMKFeyttRQ/GUIy5/SqN63ABN');
$config = [
    'region' => 'us-east-1', // Replace with your AWS region
    'version' => 'latest',
    'credentials' => $credentials,
];

// Create an AWS DynamoDB client
$ddb = new DynamoDbClient($config); 
$params = [
    'TableName' => 'MobyGameDataset',
];

function getGames($ddb, $params) {
    try {
        $result = $ddb->scan($params);
        $items = $result->get('Items');

        return json_encode(['status' => 'success', 'data' => $items]);
    } catch (Aws\Exception\AwsException $e) {
        // Handle the error
        return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// Handle AJAX request
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'getGames':
            echo getGames($ddb,$params);
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} else {
    echo json_encode(['error' => 'Action not specified']);
}


?>
