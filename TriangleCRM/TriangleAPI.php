<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace TriangleCRM;

/**
 * Triangle CRM PHP soapclient for intergrating to client CRM instance<br/>
 * Allows for seamless triangle CRM api integration<br/>  
 * 
 *Getting started CheckList<br/>
 * 
 * 1.Download and add the TriangleCRM folder into your project.<br/><br/>
 * 
 * 2.Update the config file at TriangleCRM/config.ini <br/>
 * Settings section <br/>
 * Values: [Settings]<br/>
 * USERNAME = ‘YOUR_API_NAME’<br/>
 * PASSWORD = ‘YOUR_API_PASSWORD’<br/>
 * DOMAIN = 'YOUR_TRIANGLE_CRM_DOMAIN'<br/><br/>
 * 
 * 3.Include php file TriangleCRM/TriangleAPI.php <br/>
 *  Inside the file you will be calling the TriangleCRM API <br>
 * <br/>
 * EX.<br/>
 * include(‘/TriangleCRM/TriangleAPI.php’);<br/>
 * or<br/>
 * include_once(‘/TriangleCRM/TriangleAPI.php’);<br/>
 * 
 * @author egalarza
 */
class TriangleAPI extends \SoapClient {
    
    /**
     * Options parsed and returned from Config.ini file<br/>
     * Sections are defined in Associated Arrays <br/> 
     * @var array 
     */
    public $configOptions;
    
    /**
     *Reusable sections inside config.ini file
     * 
     * @var array
     */
    private $billingInfo, $ccInfo, $credInfo;

    /**
     * Contructor for class TriangleAPI <br/>
     * Method will gathers config options from config.ini
     * and sets user options from config settings section.<br/>
     * 
     * 
     * @throws Exception
     */
    public function __construct() {
        $this->configOptions = parse_ini_file("config.ini", true);
        if(empty($this->configOptions)){
            throw new Exception('Config File Did not load properly.');
        }
        
        parent::__construct($this->GetWSDLPath(), array('trace' => 1));

        $this->billingInfo = $this->configOptions['Billing'];
        $this->ccInfo = $this->configOptions['CC'];
        
        if(!empty($this->configOptions['Settings']['USERNAME']) 
            && !empty($this->configOptions['Settings']['PASSWORD'])){
            
             $this->credInfo = array(
                'username' => $this->configOptions['Settings']['USERNAME'],
                'password' => $this->configOptions['Settings']['PASSWORD']
            );
          }else{
             throw new Exception('API USERNAME AND PASSWORD IN Config.ini needs to be updated.');
          }
    }

    /**
     * (PHP 5 &gt;= 5.0.1)<br/>
     * Performs a SOAP request
     * @link http://php.net/manual/en/soapclient.dorequest.php
     * @param string $request <p>
     * The XML SOAP request.
     * </p>
     * @param string $location <p>
     * The URL to request.
     * </p>
     * @param string $action <p>
     * The SOAP action.
     * </p>
     * @param int $version <p>
     * The SOAP version.
     * </p>
     * @param int $one_way [optional] <p>
     * If one_way is set to 1, this method returns nothing.
     * Use this where a response is not expected.
     * </p>
     * @return string The XML SOAP response.
     */
    function __doRequest($request, $location, $action, $version) {
        try {
            $result = parent::__doRequest($request, $location, $action, $version);
        } catch (SoapFault $sf) {
            //this code was not reached    
            $exception = $sf;
        } catch (Exception $e) {
            //nor was this code reached either 
            $exception = $e;
        }
        if ((isset($this->__soap_fault)) && ($this->__soap_fault != null)) {
            //this is where the exception from __doRequest is stored 
            $exception = $this->__soap_fault;
            var_dump($exception);
        }

        $this->__last_request = $request;
        return $result;
    }

    /**
     * Returns Sections of Config.ini file in Array Format<br/>
     * 
     * @example <p>
     * $api = new TriangleCRM\TriangleAPI();
     * <br/>
     * $billingModel = $api->GetModel('Billing');
     * </p>
     * 
     * @param string <b>$name</b>: name of section inside config.ini<br/>
     * 
     * @return array : array(State=>string,Info=>string, Result=>mixedType)
     */
    public function GetModel($name) {
        return empty($this->configOptions[$name]) ?
                array('State' => 'Error', 'Info' => 'Not FOUND', 'Result' => 'NOT FOUND') :
                array('State' => 'Success', 'Info' => 'FOUND ' . $name, 'Result' => $this->configOptions[$name]);
    }

