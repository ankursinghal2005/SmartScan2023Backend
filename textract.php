<?php

require 'vendor/autoload.php'; // Make sure to include the AWS SDK for PHP

use Aws\Textract\TextractClient;
// Amazon S3 API credentials 

class smartScan{


    private $region = 'us-east-1'; 
    private$version = 'latest'; 
    private$access_key_id = 'AKIA3PDZHMKUZV4IALLT'; 
    private$secret_access_key = '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'; 
    private $bucket = 'oghackathon23'; 

    private $client = '';

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
            
            $arrData = ['Label' => $LabelDetection,'value'=> $ValueDetection];
            if(!empty($summaryFields['GroupProperties'][0]['Types'])){
                $GroupProperties = $summaryFields['GroupProperties'][0]['Types'][0]; 
                $arrFinalData[$GroupProperties][$Type] = $arrData;
            }else{
                $arrFinalData[$Type] = $arrData;
            }
        }
        return $arrFinalData;
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
                    
                    $arrData[] = ['Label' => $LabelDetection,'value'=> $ValueDetection];
                    
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
