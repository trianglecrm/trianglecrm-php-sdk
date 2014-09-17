Triangle CRM PHP SDK
===================

The Triangle CRM PHP SDK makes it easy to integrate your marketing sites with Triangle CRM.  Easily create prospects, process order, and utilize Triangle's helper methods to easily (and correctly) integrate without worrying about properly formatting API requests and responses.

#Getting started
1. Download and add the TriangleCRM folder into your project.

2. Include php file TriangleCRM/TriangleAPI.php inside the file you will be calling the TriangleCRM API from.

3. Update the config file at TriangleCRM/config.ini Settings section 
```
[Settings]
USERNAME = ‘YOUR_API_NAME’
PASSWORD = ‘YOUR_API_PASSWORD’
DOMAIN = 'YOUR_TRIANGLE_CRM_DOMAIN'

```

#Creating your first API Call

Class TriangleAPI inherits from PHP SoapClient class and allows calling your API for seamless CRM integration. 

For example :

```php

    include (‘/TriangleCRM/TriangleAPI.php’);
    use TriangleCRM\TriangleAPI as api;

    $api = new api();

    $params = array(
            'creditCard'=>'4124241111111111',
            'productID'=>1
         );
        
    $result = $api->IsCreditCardDupe($params);

```

Lets review line by line, we begin by including the TriangleCRM.php class.

```php
    include (‘/TriangleCRM/TriangleAPI.php’);
```
Than we apply an alias but this is not required. Its simply to allow us to refer the class without having to worry about using the namespace. 

```php
    use TriangleCRM\TriangleAPI as api;

```

Next we create an instance of our api class so we can make calls to your CRM instance. 
In our example we are using an alias so the class name is actually api instead of TriangleCRM\TriangleAPI without an alias applied.

```php
    $api = new api();//alias

    $api = new TriangleCRM\TriangleAPI(); //without
```
The next three lines define the parameters we will be passing into the soap request.
Each method call takes a number of required and optional parameters to complete the soap request.

```php
  $params = array(
               'creditCard'=>'4124241111111111',
                'productID'=>1
            );
```

The last line is the actually soap request to your CRM instance. 
In our example we are calling IsCreditCardDupe with the parameters contained inside the $params array.

```php
    $result = $api->IsCreditCardDupe($params);
```

Each response from the api is returned inside an PHP Object with three properties (State, Info, and Result ).

><b>State</b> defines the status of the request operation to the server. 
The possible values are Success or Error.

><b>Info</b> is a string message giving more information on the api operation. 
	
><b>Result</b> is the actual payload of data returned from the request.


```php	
    print( $result->State);
    print( $result->Info);
    print( $result->Result);
```

#Typical Flow

This section will outline a typical transaction flow with the corresponding API calls.

Campaign funnels can range wildly with many variances.
For this reason, API method calls can be combined to accomplish different flows.
Often, the first call will be CreateProspect to create the account registration and billing plan in the CRM.

The CreateProspect method returns the ```prospectID``` value which is the unique identifier of that prospect in the CRM.

>*Note:* This ```prospectID``` value will be used for most proceeding API calls to track the customer in the CRM.

 
Most campaigns funnels work in similar manners.

1. Prospect visits a lander or pre-lander and fills out a sign-up form.
2. Visitor provides billing information which is submitted to process the transaction.
3. Additional offer can now be shown or confirmation page.    

The corresponding API calls to accomplish the flow list above.

-Typical API method call flow
> CreateProspect -\> CreateSubscription -\> Charge

The first call is the ```CreateProspect``` to create the account in the CRM.
This method returns the prospectID created in the CRM.


-Example ```CreateProspect```

```php

        $a = new TriangleCRM\TriangleAPI();
        
        $params = array(
                'productTypeID' => 3,   // ID to associate with project in CRM
                'campaignID' => 122, // ID to campaign in CRM
                'firstName' => 'john',
                'lastName' => 'test',
                'address1' => '124 test lane',
                'address2' => '',
                'city' => 'testland',
                'state' => 'CA',
                'zip' => '08861',
                'country' => 'US',
                'phone' => '',
                'email' => 'test@Qtest.com',
                'ip' => '$_SERVER['REMOTE_ADDR'],
                'affiliate' => 'TEST_AFF',
                'subAffiliate' => '',
                'internalID' => '',
                'customField1' => '',
                'customField2' => '',
                'customField3' => '',
                'customField4' => '',
                'customField5' => ''
            );
        
        $result = $a->CreateProspect($params);
        $prospectID = $result->State === "Success" ? $result->Result->ProspectID: NULL;


```

The next call is ```CreateSubscription``` which will set up the recurring plan and process the transaction based on the project settings in the CRM.

-Example ```CreateSubscription```

```php

        $a = new TriangleCRM\TriangleAPI();
        
         $params = array(
                'planID'=>5,//ID of subscription plan to put customer on. 
                'trialPackageID'=>1, //ID trial billing plan in the CRM
                'chargeForTrial'=>TRUE,//Boolean to determine
                'campaignID' => 2,//Campaign ID for campaign set up in CRM
                'firstName' => 'test',
                'lastName' => 'user',
                'address1' => '124 test lane',
                'address2' => '',
                'city' => 'test',
                'state' => 'city',
                'zip' => '08888',
                'country' => 'US',
                'phone' => '',
                'email' => '',
                'ip' => $_SERVER['REMOTE_ADDR'],
                'sendConfirmationEmail'=>FALSE,//boolean to determine if email should be sent on success
                'prospectID' => 58, //prospectID of CreateProspect call
                'description' => '',
                'paymentType'=>2,//Interger value that represents the card type/company, 1 = AMEX, 2 = Visa, 3 = MasterCard, 4 = Discover, All other values are not allowed
                'creditCard'=>'4141111111111111',
                'cvv'=>'111',
                'expMonth'=>1,
                'expYear'=>2015,
                'affiliate' => '',
                'subAffiliate' => '',
                'internalID' => '',
                'customField1' => '',
                'customField2' => '',
                'customField3' => '',
                'customField4' => '',
                'customField5' => ''
            );
        
         $result = $a->CreateSubscription($params);
         print($result->Info);

```

The final call is the ```Charge``` method which will process an Upsell. 
This is an optional step that allows for an additional offer to be sold.

-Example ```Charge```

```php

        $a = new TriangleCRM\TriangleAPI();
        
          $params = array(
                'sendConfirmationEmail'=>FALSE,//boolean to determine if email should be sent on success
                'amount'=>1.00,//amount to charge
                'shipping'=>4.00,//shipping amount to charge
                'paymentType'=>2,//Interger value that represents the card type/company, 1 = AMEX, 2 = Visa, 3 = MasterCard, 4 = Discover, All other values are not allowed
                'creditCard'=>'4144111111111111',
                'cvv'=>'1111',
                'expMonth'=>1,
                'expYear'=>2015,
                'productID'=>1,  // ID for Project in CRM
                'campaignID' => 2,
                'firstName' => 'unit-test',
                'lastName' => 'local',
                'address1' => '124 test lane',
                'address2' => '',
                'city' => 'test',
                'state' => 'city',
                'zip' => '08888',
                'country' => 'US',
                'phone' => '',
                'email' => '',
                'ip' => $_SERVER['REMOTE_ADDR'],
                'prospectID' => 58, //prospectID of CreateProspect call
                'affiliate' => 'TEST_AFF',
                'subAffiliate' => '',
                'internalID' => '',
                'customField1' => '',
                'customField2' => '',
                'customField3' => '',
                'customField4' => '',
                'customField5' => ''
            );
        
         $result = $a->Charge($params);
         print($result->Info);

```

