<?php
try{
    
    echo "<pre>";
    $result = file_get_contents('result.json');
    $result = json_decode($result,true);
    
    if(empty($result) || !is_array($result) ){
        return [];
    }
    $result = array_shift($result);
    
    if(empty($result['ExpenseDocuments']) || empty($result['ExpenseDocuments'][0]) || empty($result['ExpenseDocuments'][0]['SummaryFields']) ){
        return [];
    }
    $arrFinalData = [];
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
         
            $arrFinalData['LineItemGroup'][$LineItemkey] = $arrData ;
        }
     }
   
} catch (Exception $e) {
    // Handle any errors that occur
    echo "Error: " . $e->getMessage() . "\n";
}

print_r($arrFinalData);
die("asd");
?>