<?php

/**
 * @todo Write unit test.
 */

if (! function_exists('replace_key')){

    /**
     * Replace key in array.
     * @param  array  $array    Array that i want to replace keys on.
     * @param  string $original Key that i want to be replaced.
     * @param  string $new      Value i want to replace key with.
     * @return array
     */
    function replace_key(array $array, string $original, string $new){

        // duplicate original key pair to new key pair
        $array[$new] = $array[$original];

        // remove original key pair
        unset($array[$original]);

        // return array
        return $array;

    }

}

if (! function_exists('transform_keys_to_snake_case')){

    /**
     * Convert all keys of multidimensional array to snake case.
     * @param array &$array Link by reference the array that is having keys replaced.
     * @link https://stackoverflow.com/questions/1444484/how-to-convert-all-keys-in-a-multi-dimenional-array-to-snake-case
     */
    function transform_keys_to_snake_case(array &$array){

        foreach (array_keys($array) as $key){

            # Working with references here to avoid copying the value
            $value = &$array[$key];
            unset($array[$key]);

            # This is what you actually want to do with your keys:
            #  - camelCase to snake_case
            $transformedKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $key));

            # Work recursively
            if (is_array($value)) transform_keys_to_snake_case($value);

            # Store with new key
            $array[$transformedKey] = $value;

            # Do not forget to unset references!
            unset($value);

        }

    }

}

if (! function_exists('force_all_keys_into_array')){

    /**
     * Force all values in array that match supplied key to be an array.
     * @param  array  $array Search through this array for matching key.
     * @param  string $key   Key to match against.
     * @return array
     */
    function force_all_keys_into_array(array $array, string $key){

        // for each
        foreach($array as $k => $v){

            // if an array
            if(is_array($v)){

                // if key matches our target
                if($k == $key && !is_numeric($k)){

                    // if the value doesn't have a first element, and is an array
                    // we need to push the value into a deeper array
                    if(!isset($v[0]) && is_array($v)){
                        $array[$k] = [];
                        $array[$k][] = $v;
                    }

                }else{

                    // check one level deeper
                    $array[$k] = force_all_keys_into_array($array[$k], $key);

                }

            }

        }

        // return the array
        return $array;

    }

}

if (! function_exists('replace_array_key_if_value_is_array')){

    /**
     * Replace key of array if its value is an array.
     * Loops through array looking for any key that matches supplied original.
     * If a match is found, and the value of that key is an array, we overwrite the key with supplied new.
     * @param  array  $array    Array to be looped through.
     * @param  string $original Attempt to match a key on this value.
     * @param  string $new      Overwrite key with this value.
     * @return array
     */
    function replace_array_key_if_value_is_array(array $array, string $original, string $new){

        // for each
        foreach ($array as $k => $v){

            // if an array
            if(is_array($v)){

                // if key matches our target
                if($k == $original && !is_numeric($k)){

                    // create duplicate of value and assign to key, and remove old key pair
                    $array[$new] = $v;
                    unset($array[$k]);

                }else{

                    // check one level deeper
                    $array[$k] = replace_array_key_if_value_is_array($array[$k], $original, $new);

                }

            }

        }

        // return the modified array
        return $array;

    }

}

if (! function_exists('replace_key_value_with_child_key_value')){

    /**
     * Replace array value, with its own child.
     * Loops through supplied array looking for a match on supplied key.
     * When match is found, loop through that the matched array looking for a match on supplied child_key.
     * When match is found, overwrite original key value with the child_key's value.
     * @param  array  $array     Array to loop through.
     * @param  string $key       Attempt to match this value to a key in provided array.
     * @param  string $child_key Attempt to match this value to a key in a child of the provided array.
     * @return array
     */
    function replace_key_value_with_child_key_value(array $array, string $key, string $child_key){

        // for each
        foreach ($array as $k => $v){

            // if an array
            if(is_array($v)){

                // if array key matches key we are looking for
                if($k == $key && !is_numeric($k)){

                    // loop through each sub array
                    foreach($v as $k_ => $v_){

                        // if sub array key matches the child key we are looking for
                        if($k_ == $child_key && !is_numeric($k_)){

                            // replace array key pair with child value
                            $array[$k] = $v_;

                        }

                    }

                }else{

                    // check one level deeper
                    $array[$k] = replace_key_value_with_child_key_value($array[$k], $key, $child_key);

                }

            }

        }

        // return the modified array
        return $array;

    }

}