    /**
     * Creates new Prospect inside your CRM instance.
     * This function will create a customer "prospect" and return a prospect ID
     *  that will be used in various other function calls. <br />
     * CreateProspect is the first call that is made during the steps of a campaign funnel,<br/>
     *  and this call is made usually on a landing page. <br/><br/>
     * <p><b>Note:</b>If you send campaignID as an additional parameter the call is mapped to CreateProspectEx().</p>
     * 
     * <p>The CreateProspect function receives shipping information along<br/>
     *  with a ProductTypeID -- an integer that references your product group identifier in the CRM.</p>
     * <p>In order to find your ProductTypeID,  you can do the following:<br/>
     * login to your CRM instance (yourcompany).trianglecrm.com navigate to<br/>
     *  Marketing > Products & Billing > Product Group Manager<br/>
     * click your Product Group name in the left tree-list on the right pane,<br/>
     * you should see your Product Group Name along with a Product Type ID at the very top</p>
     * 
     * 
     * @param array <p>$params <br/>
     * 
     * string <b>firstName</b> : first name of new prospect <br/>
     * string <b>lastName</b> : last name of new prospect<br/>
     * string <b>address1</b> : street address of new prospect<br/>
     * string <b>address2</b> : additional address info of new prospect<br/>
     * string <b>city</b> : city of new prospect<br/>
     * string<b> state</b> : State short abbreviation, usually 2 uppercase letters (e.g. TX, CA, IA, …)
     *  of new prospect<br/>
     * 
     * string <b>zip</b> : postal code of new prospect <br/>
     * string <b>country</b> : Country short abbreviation, usually 2 uppercase letters (e.g. US, CA)
     *  of new prospect<br/>
     * 
     * string <b>phone></b> : phone number of prospect without any special characters <br/>
     * string <b>email</b> : email of new prospect<br/>
     * int <b>productTypeID</b> : Project ID in the CRM<br/>
     * 
     * int <b>campaignID</b> : Campaign ID for product <br/>
     *      (Note If Campaign ID is passed the actually Soap Call 
     *      is mapped to CreateProspectEx vs CreateProspect)<br/>
     * 
     * string <b>ip</b> : ip address of remote<br/>
     * string <b>affiliate</b> : affiliate tracking code<br/>
     * string <b>subAffiliate</b> : sub affiliate tracking code<br/>
     * int <b>internalID</b> : customer ID in CRM to charge or credit<br/>
     * string <b>customField1</b> : custom fields passed into CRM<br/>
     * string <b>customField2</b> : custom fields passed into CRM<br/>
     * string <b>customField3</b> : custom fields passed into CRM<br/>
     * string <b>customField4</b> : custom fields passed into CRM<br/>
     * string <b>customField5</b> : custom fields passed into CRM<br/>
     * <p/>
     * 
     * @example 
     * 
     * $api = new TriangleCRM\TriangleAPI();
     * <br/>
     *    $params = array(<br/>
     * 
     *&nbsp; &nbsp; &nbsp; &nbsp;'productTypeID' => {ID_OF_PRODUCT_IN_CRM},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'campaignID' => {IF NOT EMPTY CreateProspectEX is called},<br/> 
     *&nbsp; &nbsp; &nbsp; &nbsp;'firstName' => 'unit-test',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'lastName' => 'local',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'address1' => '124 test lane',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'address2' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'city' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'state' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'zip' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'country' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'phone' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'email' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'ip' => '127.0.0.1',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'affiliate' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'subAffiliate' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'internalID' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField1' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField2' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField3' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField4' => '',<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField5' => ''<br/>
     * 
     *       );
     * <br/>
     * 
     * 
     * $response = $api->CreateProspect($params);<br/>
     * echo $response->State;<br/>
     * echo $response->Result->ProspectID;<br/>
     * 
     * @return object : (string)$response->State, (string)$response->Info, (mixtype)$response->Result
     */
    public function CreateProspect($params) {
        $result = NULL;
        $params = array_merge($this->credInfo, $this->billingInfo, (array) $params);
        $isValid = $this->Validate($params);

        if (empty($isValid)) {

            $methodCall = (empty($params['campaignID'])) ? __FUNCTION__ : __FUNCTION__ . "Ex";
            $returnType = (empty($params['campaignID'])) ? __FUNCTION__ . "Result" : __FUNCTION__ . "ExResult";
            $obj = parent::__soapCall($methodCall, array($params));
            $result = $this->ParseResponse($obj->$returnType);
        } else {

            $result = (object) array('State' => 'Error', 'Info' => $isValid, 'Result' => $isValid);
        }

        return $result;
    }

    /**
     * Updates the shipping profile for a prospect or customer inside the CRM.
     * 
     * @param array $params
     * 
     * string <b>$firstName</b> : first name of new prospect<br/>
     * string <b>lastName</b> : last name of new prospect<br/>
     * string <b>address1</b> : street address of new prospect<br/>
     * string <b>address2</b> : additional address info of new prospect<br/>
     * string <b>city</b> : city of new prospect<br/>
     * string <b>state</b> : State short abbreviation, usually 2 uppercase letters (e.g. TX, CA, IA, …)
     *  of new prospect<br/>
     * 
     * string <b>zip</b> : postal code of new prospect <br/>
     * string <b>country</b> : Country short abbreviation, usually 2 uppercase letters (e.g. US, CA)
     *  of new prospect<br/>
     * 
     * string <b>phone</b> : phone number of prospect without any special characters <br/>
     * string <b>email</b> : email of new prospect<br/>
     * int <b>prospectID</b> : Unique int value to identify prospects inside CRM, returned from the 
     *      CreateProspect method<br/>
     * 
     * int <b>internalID</b> = customer ID in CRM to charge or credit<br/>
     * 
     * @example 
     * 
     * $api = new TriangleCRM\TriangleAPI();
     * <br/>
     *    $params = array(<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;''prospectID' => {ID_OF_PROSPECT_OR_CUSTOMER},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'firstName' => {FIRST NAME OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'lastName' => {LAST NAME OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'address1' => {STREET ADDRESS OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'address2' => {2 STREET ADDRESS OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'city' => {City OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'state' => {STATE OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'zip' => {ZIP OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'country' => {COUNTRY OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'phone' => {TELEPHONE OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'email' => {EMAIL OF PROSPECT},<br/>
     * 
     *       );
     * <br/>
     * 
     * 
     * $response = $api->UpdateShippingInfo($params);<br/>
     * echo $response->State;<br/>
     * echo "New Prospect ID : ".$response->Result->ProspectID;<br/>
     * 
     * @return object : (string)$response->State, (string)$response->Info, (mixtype)$response->Result
     */
    public function UpdateShippingInfo($params) {
        $result = NULL;
        $params = array_merge($this->credInfo, $this->billingInfo, (array) $params);
        $isValid = $this->Validate($params);

        if (empty($isValid)) {

            $returnType = $this->GetResult(__FUNCTION__);
            $obj = parent::__soapCall(__FUNCTION__, array($params));
            $result = $this->ParseResponse($obj->$returnType);
        } else {

            $result = (object) array('State' => 'Error', 'Info' => $isValid, 'Result' => $isValid);
        }

        return $result;
    }

