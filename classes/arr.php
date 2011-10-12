<?php

/**
 * The Arr class provides an object interface to the native PHP array
 * functions, as well as adding a few that PHP should support but which are
 * presumably too complex for PHP users to understand.
 *
 * @package     Arr
 */

namespace Arr;

class Arr {
    protected $_arr;
    
    /**
	 * Constructor just takes a normal array and creates a proper Arr out of
     * it.
	 *
	 * @param   array   $array    The search array
	 */
    public function __construct($arr) {
        $this->_arr = $arr;
    }

    /**
	 * If only one parameter is provided and it is an array it simply constructs
     * a new Arr using that. Otherwise, all parameters are shoved into their
     * own array and that is used instead.
	 *
	 * @param   mixed   $var1   Either an array or not an array
     * @param   mixed   ...     More things of any type
     * @return  \Arr\Arr
	 */
    public static function forge() {
        $args = func_get_args();

        if (count($args) == 1 && is_array($args[0])) {
            return new static($args[0]);
        }
        else {
            return new static($args);
        }
    }

    /**
	 * Split this string by the given REGEX (!) and return an Arr containing
     * the resulting list of bits. The regex string should NOT contain
     * delimiters as it will be used inside another regex and that'll break it
     * so don't.
     *
	 * @param   String   $regex   A regex by which to split the string
     * @param   String   $string  The string to split.
     * @return  \Arr\Arr
	 */
    public static function split($regex, $string) {
        $re = "/(.*?)(?:{$regex}|$)/";

        preg_match_all($re, $string, $arr);

        // The matches are in $1, which in php is handily [1].
        $arr = $arr[1];

        // The last bit of $arr is now the captured end-of-string
        array_pop($arr);

        return new static($arr);
    }

    /**
	 * Performs the same function as new \Arr\Arr(explode($delim, $string))
     *
	 * @param   String   $delim     The delimiter string
     * @param   String   $string  The string to split.
     * @return  \Arr\Arr
	 */
    public static function explode($spl, $string) {
        return new static(explode($delim, $string));

    }

    /// END OF STATICS ///

    /**
	 * Returns the Arr formed by applying $function to each element of this
     * object and collecting the return values. Returns a new Arr; does not
     * alter the object.
     *
     * As a special treat, if you provide a non-callable string, it will be used
     * as the body of a new function. The parameter to the function will be $_.
     *
	 * @param   callable   $func    The function to apply
     * @return  \Arr\Arr
	 */
    public function map($function) {
        if (!is_callable($f)) {
            $f = $function;
            $function = function($_) use ($f) {
                return eval($f);
            };
        }
            
        return new static(array_map($function, $this->_arr));
    }

    /**
	 * Returns the Arr formed by applying $function to each element of this
     * object and collecting those that return a true value.
     *
     * As a special treat, if you provide a non-callable string, it will be used
     * as the body of a new function. The parameter to the function will be $_.
     *
	 * @param   callable   $func    The function to apply
     * @return  \Arr\Arr
	 */
    public function grep($function) {
        if (!is_callable($f)) {
            $f = $function;
            $function = function($_) use ($f) {
                return eval($f);
            };
        }
            
        return new static(array_filter($this->_arr, $function));
    }
}