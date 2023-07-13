<?php
require 'vendor/autoload.php'; // Assuming you have AWS SDK for PHP installed via Composer

use Aws\Textract\TextractClient;
use Aws\Comprehend\ComprehendClient;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;

// Analyze expense using AWS Textract
function analyzeExpense($bucketName, $objectKey, $textractClient)
{
    try {
        $response = $textractClient->analyzeExpense([
            'Document' => [
                'S3Object' => [
                    'Bucket' => $bucketName,
                    'Name' => $objectKey,
                ],
            ],
        ]);

        return $response; //['ExpenseDocuments'][0]['SummaryFields']['LineItemExpenseFields']['LineItemExpenseFieldList'];
    } catch (AwsException $e) {
        echo 'Error: ' . $e->getAwsErrorMessage() . PHP_EOL;
        return null;
    }
}

// Detect entities using Amazon Comprehend
function detectEntities($text, $comprehendClient)
{
    try {
        $response = $comprehendClient->detectEntities([
            'LanguageCode' => 'en',
            'Text' => $text,
        ]);

        return $response['Entities'];
    } catch (AwsException $e) {
        echo 'Error: ' . $e->getAwsErrorMessage() . PHP_EOL;
        return null;
    }
}

// Enrich labels using schema.org and Amazon Comprehend
function enrichLabels($labels)
{
    $schemaMapping = [
        'Date' => 'https://schema.org/DateTime',
        'Amount' => 'https://schema.org/PriceSpecification',
        'Currency' => 'https://schema.org/Text',
        'Invoice' => 'https://schema.org/Invoice',
        'Payment' => 'https://schema.org/PaymentChargeSpecification',
        'Account' => 'https://schema.org/BankAccount',
        'Transaction' => 'https://schema.org/FinancialTransaction',
        'Tax' => 'https://schema.org/Tax',
        'Vendor' => 'https://schema.org/Organization',
        'Customer' => 'https://schema.org/Person',
        'Product' => 'https://schema.org/Product',
        'Place' => 'https://schema.org/Place',
        'Image' => 'https://schema.org/ImageObject',
        'Video' => 'https://schema.org/VideoObject',
        'Audio' => 'https://schema.org/AudioObject',
        'Text' => 'https://schema.org/Text',
        'URL' => 'https://schema.org/URL',
        'Supplier' => 'https://schema.org/Organization',
        'Vendor' => 'https://schema.org/Organization',
        'Manufacturer' => 'https://schema.org/Organization',
        'LogisticsCompany' => 'https://schema.org/Organization',
        'DeliveryMethod' => 'https://schema.org/Text',
        'Shipping' => 'https://schema.org/ParcelDelivery',
        'Freight' => 'https://schema.org/ParcelDelivery',
        'Incoterms' => 'https://schema.org/Text',
        'Customs' => 'https://schema.org/GovernmentOrganization',
        'PurchaseOrder' => 'https://schema.org/Order',
        'OrderStatus' => 'https://schema.org/Text',
        'OrderDate' => 'https://schema.org/DateTime',
        'OrderNumber' => 'https://schema.org/Text',
        'BillingAddress' => 'https://schema.org/PostalAddress',
        'ShippingAddress' => 'https://schema.org/PostalAddress',
        'OrderItem' => 'https://schema.org/OrderItem',
        'UnitPrice' => 'https://schema.org/PriceSpecification',
        'Quantity' => 'https://schema.org/QuantitativeValue',
        // Add more categories as needed
    ];

    $enrichedLabels = [];

    foreach ($labels as $label) {
        $category = $schemaMapping[$label['Type']] ?? 'Unknown';

        $enrichedLabel = [
            'Label' => $label['Text'],
            'Category' => $category,
            'Confidence' => $label['Score'],
        ];

        $enrichedLabels[] = $enrichedLabel;
    }

    return $enrichedLabels;
}

// Provide your AWS credentials and S3 bucket information
$awsAccessKey = 'AKIA3PDZHMKUZV4IALLT';
$awsSecretAccessKey = 'HOZkAn+LcX6eLKTLo6XpqOAr+hy8gKC17GlEvxtn';
$bucketName = 'oghackathon23';
$objectKey = 'invoices/sample_invoice_01.png';

// Set up AWS credentials
$credentials = new \Aws\Credentials\Credentials($awsAccessKey, $awsSecretAccessKey);

// Create AWS clients for Textract, Comprehend, and S3
$textractClient = new TextractClient([
    'region' => 'us-east-1', // Update with your desired region
    'version' => 'latest', // Update with the desired version
    'credentials' => $credentials,
]);

$comprehendClient = new ComprehendClient([
    'region' => 'us-east-1', // Update with your desired region
    'version' => 'latest', // Update with the desired version
]);

$s3Client = new S3Client([
    'region' => 'us-east-1', // Update with your desired region
    'version' => 'latest', // Update with the desired version
    'credentials' => $credentials,
]);

// Analyze expense using AWS Textract
$expenseFields = analyzeExpense($bucketName, $objectKey, $textractClient);

// Extract the document text from S3
$documentText = $s3Client->getObject([
    'Bucket' => $bucketName,
    'Key' => $objectKey,
])->get('Body');

// Detect entities using Amazon Comprehend
$entities = detectEntities($documentText, $comprehendClient);

// Enrich labels with schema.org mapping
$enrichedLabels = enrichLabels($entities);

// Print enriched labels
foreach ($enrichedLabels as $label) {
    echo 'Label: ' . $label['Label'] . PHP_EOL;
    echo 'Category: ' . $label['Category'] . PHP_EOL;
    echo 'Confidence: ' . $label['Confidence'] . PHP_EOL;
    echo PHP_EOL;
}