    /**
     * Checks if customer with same credit card exists in the CRM.<br/>  
     * 
     * <p>
     *  Applies only to trials within the project or product group. <br/>
     *  "Credit Card Dupe Verification" needs to be enabled in the CRM for this function to work.<br/>
     *  <b>Note</b>: Use IsCreditCardUsed() for charge types like authorization, single sales and upsells.<br/>
     * </p>
     * 
     * @param array $params
     * 
     * int <b>creditCard</b> = Credit Card number to valid if has has 
     *  been already used with another account<br/>
     * 
     * int <b>productID</b> =  Project ID in the CRM to check if the 
     *  credit card number has been used prior <br/>
     * 
     * @example 
     * 
     *    $params = array(<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp; 'creditCard'=>{CREDIT_CARD_NUMBER},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'productID' => {PRODUCT_ID_IN_CRM},<br/>
     * 
     *       );
     * <br/>
     * 
     * $response = $api->IsCreditCardDupe($params);<br/>
     * echo $response->State;<br/>
     * if($response->Result){<br/>
     *      echo "Response info : ".$response->Info;<br/>
     * }
     * <br/>
     * 
     * @return object : (string)$response->State, (string)$response->Info, (mixtype)$response->Result
     */
    public function IsCreditCardDupe($params) {
        $result = NULL;
        $isValid = $this->Validate($params);

        if (empty($isValid)) {

            $returnType = $this->GetResult(__FUNCTION__);
            $obj = parent::__soapCall(__FUNCTION__, array((array) $params));
            $result = $this->ParseResponse($obj->$returnType, true);
        } else {

            $result = (object) array('State' => 'Error', 'Info' => $isValid, 'Result' => $isValid);
        }
        return $result;
    }

    /**
     * Fires pixel code set up under affiliate in the CRM.<br/>
     * Returns the pixel code that can be placed on the upsell<br/>
     * or confirmation page to register a sale.<br>
     * 
     * @param array $params<br>
     * string <b>affiliate</b> : affiliate tracking identifier set inside the CRM<br>
     * int <b>pageTypeID</b> : represents pages type of campaigns <br>
     * &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; 1 = Landing<br>
     *  &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; 2 = Billing <br>
     *  &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; 3 = Upsell<br>
     *   &nbsp; &nbsp; &nbsp;&nbsp; &nbsp;   4 = Confirmation<br>
     * 
     * int <b>prospectID</b> : Unique int value to identify prospects inside CRM, returned from the 
     *      CreateProspect method<br>
     * 
     * @example 
     * 
      * $api = new TriangleCRM\TriangleAPI();
     * <br/>
     *    $params = array(<br/>
     *&nbsp; &nbsp; &nbsp;'affiliate' => {ID_OF_PROSPECT_OR_CUSTOMER},<br/>
     *&nbsp; &nbsp; &nbsp;'pageTypeID' => {Landing =1, Billing = 2, Confirmation = 4},<br/>
     *&nbsp; &nbsp; &nbsp;'prospectID' => {PROSPECT_ID_RETURNED_FROM_CREATEPROSPECT_CALL},<br/>
     * 
     *       );
     * <br/>
     * 
     * 
     * $response = $api->FireAffiliatePixel($params);<br/>
     * echo $response->State;<br/>
     * echo "Tracking Pixel has Landed : ".$response->Result<br/>
     * 
     * @return object : (string)$response->State, (string)$response->Info, (mixtype)$response->Result
     */
    public function FireAffiliatePixel($params) {
        $result = NULL;
        $params = array_merge($this->credInfo, (array) $params);
        $isValid = $this->Validate($params);

        if (empty($isValid)) {

            $returnType = $this->GetResult(__FUNCTION__);
            $obj = parent::__soapCall(__FUNCTION__, array($params));
            $result = $this->ParseResponse($obj->$returnType);
        } else {

            $result = (object) array('State' => 'Error', 'Info' => $isValid, 'Result' => $isValid);
        }

        return $result;
    }

