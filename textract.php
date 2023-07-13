<?php

require 'vendor/autoload.php'; // Make sure to include the AWS SDK for PHP

use Aws\Textract\TextractClient;
use Aws\Comprehend\ComprehendClient;
use Aws\Exception\AwsException;

include_once('mapping.php');

// Amazon S3 API credentials 

class smartScan{


    private $region = 'us-east-1'; 
    private$version = 'latest'; 
    private$access_key_id = 'AKIA3PDZHMKUZV4IALLT'; 
    private$secret_access_key = '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'; 
    private $bucket = 'oghackathon23'; 

    private $schemaMapping = [];

    private $client = '';

    private $comprehendClient = "";

    private $arrMapping = [];

    function __construct(){
        putenv("AWS_ACCESS_KEY_ID=" . $this->access_key_id);
        putenv("AWS_SECRET_ACCESS_KEY=" . $this->secret_access_key);

        // Set up your AWS credentials and region
        $credentials = [
            'key' => $this->access_key_id,
            'secret' => $this->secret_access_key,
            'region' => $this->region, // Replace with your desired region
            'version' => $this->version, 
        ];

        $this->client = new TextractClient($credentials);

        $this->comprehendClient = new ComprehendClient([
                                    'region' => $this->region,
                                    'version' => $this->version, // Update with the desired version
                                ]);

        $this->schemaMapping();                        
    }

    public function setMapping($arrMapping)
    {
        $this->arrMapping = $arrMapping;
    }

    private function schemaMapping(){

        $this->schemaMapping = [
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
            'Invoice Date' => 'https://schema.org/DateTime',
            'Total Amount' => 'https://schema.org/MonetaryAmount',
            'Customer Name' => 'https://schema.org/Person',
            'Shipping Address' => 'https://schema.org/PostalAddress',
            // Add more categories as needed
        ];
    }
    public function analyzeDocument($doc){
        $s3Object = [
            'S3Object' => [
                            'Bucket' => $this->bucket,
                            'Name' => $doc
                            ]
            ];

        $params = ['DocumentLocation' => $s3Object ];
        $analyzeExpenseParams = [ 'Document' => $s3Object ];
        $result = $this->client->analyzeExpense($analyzeExpenseParams);
        return $result;
    }

    public function getExpenseRecord(&$result){
        
        $arrFinalData = [];
        $arrLebel = [];
        if(empty($result['ExpenseDocuments']) || empty($result['ExpenseDocuments'][0]) || empty($result['ExpenseDocuments'][0]['SummaryFields']) ){
            return $arrFinalData;
        }
        
        foreach($result['ExpenseDocuments'][0]['SummaryFields'] AS $summaryFieldKey => $summaryFields){
           // print_r($summaryFields);
            $Type = "";
            $LabelDetection = "";
            $ValueDetection = "";
            $GroupProperties = "";
    
            if(!empty($summaryFields['Type'])){
                $LabelDetection = $Type = $summaryFields['Type']['Text']; 
            }
           
            if(!empty($summaryFields['LabelDetection'])){
                $LabelDetection = trim($summaryFields['LabelDetection']['Text']); 
            }
    
            if(!empty($summaryFields['ValueDetection'])){
                $ValueDetection = $summaryFields['ValueDetection']['Text']; 
            }

            $arrTmp = ['Label' => $LabelDetection, 'value' => $ValueDetection];
            $LabelDetection = strtolower($LabelDetection);
            if (!empty($this->arrMapping[$LabelDetection])) {
                $arrTmp['meta_tag'] = $this->arrMapping[$LabelDetection];
            }
            if (in_array($LabelDetection, $this->arrMapping)) {
                $arrTmp['meta_tag'] = $LabelDetection;
            }


            $this->detectEntities($LabelDetection, $arrTmp);

            if(!empty($summaryFields['GroupProperties'][0]['Types'])){
                $GroupProperties = $summaryFields['GroupProperties'][0]['Types'][0]; 
                $arrFinalData[$GroupProperties][$Type] = $arrTmp;
            }else{
                $arrFinalData[$Type] = $arrTmp;
            }
        }

        
        return $arrFinalData;
    }

