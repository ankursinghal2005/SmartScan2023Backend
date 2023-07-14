<?php

$arrMapping = [  
    //invoice number
    'invoice no.' => 'invoice_number',
    'invoice:' => 'invoice_number',
    'invoice' => 'invoice_number',
    'invoice id' => 'invoice_number',
    'invoice#' => 'invoice_number',
    'invoice#:' => 'invoice_number',
    'invoice #:' => 'invoice_number',
    'invoice number' => 'invoice_number',
    'invoice no:' => 'invoice_number',
    //vendor number
    'customer id' => 'vendor_number',
    'cust:' => 'vendor_number',
    'vendor#' => 'vendor_number',
    'vendor number' => 'vendor_number',
    'vendor no' => 'vendor_number',
    'vendor id' => 'vendor_number',
    //vendor details
    'name' => 'vendor_name',
    'street' => 'address_details',
    'city' => 'city',
    'state' => 'state',
    'zip_code' => 'zip_code',
    'address_block'=> 'address_details',
    //invoice total amount
    'total' => 'invoice_total',
    'total due' => 'invoice_total',
    //invoice date
    'date' => 'invoice_date',
    'invoice date' => 'invoice_date',
    'date:' => 'invoice_date',
    'order\ndate' => 'invoice_date',
    'inv date:' => 'invoice_date',
    //scheduled_payment
    'final due date' => 'scheduled_payment',
    'please pay invoice by' => 'scheduled_payment',
    'due date' => 'scheduled_payment',
    //PO number
    'p.o. no' => 'po_number',
    'p.o. number' => 'po_number',
    'cust. p.o.' => 'po_number',
    'po number:' => 'po_number',
    'po number' => 'po_number',
    //delivery charges
    'shipping' => 'delivery_charges',
    'shipping & handling' => 'delivery_charges',
    //Line Item details
    'total' => 'detail_amount',
    'amount' => 'detail_amount',    
    'subtotal' => 'detail_amount',
    'description' => 'item_description',
    'items' => 'item_description',
    "unit\nprice" => 'price',
    //quantity received
    'qty' => 'quantity',
    'qtyn' => 'quantity',
    'quantity received' => 'quantity',
    //delivery address
    'ship to:' => 'delivery_address',
    'ship to' => 'delivery_address',
    'location:' => 'delivery_address',
    'Remit to' => 'delivery_address',
    'tax exemption' => 'tax_payer_id',   
    't' => 'phone_number', 
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