    /**
     * Debits the cardholder's account 
     *  with amount specified - generally used for single sales and upsells<br/>
     * 
     * @param array $params
     * <br/>
     * float <b>amount</b> : amount of amount to be charge on given transaction<br/>
     * float <b>shipping</b> : If Shipping Amount is specified it will be added 
     *      to Amount and charged with same transaction.<br/>
     * 
     * int <b>productTypeID</b> : ID of Product Group to be associated with customer. <br/>
     *      Product Groups are configured in CRM. <br/>
     *      If ID of Product Group is not specified ID = 1 will be used by default.<br/>
     * 
     * int <b>productID</b> : ID of Product to be shipped if "Charge" function <br/>
     *      is completed successfully. Products are configured in CRM <br/>
     *      and can contain any number of SKU with quantity.<br/>
     *      If Product ID is not specified no shipment will be placed.<br/>
     * 
     * int <b>campaignID</b> : Campaign ID for product being sold<br/>
     * bool <b>sendConfirmationEmail</b> : Flag to determine if a confirmation email 
     * should be sent on successful orders<br/>
     * 
     * string <b>firstName</b> : first name of billing info<br/>
     * string <b>lastName</b> : last name of billing info<br/>
     * string <b>address1</b> : street address of billing info<br/>
     * string <b>address2</b> : additional address info of billing info<br/>
     * string <b>city</b> : city of billing info<br/>
     * string <b>state</b> : State short abbreviation, usually 2 uppercase letters (e.g. TX, CA, IA, …)
     *  of billing info<br/>
     * 
     * string <b>zip</b> : postal code of billing info <br/>
     * string <b>country</b> : Country short abbreviation, usually 2 uppercase letters (e.g. US, CA)
     *  of billing info<br/>
     * 
     * string <b>phone</b> : phone number of billing info without any special characters <br/>
     * string <b>email</b> : email of new billing info<br/>
     * string <b>ip</b> : ip address of remote client/customer<br/>
     * string <b>affiliate</b> : affiliate tracking code<br/>
     * string <b>subAffiliate</b> : sub affiliate tracking code<br/>
     * int <b>internalID</b> : customer ID in CRM to charge or credit<br/>
     * 
     * int <b>prospectID</b> : Unique int value to identify prospects inside CRM,<br/> 
     *   returned from the CreateProspect method. <br/>
     *   ID of customer "prospect" that was created by "CreateProspect" function. <br/>
     *   If both "internalID" and "prospectID" are specified system will perform validation <br/>
     *   if values match to the same customer: If "internalID" and "prospectID" <br/>
     *   match to different customers system will throw appropriate error. <br/>
     *   If specified "internalID" is not associated with customer in the system <br/>
     *   and "prospectID" doesn’t have "internalID" associated with it system <br/>
     *   will associate "internalID" with "prospectID". Only one of "internalID" <br/>
     *   or "prospectID" is sufficient to associate charge with existing customer.<br/>
     * 
     * int <b>paymentType</b> : interger value that represents the card type/company <br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;1 = AMEX, 2 = Visa, 3 = MasterCard, 4 = Discover<br/> 
     * 
     * int <b>creditCard</b> : Credit Card number to process the transaction under<br/>
     * int <b>cvv</b> : Credit card security code of the Credit card used to process the transaction<br/>
     * 
     * int <b>expMonth</b> : Expire date month of the Credit Card used to process the transaction
     *      1 or 2 digits month number<br/>
     * 
     * int <b>expYear</b> : Expire date year of the Credit Card used to process the transaction. 
     *      4 digits year<br/>
     * 
     * string <b>description</b> : Description of the charge and information about transaction<br/>
     * string <b>customField1</b> : custom fields passed into CRM<br/>
     * string <b>customField2</b> : custom fields passed into CRM<br/>
     * string <b>customField3</b> : custom fields passed into CRM<br/>
     * string <b>customField4</b> : custom fields passed into CRM<br/>
     * string <b>customField5</b> : custom fields passed into CRM<br/>
     * 
     * @example 
     * 
     * $api = new TriangleCRM\TriangleAPI();
     * <br/>
     *    $params = array(<br/>
     * 
     *&nbsp; &nbsp; &nbsp;'prospectID' => {PROSPECT_ID_RETURNED_FROM_CREATEPROSPECT_CALL},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'sendConfirmationEmail' => {TRUE OR FALSE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'amount' => {AMOUNT TO CHARGE},<br/> 
     *&nbsp; &nbsp; &nbsp; &nbsp;'paymentType' => {1=AMEX,2=Visa,3=MasterCard,4=Discover},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'creditCard' => {CREDIT CARD NUMBER TO CHARGE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'cvv' => {CREDIT CARD SECURITY CODE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'expMonth' => {1 OR 2 DIGITS MONTH NUMBER},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'expYear' => {4 DIGITS YEAR},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'productID' => {PRODUCT ID CHARGE FOR},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'campaignID' => {CAMPAIGN ID},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'firstName' => {FIRST NAME OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'lastName' => {LAST NAME OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'address1' => {STREET ADDRESS OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'address2' => {2 STREET ADDRESS OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'city' => {City OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'state' => {STATE OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'zip' => {ZIP OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'country' => {COUNTRY OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'phone' => {TELEPHONE OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'email' => {EMAIL OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'ip' => {REMOTE IP OF CLIENT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'affiliate' => {AFFILIATE CODE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'subAffiliate' => {SUB AFFILIATE CODE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'internalID' =>{CUSTOMER ID TO CHARGE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField1' => {CUSTOM FIELD 1},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField2' => {CUSTOM FIELD 2},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField3' => {CUSTOM FIELD 3},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField4' => {CUSTOM FIELD 4},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField5' => {CUSTOM FIELD 5}<br/>
     * 
     *       );
     * <br/>
     * 
     * 
     * $response = $api->Charge($params);<br/>
     * echo $response->State;<br/>
     * echo $response->Result;<br/> 
     * 
     * @return object : (string)$response->State, (string)$response->Info, (mixtype)$response->Result
     */
    public function Charge($params) {
        $result = NULL;
        $params = array_merge($this->credInfo, $this->billingInfo, $this->ccInfo, (array) $params);
        $isValid = $this->Validate($params);

        if (empty($isValid)) {

            $returnType = $this->GetResult(__FUNCTION__);
            $obj = parent::__soapCall(__FUNCTION__, array($params));
            $result = $this->ParseResponse($obj->$returnType);
        } else {

            $result = (object) array('State' => 'Error', 'Info' => $isValid, 'Result' => $isValid);
        }

        return $result;
    }

