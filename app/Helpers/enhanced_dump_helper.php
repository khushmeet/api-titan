<?php

/**
 * Functionality used to help display data to screen in a nicer format than var_dump()
 *
 * ddd() is a quicker way o call pretty_dump(), they both do pretty much the same thing
 * @todo Write unit test.
 */

if (! function_exists('ddd')){

    /**
     * Alias for pretty_dump()
     * @param variable $object     Whatever it is we want to pretty print.
     * @param boolean  $die        Do we want the php script to end after the dump.
     * @param boolean  $full_trace Do we want a full trace.
     */
    function ddd($object, bool $die = true, bool $full_trace = false){
        pretty_dump($object, $die, $full_trace, true);
    }

}

if (! function_exists('pretty_dump')){

    /**
     * Dumps variable to screen using dump().
     * This is a slight extension on the default dump() method.
     * We can optionally die after the dump.
     * We can also do a full trace back during the dump.
     * We also get a informed what line/file the dump occurred.
     * @param  variable $object     Whatever it is we want to pretty print.
     * @param  boolean $die         Do we want the php script to end after the dump.
     * @param  boolean $full_trace  Do we want a full trace.
     * @param  boolean $ddd         If this method was called via ddd().
     * @param  int $trace_line      If a numeric value is used, we will trace back that line.
     */
    function pretty_dump($object, bool $die = true, bool $full_trace = false, bool $ddd = false, $trace_line = null){

        // //take note of current buffer handlers before we clear them!
        // $ob_list_handlers = ob_list_handlers();

        // //remove the handlers
        // while (@ob_end_flush());

        $fileinfo = 'no_file_info';
        $backtrace = debug_backtrace();

        //if we want a full trace of whats happend
        if($full_trace){
            $backtrace_data_i_want = array();
            if(!empty($backtrace)){
                foreach($backtrace as $back){
                    $backtrace_data_i_want[] = $back['file'] . " - Line: " . $back['line'];
                }
            }

            if($trace_line !== null){
                dump($backtrace_data_i_want[$trace_line], $object);
            }else{
                dump($backtrace_data_i_want, $object);
            }

        //otherwise we only want the last trace data
        }else{
            $depth = $ddd == false ? 0 : 1;
            if (!empty($backtrace[$depth]) && is_array($backtrace[$depth])) {
                $fileinfo = $backtrace[$depth]['file'] . " - Line: " . $backtrace[$depth]['line'];
            }
            dump($fileinfo, $object);
        }

        //should we die?
        if($die){
            die();
        }

        // //add the handlers back in
        // foreach($ob_list_handlers as $handler){
        //  //dont try to add this handler back in, as it errors...
        //  if($handler != 'default output handler'){
        //      ob_start($handler);
        //  }
        // }

    }

}
