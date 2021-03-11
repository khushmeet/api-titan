<?php

namespace App\Models\Kerridge;

use App\Exceptions\FileNotFoundException;

/**
 * @todo Write unit test.
 */
abstract class BaseKerridgeAbstract
{

    /**
     * Capacity size of rate limit bucket.
     * Limits the amount of calls that can be made to Kerridge through this end point.
     * @var integer
     */
    public $capacity = 10;

    /**
     * Cache duration.
     * How many seconds any cache for this end point should persist for.
     * @var integer
     */
    public $cache_duration = 10;

    /**
     * URL structure.
     * This represents the url structure of the end point that was called.
     * This gets used in any error message generation to assist with understanding the error message.
     * @var string
     */
    public $url_structure = "";

    /**
     * No entities message.
     * When getting data from Kerridge, we need to check for this error message to see if the failure is due to no resources been found on Kerridge.
     * @var string
     */
    public $no_entities_message = "";

    /**
     * Template file name.
     * @var string
     */
    public $file_name = "";

    /**
     * Validation file name.
     * @var string
     */
    public $validation_file = "";


    /**
     * Get XML from file.
     * Returns a SingleXMLElement instance that has been created from the XML within a file located within the /Libraries/XMLTemplates directory.
     * @param  string $file_name
     * @return SimpleXMLElement
     * @throws FileNotFoundException if the file can't be found.
     */
    public function getXMLFromFile($file_name)
    {

        // load helper functions
        helper('helper');

        // get xml
        $xml = get_xml_from_file($file_name);

        // return xml
        return $xml;

    }

    /**
     * Maps input to a SimpleXMlElement instance.
     * Loops through provided $map and checks if $input matches an expected fields.
     * If the expected input for a match is an array, we create a new child element and map the $input array to it.
     * Otherwise we just map the $input value to the Kerridge key on the SimpleXMLElement.
     * @param  array             $map   Mapping array, should be "kerridge" => "us" format.
     * @param  array             $input Array of input provided by user.
     * @param  \SimpleXMLElement &$xml  Instance of SimpleXMlElement that we are going to add to.
     */
    public function mapper(array $map, array $input, \SimpleXMLElement &$xml)
    {
        // loop through all elements in $map
        foreach($map as $k => $v){
            // make sure that a value exists for expected field
            if(isset($input[$v])){
//print_r([$v,$k,$input[$v]]);
                // check if map key is an array...
		    if($v == "invoice_address_name"){

                         $input[$v] = htmlspecialchars($input[$v]);

                    }
    
		$array = explode(".", $k);

                // if it is an array,
                // create a new xml element and add the key and value to this
                if(count($array) > 1){

                    // add the bunch of xml elements
                    $element = $xml->addChild($array[0]);

					foreach($input[$v] as $e){

						$element->addChild($array[1], htmlspecialchars($e));

                    }

                // if it is not an array,
                // add the key and value
                }else{

                    $xml->addChild($k, $input[$v]);

                }

            }

        }
    }

    /**
     * Adds a 'count' to the data that is supplied.
     * @param  array  $data Array of data that we want counted.
     * @param  string $key  Name of the key pair in the array we want counting.
     * @return array
     */
    public function addCountToData(array &$data, string $key = '')
    {

        // create count
        $count = count($data[$key]);

        $data = array_merge([
            "count" => $count,
        ], $data);

    }

    /**
     * Adds "next_page" element to data
     * @param array  &$data     Reference to $data object to have next_page added
     * @param string $next_page Value of next page
     */
    public function addNextPageToData(array &$data, string $next_page)
    {

        $data = array_merge([
            "next_page" => $next_page,
        ], $data);

    }

    /**
     * Returns a modified SimpleXMlElement.
     * @param  array $data Any data that will be used to modify the SimpleXMlElement.
     * @return \SimpleXMLElement
     */
    abstract public function fetch(array $data);

    /**
     * Returns converted SimpleXMLElement.
     * @param  \SimpleXMLElement $response This should be the a response from Kerridge.
     * @return array
     */
    abstract public function toData(\SimpleXMLElement $response);

}