    /**
     *  Runs a charge for the specified product list. <br/>
     *  The array should contain Associated Array values 
     *  representing Product Code Items. <br/><br/>
     * The structure of productList <br/>
     * &nbsp; &nbsp;$productList = array(<br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;'ProductDesc'=>array(<br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;'ProductID'=>1,<br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;'Amount'=>2.0,<br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;'Upsell'=>FAlSE<br/>
     * ));
     * <br/>
     * 
     * @param array $params<br/>
     * 
     * int <b>productTypeID</b> : ID of Product Group to be associated with customer. <br/>
     *      Product Groups are configured in CRM. <br/>
     *      If ID of Product Group is not specified ID = 1 will be used by default.<br/>
     * 
     * array <b>productList</b> : List of IDs of Products to be shipped if "ChargeSales" operation 
     *      is completed successfully.<br> Products are configured in CRM 
     *      and can contain any number of SKU with quantity.<br/>
     *      If Product ID is not specified no shipment will be placed.<br/><br/>
     * The structure of productList <br/>
     * &nbsp; &nbsp;$productList = array(<br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;'ProductDesc'=>array(<br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;'ProductID'=>1,<br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;'Amount'=>2.0,<br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;'Upsell'=>FAlSE<br/>
     * ));
     * <br/>
     * int <b>campaignID </b>: Campaign ID for product being sold<br/>
     * 
     * bool <b>sendConfirmationEmail </b>: Flag to determine if a confirmation email 
     * should be sent on successful orders<br/>
     * 
     * string <b>firstName </b>: first name of billing info<br/>
     * string <b>lastName</b> : last name of billing info<br/>
     * string <b>address1</b> : street address of billing info<br/>
     * string <b>address2 </b>: additional address info of billing info<br/>
     * string <b>city </b>: city of billing info<br/>
     * string <b>state </b>: State short abbreviation, usually 2 uppercase letters (e.g. TX, CA, IA, …)
     *  of billing info<br/>
     * 
     * string <b>zip </b>: postal code of billing info <br/>
     * string <b>country </b>: Country short abbreviation, usually 2 uppercase letters (e.g. US, CA)
     *  of billing info<br/>
     * 
     * string <b>phone </b>: phone number of billing info without any special characters <br/>
     * string<b> email </b>: email of new billing info<br/>
     * string <b>ip </b>: ip address of remote client/customer<br/>
     * string<b> affiliate </b>: affiliate tracking code<br/>
     * string <b>subAffiliate </b>: sub affiliate tracking code<br/>
     * int <b>internalID </b>: customer ID in CRM to charge or credit<br/>
     * 
     * int <b>prospectID </b>: Unique int value to identify prospects inside CRM, 
     *   returned from the CreateProspect method. <br/>
     *   ID of customer "prospect" that was created by "CreateProspect" function. <br/>
     *   If both "internalID" and "prospectID" are specified system will perform validation 
     *   if values match to the same customer: If "internalID" and "prospectID" <br/>
     *   match to different customers system will throw appropriate error. <br/>
     *   If specified "internalID" is not associated with customer in the system 
     *   and "prospectID" doesn’t have "internalID" associated with it system <br/>
     *   will associate "internalID" with "prospectID". Only one of "internalID" <br/>
     *   or "prospectID" is sufficient to associate charge with existing customer.<br/>
     * 
     * int <b>paymentType</b> : interger value that represents the card type/company <br/>
     *&nbsp; &nbsp; &nbsp; &nbsp; 1 = AMEX, 2 = Visa, 3 = MasterCard, 4 = Discover <br/>
     * 
     * int <b>creditCard </b>: Credit Card number to process the transaction under<br/>
     * int <b>cvv</b> : Credit card security code of the Credit card used to process the transaction<br/>
     * int <b>expMonth </b>: Expire date month of the Credit Card used to process the transaction<br/>
     *      1 or 2 digits month number<br/>
     * 
     * int <b>expYear</b> : Expire date year of the Credit Card used to process the transaction. <br/>
     *      4 digits year<br/>
     * 
     * string <b>customField1 </b>: custom fields passed into CRM<br/>
     * string <b>customField2 </b>: custom fields passed into CRM<br/>
     * string <b>customField3</b> : custom fields passed into CRM<br/>
     * string <b>customField4</b> : custom fields passed into CRM<br/>
     * string <b>customField5</b> : custom fields passed into CRM<br/>
     * 
     * @example 
     * 
     * $api = new TriangleCRM\TriangleAPI();
     * <br/>
     *    $params = array(<br/>
     * 
     *&nbsp; &nbsp; &nbsp;'productTypeID' => {ID OF PRODUCT GROUP INSIDE CRM},<br/>
     *&nbsp; &nbsp; &nbsp;'productList' => {LIST OF PRODUCT WITH (int)PRODUCTID,(float)AMOUNT,(bool)UPSEll},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'campaignID' => {CAMPAIGN ID},<br/>
     *&nbsp; &nbsp; &nbsp;'prospectID' => {PROSPECT_ID_RETURNED_FROM_CREATEPROSPECT_CALL},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'sendConfirmationEmail' => {TRUE OR FALSE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'paymentType' => {1=AMEX,2=Visa,3=MasterCard,4=Discover},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'creditCard' => {CREDIT CARD NUMBER TO CHARGE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'cvv' => {CREDIT CARD SECURITY CODE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'expMonth' => {1 OR 2 DIGITS MONTH NUMBER},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'expYear' => {4 DIGITS YEAR},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'productID' => {PRODUCT ID CHARGE FOR},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'firstName' => {FIRST NAME OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'lastName' => {LAST NAME OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'address1' => {STREET ADDRESS OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'address2' => {2 STREET ADDRESS OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'city' => {City OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'state' => {STATE OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'zip' => {ZIP OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'country' => {COUNTRY OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'phone' => {TELEPHONE OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'email' => {EMAIL OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'ip' => {REMOTE IP OF CLIENT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'affiliate' => {AFFILIATE CODE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'subAffiliate' => {SUB AFFILIATE CODE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'internalID' =>{CUSTOMER ID TO CHARGE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField1' => {CUSTOM FIELD 1},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField2' => {CUSTOM FIELD 2},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField3' => {CUSTOM FIELD 3},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField4' => {CUSTOM FIELD 4},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField5' => {CUSTOM FIELD 5}<br/>
     * 
     *       );
     * <br/>
     * 
     * 
     * $response = $api->ChargeSales($params);<br/>
     * echo $response->State;<br/>
     * echo $response->Result;<br/> 
     * 
     * @return object : (string)$response->State, (string)$response->Info, (mixtype)$response->Result
     */
    public function ChargeSales($params) {
        $result = NULL;
        $params = array_merge($this->credInfo, $this->billingInfo, $this->ccInfo, (array) $params);
        $isValid = $this->Validate($params);

        if (empty($isValid)) {

            $returnType = $this->GetResult(__FUNCTION__);
            $obj = parent::__soapCall(__FUNCTION__, array($params));
            $result = $this->ParseResponse($obj->$returnType);
        } else {

            $result = (object) array('State' => 'Error', 'Info' => $isValid, 'Result' => $isValid);
        }

        return $result;
    }

