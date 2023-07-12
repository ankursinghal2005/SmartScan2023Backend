<?php

require 'vendor/autoload.php'; // Make sure to include the AWS SDK for PHP

use Aws\Textract\TextractClient;
// Amazon S3 API credentials 
$region = 'us-east-1'; 
$version = 'latest'; 
$access_key_id = 'AKIA3PDZHMKUZV4IALLT'; 
$secret_access_key = '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'; 
$bucket = 'oghackathon23'; 

putenv("AWS_ACCESS_KEY_ID=" . $access_key_id);
putenv("AWS_SECRET_ACCESS_KEY=" . $secret_access_key);

if(empty($_GET['file_name'])){
    $statusMsg = "File upload failed!"; 
    header('Location: index.php?msg='.$statusMsg);
};
// Set up your AWS credentials and region
$credentials = [
    'key' => $access_key_id,
    'secret' => $secret_access_key,
    'region' => $region, // Replace with your desired region
    'version' => $version, 
];

// Create an instance of the Textract client
$client = new TextractClient($credentials);

$bucket = 'oghackathon23'; 

// Specify the S3 bucket and file name of the document to process
$doc = $_GET['file_name'];
$document = "s3://$bucket/$doc";


// Set the parameters for the StartDocumentTextDetection operation
$params = [
    'DocumentLocation' => [
        'S3Object' => [
            'Bucket' => $bucket,
            'Name' => $doc
        ]
    ]
];

try {
    // Start the text detection job
    $result = $client->startDocumentTextDetection($params);
    
    // Get the JobId to check the status later
    $jobId = $result['JobId'];

    // Check the status of the job
    $status = '';
    while ($status !== 'SUCCEEDED' && $status !== 'FAILED') {
        sleep(5); // Wait for a few seconds before checking the status
        $result = $client->getDocumentTextDetection(['JobId' => $jobId]);
        $status = $result['JobStatus'];
    }

    // Get the results of the completed job
    $result = $client->getDocumentTextDetection(['JobId' => $jobId]);
    $arrParsedData = [];
    echo "<pre>";
    // Process the results
    $blocks = $result['Blocks'];
    print_r($blocks);
    foreach ($blocks as $block) {
        if ($block['BlockType'] === 'LINE') {
            $arrParsedData = $block['Text'];
            echo $block['Text'] . "\n";
        }
    }
    //print_r($arrParsedData);
} catch (Exception $e) {
    // Handle any errors that occur
    echo "Error: " . $e->getMessage() . "\n";
}

?>
