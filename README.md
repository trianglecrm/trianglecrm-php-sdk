trianglecrm-php-sdk
===================

#Getting started

1. Download and add the TriangleCRM folder into your project.

2.Update the config file at TriangleCRM/config.ini Settings section 
		
>Values: 
>[Settings]

```
USERNAME = ‘YOUR_API_NAME’
PASSWORD = ‘YOUR_API_PASSWORD’
DOMAIN = 'YOUR_TRIANGLE_CRM_DOMAIN'
```
3.  Include php file TriangleCRM/TriangleAPI.php inside the file you will be calling the TriangleCRM API 
--EX. ```include(‘/TriangleCRM/TriangleAPI.php’);``` or	```include_once(‘/TriangleCRM/TriangleAPI.php’);```


#Creating your first API Call-

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
    print ( $result->Info);
    print( $result->Result);
```