    /**
     * Runs a charge for an existing customer using existing Billing Profile<br/>
     * 
     * @param array $params
     * 
     * int <b>prospectID </b>: Unique int value to identify prospects inside CRM, 
     *   returned from the CreateProspect method. <br/>
     *   ID of customer "prospect" that was created by "CreateProspect" function. <br/>
     *   If both "internalID" and "prospectID" are specified system will perform validation <br/>
     *   if values match to the same customer: If "internalID" and "prospectID" 
     *   match to different customers system will throw appropriate error. <br/>
     *   If specified "internalID" is not associated with customer in the system 
     *   and "prospectID" doesn’t have "internalID" associated with it system <br/>
     *   will associate "internalID" with "prospectID". Only one of "internalID" <br/>
     *   or "prospectID" is sufficient to associate charge with existing customer.<br/>
     * 
     * int<b> productTypeID</b>  : ID of Product Group to be associated with customer. <br/>
     *      Product Groups are configured in CRM. <br/>
     *      If ID of Product Group is not specified ID = 1 will be used by default.<br/>
     * 
     * array <b>productList</b>  : List of IDs of Products to be shipped if "ChargeSales" operation 
     *      is completed successfully. <br/>Products are configured in CRM 
     *      and can contain any number of SKU with quantity.<br/>
     *      If Product ID is not specified no shipment will be placed.<br/><br/>
     * 
     * The structure of productList <br/>
     * &nbsp; &nbsp;$productList = array(<br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;'ProductDesc'=>array(<br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;'ProductID'=>1,<br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;'Amount'=>2.0,<br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;'Upsell'=>FAlSE<br/>
     * ));
     * <br/>
     * bool <b>sendConfirmationEmail</b> : Flag to determine if a confirmation email 
     * should be sent on successful orders<br/>
     * 
     * 
      * $api = new TriangleCRM\TriangleAPI();
     * <br/>
     *    $params = array(<br/>
     * 
     *&nbsp; &nbsp; &nbsp; &nbsp;'prospectID' => {ID OF CUSTOMER IN CRM},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'productTypeID' => {ID OF THE PRODUCT TYPE IN CRM},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'productList' =>array('ProductCodeItem'=>array('ProductID'=>1,'Amount'=>4.00,'Quantity'=>2))<br/> 
     *&nbsp; &nbsp; &nbsp; &nbsp;'sendConfirmationEmail' => {TRUE OR FALSE},<br/>
     *       );
     * <br/>
     * 
     * 
     * $response = $api->ChargeSalesExistingCustomer($params);<br/>
     * echo $response->State;<br/>
     * echo $response->Result;<br/> 
     * 
     * 
     * @return object : (string)$response->State, (string)$response->Info, (mixtype)$response->Result
     */
    public function ChargeSalesExistingCustomer($params) {
        $result = NULL;
        $params = array_merge($this->credInfo, (array) $params);
        $isValid = $this->Validate($params);

        if (empty($isValid)) {

            $returnType = $this->GetResult(__FUNCTION__);
            $obj = parent::__soapCall(__FUNCTION__, array($params));
            $result = $this->ParseResponse($obj->$returnType);
        } else {

            $result = (object) array('State' => 'Error', 'Info' => $isValid, 'Result' => $isValid);
        }

        return $result;
    }

    /**
     * Signs up customer on a recurring plan.Signs up customer on a recurring plan. <br/>
     * TrialPackageID and PlanID from CRM need to be passed to charge the customer and ship products if applicable.<br/>
     * 
     * @param array $params<br/>
     * 
     * int <b>planID</b> : ID of subscription plan to put customer on. <br/>
     *      Subscription Plans are configured in CRM 
     *      and can be used for recurring charges.<br/>
     * 
     * int <b>trialPackageID</b> : Trial package set inside the CRM<br/>
     * 
     * bool <b>chargeForTrial</b> : Boolean option to specify whether the customer 
     *      should be charged for trial price. <br/>
     *      If set to "true" function will charge customer immediately 
     *      and place shipment according to subscription settings.<br/>
     *      Trial prices and products for subscription plans are configured in CRM. <br/>
     *      Function will not put the customer on subscription plan 
     *      if "chargeForTrial" option is set to "true" 
     *      and charge is failed for trial price.<br/>
     * 
     * int <b>campaignID</b> : Campaign ID for product being sold<br/>
     * 
     * string <b>firstName</b> : first name of billing info<br/>
     * string <b>lastName</b> : last name of billing info<br/>
     * string <b>address1</b> : street address of billing info<br/>
     * string <b>address2</b> : additional address info of billing info<br/>
     * string <b>city</b> : city of billing info<br/>
     * string <b>state</b> : State short abbreviation, usually 2 uppercase letters (e.g. TX, CA, IA, …)
     *      of billing info<br/>
     * string <b>zip</b> : postal code of billing info <br/>
     * string <b>country</b> : Country short abbreviation, usually 2 uppercase letters (e.g. US, CA)
     *      of billing info<br/>
     * string <b>phone</b> : phone number of billing info without any special characters <br/>
     * 
     * string <b>email</b> : email of new billing info<br/>
     * 
     * bool <b>sendConfirmationEmail</b> : Flag to determine if a confirmation email <br/>
     * 
     * string <b>ip</b> : ip address of remote client/customer<br/>
     * 
     * string <b>affiliate</b> : affiliate tracking code<br/>
     * 
     * string <b>subAffiliate</b> : sub affiliate tracking code<br/>
     * 
     * int <b>internalID</b> : customer ID in CRM to charge or credit<br/>
     * 
     * int <b>prospectID</b> : Unique int value to identify prospects inside CRM, 
     *   returned from the CreateProspect method. <br/>
     *   ID of customer "prospect" that was created by "CreateProspect" function. <br/>
     *   If both "internalID" and "prospectID" are specified system will perform validation <br/>
     *   if values match to the same customer: If "internalID" and "prospectID" <br/>
     *   match to different customers system will throw appropriate error. <br/>
     *   If specified "internalID" is not associated with customer in the system 
     *   and "prospectID" doesn’t have "internalID" associated with it system <br/>
     *   will associate "internalID" with "prospectID". Only one of "internalID" <br/>
     *   or "prospectID" is sufficient to associate charge with existing customer.<br/>
     * 
     * int <b>paymentType</b> : interger value that represents the card type/company <br/>
     * &nbsp; &nbsp; &nbsp; &nbsp;1 = AMEX, 2 = Visa, 3 = MasterCard, 4 = Discover<br/> 
     * 
     * int <b>creditCard</b> : Credit Card number to process the transaction under<br/>
     * int <b>cvv</b> : Credit card security code of the Credit card used to process the transaction<br/>
     * int <b>expMonth </b>: Expire date month of the Credit Card used to process the transaction
     *      1 or 2 digits month number<br/>
     * 
     * int <b>expYear</b> : Expire date year of the Credit Card used to process the transaction. 
     *      4 digits year<br/>
     * 
     * @example 
     *      * $api = new TriangleCRM\TriangleAPI();
     * <br/>
     *    $params = array(<br/>
     * 
     *&nbsp; &nbsp; &nbsp; &nbsp;'planID' => {ID OF Subscription Plan IN CRM},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'trialPackageID' => {ID OF Trial Plan IN CRM},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'chargeForTrial' => {TRUE OR FALSE IF Charge IS A Trial},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'campaignID' => {CAMPAIGN ID},<br/>     
     *&nbsp; &nbsp; &nbsp; &nbsp;'firstName' => {FIRST NAME OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'lastName' => {LAST NAME OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'address1' => {STREET ADDRESS OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'address2' => {2 STREET ADDRESS OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'city' => {City OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'state' => {STATE OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'zip' => {ZIP OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'country' => {COUNTRY OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'phone' => {TELEPHONE OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'email' => {EMAIL OF PROSPECT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'ip' => {REMOTE IP OF CLIENT},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'sendConfirmationEmail' => {TRUE OR FALSE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'prospectID' => {ID OF CUSTOMER IN CRM},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'description' => {DESCRIPTION OF Charge to APPEAR in CRM },<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'affiliate' => {AFFILIATE CODE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'subAffiliate' => {SUB AFFILIATE CODE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'internalID' =>{CUSTOMER ID TO CHARGE},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField1' => {CUSTOM FIELD 1},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField2' => {CUSTOM FIELD 2},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField3' => {CUSTOM FIELD 3},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField4' => {CUSTOM FIELD 4},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'customField5' => {CUSTOM FIELD 5}<br/>
     * 
     *       );
     * <br/>
     * 
     * 
     * $response = $api->CreateSubscription($params);<br/>
     * echo $response->State;<br/>
     * echo $response->Result;<br/> 
     *  
     * @return object : (string)$response->State, (string)$response->Info, (mixtype)$response->Result
     * 
     */
    public function CreateSubscription($params) {
        $result = NULL;
        $params = array_merge($this->credInfo, $this->billingInfo, $this->ccInfo, (array) $params);
        $isValid = $this->Validate($params);

        if (empty($isValid)) {

            $returnType = $this->GetResult(__FUNCTION__);
            $obj = parent::__soapCall(__FUNCTION__, array($params));
            $result = $this->ParseResponse($obj->$returnType);
        } else {

            $result = (object) array('State' => 'Error', 'Info' => $isValid, 'Result' => $isValid);
        }

        return $result;
    }

