<?php

/**
 * The Arr class provides an object interface to the native PHP array
 * functions, as well as adding a few that PHP should support but which are
 * presumably too complex for PHP users to understand.
 *
 * This is currently intended to represent an ordinal array, but a future
 * development will allow non-numeric keys to work, given that PHP's associative
 * arrays have an unfathomable internal ordering and hence it should be
 * workable.
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
    public function __construct($arr=array()) {
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
	 * @param   String  $regex   A regex by which to split the string
     * @param   String  $string  The string to split.
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
     * @param   String   $string    The string to split.
     * @return  \Arr\Arr
	 */
    public static function explode($spl, $string) {
        return new static(explode($delim, $string));

    }

    /// END OF STATICS ///

    // Accessors //

    /**
     * Returns the array elements at the given indices. If any index is an array
     * it is taken to be itself a list of indices. If any of *those* is an array
     * then stop being silly.
     *
     * Returns a new Arr containing those elements.
     *
     * @param   mixed   $index  Index or array of indices to take
     * @param   mixed   ...     More of the same
     * @return  \Arr\Arr
     */
    public function slice() {
        $take = new static();

        foreach(func_get_args() as $arg) {
            if (is_array($arg)) {
                $take->concat( call_user_func_array($this, 'slice', $arg) );
            }
            else {
                if ($arg < 0) {
                    $arg = count($this->_arr) + $arg;
                }
                $take->push( $this->_arr[$arg] );
            }
        }
    }

    // Mutators //

    /**
     * Adds these arrays to the array, merging them all and destroying any
     * key/value associations. Modifies the object.
     *
     * @param   Array   $arr    Array to merge
     * @param   Array   ...     Additional arrays
     * @return  $this
     */
    public function concat() {
        foreach(func_get_args() as $arr) {
            $this->_arr = array_merge($this->_arr, array_values($arr));
        }

        return $this;
    }

    /**
     * Adds items onto the end of the array. Returns the Arr again for chaining.
     *
     * @param   mixed   $any    Anything
     * @param   mixed   ...     Any more things
     * @return  $this
     */
    public function push() {
        $this->splice(count($this->_arr), 0, func_get_args());

        return $this;
    }

    /**
     * Adds items onto the front of the array. Returns the Arr again for
     * chaining.
     *
     * @param   mixed   $any    Anything
     * @param   mixed   ...     Any more things
     * @return  $this
     */
    public function unshift() {
        $this->splice(0, 0, func_get_args());

        return $this;
    }

    /**
     * Removes $length items from the index $start, and replaces them with
     * $array if provided. $array is converted to an array if it is not one.
     * Returns the Arr again for chaining.
     *
     * If no $length is provided then the array is truncated at $start.
     *
     * If $start is negative then it is counted from the end of the array.
     *
     * @param   int     $start      Index of first removed item
     * @param   int     $length     Optional number of items to remove
     * @param   array   $rep        Optional replacement array
     * @return  $this
     */
    public function splice($start, $length=null, $rep=null) {
        array_splice($this->_arr, $length, $rep);

        return $this;
    }

    // Non-Arr-returning utilities //

    /**
	 * Returns the string formed by concatenating the array elements with the
     * join string between each one, a la implode
     *
	 * @param   String   $str    The interstitial string
     * @return  String
	 */
    public function join($string) {
        return implode($string, $this->_arr);
    }

    /**
     * Alias for join
     *
	 * @param   String   $str    The interstitial string
     * @return  String
	 */
    public function implode($string) {
        return implode($string, $this->_arr);
    }

    // Copy-modifiers //

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
        if (!is_callable($function)) {
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
        if (!is_callable($function)) {
            $f = $function;
            $function = function($_) use ($f) {
                return eval($f);
            };
        }
            
        return new static(array_filter($this->_arr, $function));
    }

    /**
	 * Alias for grep
     *
	 * @param   callable   $func    The function to apply
     * @return  \Arr\Arr
	 */
    public function filter($function) {
        return $this->grep($function);
    }

    /**
     * Sort the array by its values, discarding keys, using the provided
     * function. If no function is provided, the < / > operators are used, which
     * is to say the behaviour is totally unpredictable.
     *
     * As a special treat, if you provide a non-callable string, it will be used
     * as the body of a new function. The parameters to the function will be $a
     * and $b.
     *
	 * @param   callable   $func    The function to apply. Takes two parameters.
     * @return  \Arr\Arr
	 */
    public function sort($function=null) {
        if (null === $function) {
            $function = function($a, $b) {
                return ($a < $b) ? -1 : ($a == $b) ? 0 : 1;
            };
        }
        if (!is_callable($function)) {
            $f = $function;
            $function = function($a, $b) use ($f) {
                return eval($f);
            };
        }
            
        return new static(usort($this->_arr, $function));
    }

    /**
	 * Return the values of the array in whatever internal order they are in,
     * effectively discarding the keys and making an ordinal array.
     *
     * @return  \Arr\Arr
	 */
    public function values() {
        return new static(array_values($this->_arr));
    }
}