    private function detectEntities($label,&$arrTmp)
    {
        try {
            $response = $this->comprehendClient->detectEntities([
                'LanguageCode' => 'en',
                'Text' => $label,
            ]);

            $response = json_decode(json_encode($response), true);
            $enrichedLabel = [
                'Label' => $label,
                'Category' => 'Unknown',
                'Confidence' => 0.0,
            ];

            if (!empty($response['Entities'])) {
                $entity = max($response['Entities'], function ($a, $b) {
                    return $a['Score'] <=> $b['Score'];
                });

                $category = $this->schemaMapping[$label] ?? 'Unknown';

                $enrichedLabel = [
                    'Label' => $label,
                    'Category' => $category,
                    'Confidence' => $entity['Score'],
                ];
            }

            $arrTmp['enriched_label'] = $enrichedLabel;
          

        } catch (AwsException $e) {
            echo 'Error: ' . $e->getAwsErrorMessage() . PHP_EOL;
            return null;
        }
    }

    public function getLineItemDetails(&$result){

        $arrFinalData = [];
        if(empty($result['ExpenseDocuments']) || empty($result['ExpenseDocuments'][0]) || empty($result['ExpenseDocuments'][0]['LineItemGroups']) ){
            return $arrFinalData;
        }

        foreach($result['ExpenseDocuments'][0]['LineItemGroups'] AS $LineItemGroupsKey => $LineItemGroups){
            //print_r($LineItemGroups['LineItems']); die("asd");
            foreach($LineItemGroups['LineItems'] AS $LineItemkey => $LineItems){
                //print_r($LineItems); die("asd");
                $arrData = [];
                foreach($LineItems['LineItemExpenseFields'] AS $key => $LineItem){
                    //print_r($LineItem); die("asd");
                    $Type = "";
                    $LabelDetection = "";
                    $ValueDetection = "";
                    $GroupProperties = "";
            
                    if(!empty($LineItem['Type'])){
                        $LabelDetection = $Type = $LineItem['Type']['Text']; 
                    }
                    
                    if(!empty($LineItem['LabelDetection'])){
                        $LabelDetection = trim($LineItem['LabelDetection']['Text']); 
                    }
            
                    if(!empty($LineItem['ValueDetection'])){
                        $ValueDetection = $LineItem['ValueDetection']['Text']; 
                    }

                    $arrTmp = ['Label' => $LabelDetection, 'value' => $ValueDetection];
                    $LabelDetection = strtolower($LabelDetection);
                    if (!empty($this->arrMapping[$LabelDetection])) {
                        $arrTmp['meta_tag'] = $this->arrMapping[$LabelDetection];
                    }
                    if (in_array($LabelDetection, $this->arrMapping)) {
                        $arrTmp['meta_tag'] = $LabelDetection;
                    }

                    $arrData[] = $arrTmp;
                    
                }
             
                $arrFinalData[$LineItemkey] = $arrData ;
            }
        }
        return $arrFinalData;
    }

}

try{

    
    header('Content-Type: application/json; charset=utf-8');
    $arrRes = ['status' => 'fail','data'=>[]];
    if(empty($_GET['file_name'])){
    $arrRes['msg'] = "file_name is missing !";
    echo json_encode($arrRes); exit();
    };
    
    
    $objSmartScan = new smartScan();
    $result = $objSmartScan->analyzeDocument($_GET['file_name']);
    $result =  (array) $result;
    

    /*
    // for test purpose only
    $objSmartScan = new smartScan();
    $result = file_get_contents('result.json');
    $result = json_decode($result, true);
    */


    $objSmartScan->setMapping($arrMapping);

    if(empty($result) || !is_array($result) ){
        $arrRes['msg'] = "Un able to parse the document !";
        echo json_encode($arrRes); exit();
    }

    $result = array_shift($result);
    $arrFinalData['invoice_control'] = $objSmartScan->getExpenseRecord($result);
    $arrFinalData['invoice_details'] = $objSmartScan->getLineItemDetails($result);
    
    $msg = "Document Parrsed Successfully"; 
    $arrRes = ['status' => 'success','data'=>$arrFinalData,'msg'=>$msg];
    echo json_encode($arrRes); exit();

} catch (Exception $e) {
    $msg = $e->getMessage();
    $arrRes = ['status' => 'fail','data'=>[],'msg'=>$msg];
    echo json_encode($arrRes); exit();
}




?>