    /**
     * Creates a new subscription plan an existing customer in the CRM.<br/>
     * 
     * @param array $params<br/>
     * 
     * int <b>prospectID</b> : Unique int value to identify prospects inside CRM, 
     *   returned from the CreateProspect method. <br/>
     *   ID of customer "prospect" that was created by "CreateProspect" function. <br/>
     *   If both "internalID" and "prospectID" are specified system will perform validation <br/>
     *   if values match to the same customer: If "internalID" and "prospectID" <br/>
     *   match to different customers system will throw appropriate error. <br/>
     *   If specified "internalID" is not associated with customer in the system <br/>
     *   and "prospectID" doesn’t have "internalID" associated with it system <br/>
     *   will associate "internalID" with "prospectID". Only one of "internalID" <br/>
     *   or "prospectID" is sufficient to associate charge with existing customer.<br/>
     * 
     * int <b>billingID</b> : Unique int value to identify billing plans to accounts. <br/>
     *      This value can be found on the Customer Profile page in CRM.<br/>
     * 
     * int <b>planID</b> : ID of subscription plan to put customer on.<br/>
     *      Subscription Plans are configured in CRM and 
     *      can be used for recurring charges.<br/>
     * 
     * int <b>trialPackageID</b> : Unique int value of the trial package being ordered.<br/>
     * 
     * bool <b>chargeForTrial</b> : Boolean option to specify whether the customer 
     *      should be charged for trial price.<br/> If set to "true" function 
     *      will charge customer immediately and place shipment according 
     *      to subscription settings.<br/>Trial prices and products for subscription 
     *      plans are configured in CRM.<br/> Function will not put the customer 
     *      on subscription plan if "chargeForTrial" option is set <br/>
     *      to "true" and charge is failed for trial price.<br/>
     * 
     * bool <b>sendConfirmationEmail</b> : Flag to determine if a confirmation email 
     *      should be sent on successful order<br/>
     * 
     * @example 
     * 
      * $api = new TriangleCRM\TriangleAPI();
     * <br/>
     *    $params = array(<br/>
     * 
     *&nbsp; &nbsp; &nbsp; &nbsp;'prospectID' => {PROSPECT OR CUSTOMER ID IN CRM},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'billingID' => {BILLING PROFILE IN CRM TO CHARGE},<br/> 
     *&nbsp; &nbsp; &nbsp; &nbsp;'planID' => {PLAN UNIQUE ID IN THE CRM},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'trialPackageID' => {TRIAL PLAN UNIQUE ID IN THE CRM},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'chargeForTrial' => {TRUE OR FALSE IF CHARGE SHOULD BE RAN AS A TRIAL},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'sendConfirmationEmail' => {TRUE OR FALSE IF EMAIL SHOULD BE SENT ON SUCCESS}<br/>
     * 
     *       );
     * <br/>
     * $response = $api->CreateNewSubscriptionExistingCustomer($params);<br/>
     * echo $response->State;<br/>
     * echo $response->Result;<br/> 
     * 
     * @return object : (string)$response->State, (string)$response->Info, (mixtype)$response->Result
     */
    public function CreateNewSubscriptionExistingCustomer($params) {
        $result = NULL;
        $params = array_merge($this->credInfo, (array) $params);
        $isValid = $this->Validate($params);

        if (empty($isValid)) {

            $returnType = $this->GetResult(__FUNCTION__);
            $obj = parent::__soapCall(__FUNCTION__, array($params));
            $result = $this->ParseResponse($obj->$returnType);
        } else {

            $result = (object) array('State' => 'Error', 'Info' => $isValid, 'Result' => $isValid);
        }

        return $result;
    }

