<?php

/**
 * @todo Write unit test.
 */

if (! function_exists('generate_guid')){

    /**
     * Creates random 36 character string.
     * @example d7ab21cd-9d3af1b8-8dec533a-2d12ffc3
     * @return string
     */
    function generate_guid(){

        // generate random 32 character string
        $random_string = bin2hex(random_bytes(16));

        // chunk into 8's
        $chunks = str_split($random_string, 8);

        // re glue with hyphen in between
        $guid = implode("-", $chunks);

        // return
        return $guid;

    }

}

if (! function_exists('convert_exception_to_json')){

    /**
     * Convert an exception to JSON.
     * Converts a standard exception into an array.
     * If the getErrors() method exists on the exception, we will get additional information.
     * Also converts any previous exception into an array.
     * @param Exception $e     The exception to be converted.
     * @param boolean   $first Is this the first iteration of this function?
     * @return array
     */
    function convert_exception_to_json(\Exception $e, bool $first = true){

        // need to convert getTrace into an associative array
        $trace = json_decode(json_encode($e->getTrace()), true);

        // create array
        $data = [
            "exception" => get_class($e),
            "message" => $e->getMessage(),
            "code" => $e->getCode(),
            "file" => $e->getFile(),
            "line" => $e->getLine(),
            "errors" => [],
            "trace" => $trace,
        ];
        // if exception has the getErrors method, then add its errors to $data
        if(method_exists($e, "getErrors")){
            $data["errors"] = $e->getErrors();
        }

        // if the exception has a previous exception, we need to add it to $data
        if($e->getPrevious() !== null){

            // convert the previous exception to an array and add to data
            $previous_exception = $e->getPrevious();
            $data["previous"] = convert_exception_to_json($previous_exception, false);

        }

        if($first == true){
            $data = json_encode($data);
        }

        // return json
        return $data;

    }

}

if (! function_exists('get_xml_from_file')){

    /**
     * Get XML from file.
     * Returns a SingleXMLElement instance that has been created from the XML within a file located within the /Libraries/XMLTemplates directory.
     * @param  string $file_name
     * @return SimpleXMLElement
     * @throws FileNotFoundException if the file can't be found.
     */
    function get_xml_from_file($file_name)
    {

        $file_path = APPPATH."/Libraries/XMLTemplates/" . $file_name . ".xml";

        if (!file_exists($file_path)){
            throw new App\Exceptions\FileNotFoundException($file_path);
        }

        $xml = simplexml_load_file($file_path);

        return $xml;

    }

}

if (! function_exists('get_now_utc_date_time')){

    /**
     * Returns formatted UTC DateTime
     * @param $format What format do you want the UTC DateTime returned?
     * @return string
     */
    function get_now_utc_date_time(string $format = "Y-m-d H:i:s"){

        // get now as UTC date time
        $date_time_utc = new \DateTime("now", new \DateTimeZone("UTC"));

        // format to Y-m-d H:i:s
        $now_utc_date_time = $date_time_utc->format($format);

        //return
        return $now_utc_date_time;

    }

}
