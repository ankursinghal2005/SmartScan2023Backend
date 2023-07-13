<?php

$arrMapping = [  
    //invoice number
    'invoice no.' => 'invoice number',
    'invoice:' => 'invoice number',
    'invoice' => 'invoice number',
    'invoice id' => 'invoice number',
    'invoice#' => 'invoice number',
    'invoice number' => 'invoice number',
    'invoice no:' => 'invoice number',
    //vendor number
    'customer id' => 'vendor number',
    'cust:' => 'vendor number',
    //vendor details
    'name' => 'vendor name',
    'street' => 'address details',
    'city' => 'city',
    'state' => 'state',
    'zip_code' => 'zip code',
    //invoice total amount
    'total' => 'invoice total',
    'total due' => 'invoice total',
    //invoice date
    'date' => 'invoice date',
    'invoice date' => 'invoice date',
    'date:' => 'invoice date',
    'order\ndate' => 'invoice date',
    //scheduled payment
    'final due date' => 'scheduled payment',
    'please pay invoice by' => 'scheduled payment',
    'due date' => 'scheduled payment',
    //PO number
    'p.o. no' => 'po number',
    'p.o. number' => 'po number',
    'cust. p.o.' => 'po number',
    //delivery charges
    'shipping' => 'delivery charges',
    'shipping & handling' => 'delivery charges',
    //Line Item details
    'total' => 'detail amount',
    'amount' => 'detail amount',    
    'subtotal' => 'detail amount',
    'description' => 'item description',
    'items' => 'item description',
    //quantity received
    'qty' => 'quantity',
    'qtyn' => 'quantity',
    'quantity received' => 'quantity',
    //delivery address
    'ship to:' => 'delivery address',
    'ship to' => 'delivery address',
    'location:' => 'delivery address',
    'Remit to' => 'delivery address',
    'tax exemption' => 'tax payer id',   
    't' => 'phone number', 
];

$removeKeys = [
    'RECEIVER_SHIP_TO',
    'RECEIVER_ADDRESS',
    'RECEIVER_NAME',
    'RECEIVER_PHONE',
    'VENDOR_REMIT_TO',
    'RECEIVER',
];

?>