    /**
     * Returns customer's shipping and billing information and charge history for a prospect.<br/>
     * For example, this function can be used when you wish to call prospects via customer service or dialer.<br/>
     * 
     * @param array $params<br/>
     * 
     * string <b>phoneNumber</b> : Telephone number of customer<br/>
     * 
     * int <b>prospectID</b> : Unique int value to identify prospects inside CRM, 
     *   returned from the CreateProspect method. <br/>
     *   ID of customer "prospect" that was created by "CreateProspect" function. <br/>
     *   If both "internalID" and "prospectID" are specified system will perform validation <br/>
     *   if values match to the same customer: If "internalID" and "prospectID" <br/>
     *   match to different customers system will throw appropriate error. <br/>
     *   If specified "internalID" is not associated with customer in the system <br/>
     *   and "prospectID" doesn’t have "internalID" associated with it system <br/>
     *   will associate "internalID" with "prospectID". Only one of "internalID" <br/>
     *   or "prospectID" is sufficient to associate charge with existing customer. <br/>
     * 
     * @example
     * 
     * $api = new TriangleCRM\TriangleAPI();
     * <br/>
     *    $params = array(<br/>
     * 
     *&nbsp; &nbsp; &nbsp; &nbsp;'phoneNumber' => {TELEPHONE_CUSTOMER},<br/>
     *&nbsp; &nbsp; &nbsp; &nbsp;'prospectID' => {PROSPECT OR CUSTOMER ID IN CRM},<br/> 
     * 
     *       );
     * <br/>
     * 
     * 
     * $response = $api->GetCustomerDetail($params);<br/>
     * echo $response->State;<br/>
     * echo $response->Result;<br/> 
     * 
     * @return object : (string)$response->State, (string)$response->Info, (mixtype)$response->Result
     */
    public function GetCustomerDetail($params) {
        $result = NULL;
        $params = array_merge($this->credInfo, (array) $params);
        $isValid = $this->Validate($params);

        if (empty($isValid)) {

            $returnType = $this->GetResult(__FUNCTION__);
            $obj = parent::__soapCall(__FUNCTION__, array($params));
            $result = $this->ParseResponse($obj->$returnType);
        } else {

            $result = (object) array('State' => 'Error', 'Info' => $isValid, 'Result' => $isValid);
        }

        return $result;
    }
    /**
     * Maps Interger value to actually Card provider<br/>
     * 
     * @param string $type<br/>
     * Interger value of Credit Card Payment Company <br/>
     * 
     * @example
     * 
     * $api = new TriangleCRM\TriangleAPI();
     * $val = $api->MapPaymentTypeValue(1);
     * 
     * print $val;//American Express
     * 
     * @return string $res
     */
    protected function MapPaymentTypeValue($type){
        $res = NULL;
        switch ($type) {
                case 1:
                        $res = 'American Express';
                        break;
                case 2:
                        $res = 'Visa';
                        break;
                case 3:
                        $res = 'Mastercard';
                        break;
                case 4:
                        $res= 'Discover';
                        break;
                default:
                        $res = NULL;
        }
       return $res; 
    }

    private function GetWSDLPath() {
        return "https://" . $this->configOptions['Settings']['DOMAIN'] . ".trianglecrm.com" . $this->configOptions['Settings']['WSDL'];
    }

    private function Validate($model, $testmodel = NULL) {
        $result = NULL;
        $message = NULL;

        if (!empty($model['creditCard'])) {
            //validate CC number
            if (!$this->luhn_validate($model['creditCard'])) {
                $message = "Credit Card Number is not proper format.";
            }
            
            if(!empty($model['paymentType'])){
                //validate CVV
                if(!$this->ValidateCVVCount($model['paymentType'],$model['cvv'])){
                    $message = "CVV count is not correct.";
                }
                //validate number count
                if(!$this->ValidateCardNumberCount($model['paymentType'],$model['creditCard'])){
                    $message = "Credit Card number count is not correct.";
                }
            }
        }
        
        if(!empty($model['email'])){
            
            if(!filter_var($model['email'], FILTER_VALIDATE_EMAIL)){
                $message = "Email format is not correct.";
            }
            
        }

        return $result ? '' : $message;
    }
    
    private function ValidateCVVCount($paymentType,$cvv){
        $amexCount = 4;
        $other = 3;
        $res = FAlSE;
        
        if(!empty($paymentType) && !empty($cvv)){
            $count = strlen($cvv);
            
            if($paymentType == 1){
               $res = ($amexCount == $count)?true:false;
            }else{
                $res = ($other == $count)?true:false;
            }
        }
        
      return $res;  
    }

    private function ValidateCardNumberCount($paymentType,$cardNumber){
       $amexCount = 15;
       $other = 16;
       $res = FAlSE;
        
        if(!empty($paymentType) && !empty($cardNumber)){
            $count = strlen($cardNumber);
            
            if($paymentType == 1){
               $res = ($amexCount == $count)?true:false;
            }else{
                $res = ($other >= $count)?true:false;
            }
        }
        
      return $res;   
    }
    
    private function FormatParams($key, $params) {
        $result = NULL;
        $result = array_intersect($params, $this->configOptions[$key]);
        foreach ($this->configOptions[$key] as $k => $v) {
            if (array_key_exists($v, $params)) {
                $result[$k] = $params[$v];
            }
        }
        return $result;
    }

    private function ParseResponse($obj, $justBool = false) {
        $state = 'Error';
        $message = 'No Operation';
        $result = "";
        try {
            if (!$justBool) {
                $state = $obj->State;
                $message = (stripos($state, 'Error') !== false) ?
                        $this->ParseErrorMessage($obj->ErrorMessage) :
                        'Successfull Operation';
                $result = (stripos($state, 'Error') !== false) ? "" : $obj->ReturnValue;
            } else {
                $state = 'Success';
                $message = ($obj) ? "Result Returned True" : "Result Returned False";
                $result = $obj;
            }
        } catch (\Exception $exp) {
            $state = 'Error';
            $message = $exp;
        }

        return (object) array('State' => $state, 'Info' => $message, 'Result' => $result);
    }

    private function ParseErrorMessage($error) {
        $result = NULL;
        try {
            parse_str($error, $tmpOutput);
            $result = empty($tmpOutput['ResponseMessage']) ?
                    $error :
                    $tmpOutput['ResponseMessage'];
        } catch (Exception $e) {
            $result = $error;
        }
        return $result;
    }

    private function GetResult($fnc_name) {
        return $fnc_name . "Result";
    }

    private function luhn_validate($s) {
        if (0 == $s) {
            return(false);
        } // Don’t allow all zeros
        $sum = 0;
        $i = strlen($s); // Find the last character
        $o = $i % 2; // Shift odd/even for odd-length $s
        while ($i-- > 0) { // Iterate all digits backwards
            $sum+=$s[$i]; // Add the current digit
            // If the digit is even, add it again. Adjust for digits 10+ by subtracting 9.
            ($o == ($i % 2)) ? ($s[$i] > 4) ? ($sum+=($s[$i] - 9)) : ($sum+=$s[$i]) : false;
        }
        return (0 == ($sum % 10));
    }

}
