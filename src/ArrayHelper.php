<?php
/**
 * Project array-helper
 * Created by PhpStorm
 * User: 713uk13m <dev@nguyenanhung.com>
 * Copyright: 713uk13m <dev@nguyenanhung.com>
 * Date: 09/22/2021
 * Time: 20:47
 */

namespace nguyenanhung\Libraries\ArrayHelper;

use InvalidArgumentException;
use BadMethodCallException;
use stdClass;
use nguyenanhung\Libraries\Number\Number as Num;
use nguyenanhung\Libraries\String\Str;

if (!class_exists('nguyenanhung\Libraries\ArrayHelper\ArrayHelper')) {
    /**
     * Class Arr - The array (aka, "arr") class
     *
     * @package   nguyenanhung\Classes\Helper
     * @author    713uk13m <dev@nguyenanhung.com>
     * @copyright 713uk13m <dev@nguyenanhung.com>
     */
    class ArrayHelper
    {
        /**
         * @var  string  default delimiter for path()
         */
        public static $delimiter = '.';

        /**
         * Returns the diff between $from and $to arrays
         *
         * @param string[]  the actual array
         * @param string[]  the expected array
         *
         * @return  array[]  an array of arrays with keys 'value', the string value, and
         *     'mask', and integer mask where -1 means deleted, 0 means unchanged, and 1
         *     means added
         * @see    http://stackoverflow.com/a/22021254/537724  Clamarius' StackOverflow
         *     answer to "Highlight the difference between two strings in PHP" (edited
         *     to be PSR-2-compliant and to return a single array of rows instead of an
         *     array of columns).
         * @since  0.1.2
         */
        public static function diff(array $from, array $to): array
        {
            $diffs = [];

            $dm = array();
            $n1 = count($from);
            $n2 = count($to);

            for ($j = -1; $j < $n2; $j++) {
                $dm[-1][$j] = 0;
            }

            for ($i = -1; $i < $n1; $i++) {
                $dm[$i][-1] = 0;
            }

            for ($i = 0; $i < $n1; $i++) {
                for ($j = 0; $j < $n2; $j++) {
                    if ($from[$i] === $to[$j]) {
                        $ad = $dm[$i - 1][$j - 1];
                        $dm[$i][$j] = $ad + 1;
                    } else {
                        $a1 = $dm[$i - 1][$j];
                        $a2 = $dm[$i][$j - 1];
                        $dm[$i][$j] = max($a1, $a2);
                    }
                }
            }

            $i = $n1 - 1;
            $j = $n2 - 1;

            while (($i > -1) || ($j > -1)) {
                if (($j > -1) && $dm[$i][$j - 1] === $dm[$i][$j]) {
                    $diffs[] = ['value' => $to[$j], 'mask' => 1];
                    $j--;
                    continue;
                }
                if (($i > -1) && $dm[$i - 1][$j] === $dm[$i][$j]) {
                    $diffs[] = ['value' => $from[$i], 'mask' => -1];
                    $i--;
                    continue;
                }
                {
                    $diffs[] = ['value' => $from[$i], 'mask' => 0];
                    $i--;
                    $j--;
                }
            }

            return array_reverse($diffs);
        }

        /**
         * Filters an array by key
         *
         * I'll iterate over each key in $array passing it to the $callback function.
         * If the callback function returns true, the current value from $array is added
         * to the result array. Array keys are preserved.
         *
         * For example:
         *
         *     $a = ['foo', 'bar', 'baz'];
         *     Arr::filterByKey($a, function ($k) {
         *         return $k > 1;
         *     });  // returns ['baz']
         *
         * @since 0.1.0
         *
         * @param array    $array    the array to filter
         * @param callback $callback the function to call for each key in $array
         *
         * @return array the filtered array
         *
         * @throws  BadMethodCallException    if $array or $callback is null
         * @throws  InvalidArgumentException  if $array is not an array
         * @throws  InvalidArgumentException  if $callback is not a callable function
         *
         * @see   http://php.net/manual/en/function.array-filter.php#99073  Acid24's filter
         *    by key function on on array_filter() man page
         */
        public static function filterByKey(array $array, callable $callback): array
        {
            $filtered = array();

            // if $array and $callback are given
            if ($array !== null && $callback !== null) {
                // if the input arr is actually an arr
                if (is_array($array)) {
                    // if $callback is callable
                    if (is_callable($callback)) {
                        // if $array is not empty
                        if (!empty($array)) {
                            // if there are keys that pass the filter
                            $keys = array_filter(array_keys($array), $callback);
                            if (!empty($keys)) {
                                $filtered = array_intersect_key($array, array_flip($keys));
                            }
                        }
                    } else {
                        throw new InvalidArgumentException(__METHOD__ . "() expects parameter two to be a callable function");
                    }
                } else {
                    throw new InvalidArgumentException(__METHOD__ . "() expects paramater one to be an array");
                }
            } else {
                throw new BadMethodCallException(__METHOD__ . "() expects two parameters, an array and a callable function");
            }

            return $filtered;
        }

        /**
         * Filters an array by a key prefix
         *
         * I'll iterate over each key in $array. If the key starts with $prefix, I'll
         * add it to the result array. Array keys are preserved.
         *
         * For example:
         *     $a = ['foo' => 'bar', 'baz' => 'qux'];
         *     Arr::filterByKeyPrefix($a, 'b');  // returns ['baz']
         *
         * @since  0.1.0
         *
         * @param array  $array  the array to filter
         * @param string $prefix the key's prefix to filter
         *
         * @return  array  the filtered array
         *
         * @throws  BadMethodCallException    if $array or $prefix is null
         * @throws  InvalidArgumentException  if $array is not an array
         * @throws  InvalidArgumentException  if $prefix is not a string
         */
        public static function filterByKeyPrefix(array $array, string $prefix): array
        {
            $filtered = array();

            // if $array and $prefix are given
            if ($array !== null && $prefix !== null) {
                // if $array is actually an array
                if (is_array($array)) {
                    // if $prefix is a string
                    if (is_string($prefix)) {
                        // if $array is not empty
                        if (!empty($array)) {
                            // filter the array by the key's prefix
                            $filtered = self::filterByKey($array, function($k) use ($prefix) {
                                return strpos($k, $prefix) === 0;
                            });
                        }
                    } else {
                        throw new InvalidArgumentException(__METHOD__ . "() expects parameter two to be a string prefix");
                    }
                } else {
                    throw new InvalidArgumentException(__METHOD__ . "() expects parameter one to be an array");
                }
            } else {
                throw new BadMethodCallException(__METHOD__ . "() expects two parameters, an array and a string prefix");
            }

            return $filtered;
        }

        /**
         * Wildcard search for a value in an array
         *
         * I'll search $haystack for $needle. Unlike PHP's native in_array() method,
         * I'll accept begins-with (e.g., "foo*"), ends-with (e.g., "*foo"), and
         * contains (e.g., "*foo*") wildcard notation.
         *
         * For example:
         *
         *     Arr::inArray('foo', ['foo', 'bar']);  // returns true
         *     Arr::inArray('qux', ['foo', 'bar']);  // returns false
         *     Arr::inArray('fo*', ['foo', 'bar']);  // returns true
         *     Arr::inArray('*oo', ['foo', 'bar']);  // returns true
         *     Arr::inArray('*o*', ['foo', 'bar']);  // returns true
         *
         * @since  0.1.0
         *
         * @param string   $needle    the needle to find
         * @param string[] $haystack  the haystack to search
         * @param string   $wildcard  the wildcard character (optional; if omitted,
         *                            defaults to '*')
         *
         * @return  bool  true if the needle exists in haystack
         *
         * @throws  BadMethodCallException    if $needle, $haystack, or $wildcard is null
         * @throws  InvalidArgumentException  if $needle is not a string
         * @throws  InvalidArgumentException  if $haystack is not an array
         * @throws  InvalidArgumentException  if $wildcard is not a string
         */
        public static function inArray(string $needle, array $haystack, string $wildcard = '*'): bool
        {
            $inArray = false;

            // if $needle, $haystack, and $wildcard are given
            if ($needle !== null && $haystack !== null && $wildcard !== null) {
                // if $needle is a string
                if (is_string($needle)) {
                    // if $haystack is an array
                    if (is_array($haystack)) {
                        // if $wildcard is a string
                        if (is_string($wildcard)) {
                            // if $needle contains the wildcard character
                            if (strpos($needle, $wildcard) !== false) {
                                // determine if the neeedle starts or ends with the wildcard
                                $startsWith = Str::startsWith($needle, $wildcard);
                                $endsWith = Str::endsWith($needle, $wildcard);
                                // set the *actual* needle
                                $needle = str_ireplace($wildcard, '', $needle);
                                // loop through the haystack
                                foreach ($haystack as $value) {
                                    if ($startsWith && $endsWith) {
                                        $inArray = strpos($value, $needle) !== false;
                                    } elseif ($startsWith) {
                                        $inArray = Str::endsWith($value, $needle);
                                    } else {
                                        $inArray = Str::startsWith($value, $needle);
                                    }
                                    // if the needle is in the array, stop looking
                                    if ($inArray) {
                                        break;
                                    }
                                }
                            } else {
                                $inArray = in_array($needle, $haystack);
                            }
                        } else {
                            throw new InvalidArgumentException(__METHOD__ . "() expects parameter three, the wildcard character, to be a string");
                        }
                    } else {
                        throw new InvalidArgumentException(__METHOD__ . "() expects parameter two, the haystack, to be an array");
                    }
                } else {
                    throw new InvalidArgumentException(__METHOD__ . "() expects parameter one, the needle, to be a string");
                }
            } else {
                throw new BadMethodCallException(__METHOD__ . "() expects two or three parameters: needle, haystack, and wildcard");
            }

            return $inArray;
        }

        /**
         * Returns true if the array has at least one string key (excluding int strings)
         *
         * PHP natively treats all arrays as associative arrays. I'll consider an
         * associative array as an array with a string key. Interally, PHP casts
         * string keys containing valid integers to integer type (e.g., "8" will be
         * stored as 8).
         *
         * For example:
         *
         *     Arr::isAssoc([1, 2, 3]);                       // returns false
         *     Arr::isAssoc(['foo', 'bar', 'baz']);           // returns false
         *     Arr::isAssoc(['1' => 'foo', 2 => 'bar']);      // returns false (PHP casts '1' to 1)
         *     Arr::isAssoc(['1' => 'foo', 8 => 'bar']);      // returns false (sparse doens't matter)
         *     Arr::isAssoc(['1' => 'foo', 'bar' => 'baz']);  // returns true
         *
         * @since  0.1.0
         *
         * @param array $array the array to test
         *
         * @return  bool  true if the array has a string key (excluding int strings)
         */
        public static function isAssoc(array $array): bool
        {
            $isAssoc = false;

            if (!empty($array) && is_array($array)) {
                $isAssoc = (bool) count(array_filter(array_keys($array), 'is_string'));
            }

            return $isAssoc;
        }

        /**
         * Returns true if $key does not exist in $array or $array[$key] is empty
         *
         * PHP's isset() method is will return false if the key does not exist or if the
         * key exists and its value is null. However, it will return true if the key
         * exists and its value is not null (including other "empty" values like '', false
         * and array()).
         *
         * PHP's empty() method (or some frameworks) will throw a warning if you attempt
         * to test a non-existant key in an array.
         *
         * I, on the other hand, will return false if the key does not exist in the array
         * or if the key's value is empty.
         *
         * For example:
         *
         *     $a = ['foo' => null, 'bar' => array(), 'qux' => 'hello'];
         *
         *     // when key doesn't exist (!)
         *     isset($a['quux']);           // returns false
         *     ! empty($a['quux']);         // throws key-does-not-exist warning
         *     ! Arr::isEmpty('quux', $a);  // returns false
         *
         *     // when key does exist, but value is null
         *     isset($a['foo']);           // returns false
         *     ! empty($a['foo']);         // returns false
         *     ! Arr::isEmpty('foo', $a);  // returns false
         *
         *     // when key does exist, but value is "empty" (!)
         *     isset($a['bar']);           // returns true
         *     ! empty($a['bar']);         // returns false
         *     ! Arr::isEmpty('bar', $a);  // returns false
         *
         *     // when key does exist, but value is not "empty"
         *     isset($a['qux']);           // returns true
         *     ! empty($a['qux']);         // returns true
         *     ! Arr::isEmpty('qux', $a);  // returns true
         *
         * @since  0.1.0
         *
         * @param string $key          the key's name
         * @param array  $array        the array to test
         * @param bool   $isZeroEmpty  a flag indicating whether or not zero is
         *                             considered empty (optional; if omitted, defaults to true - i.e., the
         *                             default behavior of PHP's empty() function )
         *
         * @return  bool  true if the key exists and its value is not empty
         *
         * @throws  BadMethodCallException    if $key or $array are null
         * @throws  InvalidArgumentException  if $key is not a string
         * @throws  InvalidArgumentException  if $array is not an array
         * @throws  InvalidArgumentException  if $isZeroEmpty is not a bool value
         */
        public static function isEmpty(string $key, array $array, bool $isZeroEmpty = true): bool
        {
            $isEmpty = true;

            // if $key and array are given
            if ($key !== null && $array !== null) {
                // if $key is a string
                if (is_string($key)) {
                    // if $array is an array
                    if (is_array($array)) {
                        // if $zero is a bool value
                        if (is_bool($isZeroEmpty)) {
                            // if $array is not empty
                            // if the key exists
                            if (!empty($array) && array_key_exists($key, $array)) {
                                $isEmpty = empty($array[$key]);
                                // if the value is "empty" but zero is not considered empty
                                if ($isEmpty && !$isZeroEmpty) {
                                    // if the value is zero it is not empty
                                    $isEmpty = !Num::isZero($array[$key]);
                                }
                            }
                        } else {
                            throw new InvalidArgumentException(__METHOD__ . "() expects parameter three, allow zeros, to be a bool");
                        }
                    } else {
                        throw new InvalidArgumentException(__METHOD__ . "() expects parameter two, array, to be an array");
                    }
                } else {
                    throw new InvalidArgumentException(__METHOD__ . "() expects parameter one, key, to be a string key name");
                }
            } else {
                throw new BadMethodCallException(__METHOD__ . "() expects two parameters, a string key name and an array");
            }

            return $isEmpty;
        }

        /**
         * Replaces all occurences of $search with $replace in $array's keys
         *
         * I'll return an array with all occurences of $search in the array's keys
         * replaced with the given $replace value (case-insensitive).
         *
         * @since   0.1.0
         *
         * @param mixed $search    the value being searched for (aka the needle); an
         *                         array may be used to designate multiple neeeles
         * @param mixed $replace   the replacement value that replaced found $search
         *                         values; an array may be used to designate multiple replacements
         * @param array $array     the array to replace
         *
         * @return  array  the array with replacements
         *
         * @throws  BadMethodCallException    if $search, $replace, or $array are null
         * @throws  InvalidArgumentException  if $search is not a string or array
         * @throws  InvalidArgumentException  if $replace is not a string or array
         * @throws  InvalidArgumentException  if $array is not an array
         *
         * @see     http://us1.php.net/str_replace  str_replace() man page
         */
        public static function keyStringReplace($search, $replace, array $array): array
        {
            $replaced = array();

            // if $search, $replace, and $array are given
            if ($search !== null && $replace !== null && $array !== null) {
                // if $search is a string or an array
                if (is_string($search) || is_array($search)) {
                    // if $replace is a string or an array
                    if (is_string($replace) || is_array($replace)) {
                        // if $array is actually an array
                        if (is_array($array)) {
                            // if $array isn't empty
                            if (!empty($array)) {
                                // flip the array, search/replace, and flip again
                                $replaced = array_flip($array);
                                $replaced = array_map(static function($v) use ($search, $replace) {
                                    return str_ireplace($search, $replace, $v);
                                }, $replaced);
                                $replaced = array_flip($replaced);
                            }
                        } else {
                            throw new InvalidArgumentException(__METHOD__ . "() expects the third parameter, array, to be an array");
                        }
                    } else {
                        throw new InvalidArgumentException(__METHOD__ . "() expects the second parameter, replace, to be a string or array");
                    }
                } else {
                    throw new InvalidArgumentException(__METHOD__ . "() expects the first parameter, search, to be a string or array");
                }
            } else {
                throw new BadMethodCallException(__METHOD__ . "() expects three parameters: search, replace, and array");
            }

            return $replaced;
        }

        /**
         * Returns an array of this array's permutations
         *
         * @param array $set an array of strings
         *
         * @return  array  an array of $array's permutations
         * @see    http://docstore.mik.ua/orelly/webprog/pcook/ch04_26.htm  an example from
         *     O'Reilly's PHPCookbook
         * @since  0.1.2
         */
        public static function permute(array $set): array
        {
            $perms = [];

            $j = 0;
            $size = count($set) - 1;
            $perm = range(0, $size);

            do {
                foreach ($perm as $i) {
                    $perms[$j][] = $set[$i];
                }
            }
            while ($perm = self::getNextPermutation($perm, $size) and ++$j);

            return $perms;
        }

        /**
         * Sorts an array of associative arrays by a field's value
         *
         * Oftentimes, you have a 0-indexed array of associative arrays. For example,
         * a SELECT sql query result or a display-friendly data array. I'll sort a
         * 0-based array of associative arrays by a field's value.
         *
         * For example:
         *
         *     $a = [['a' => 3], ['a' => 1], ['a' => 2]];
         *     Arr::usort_field($a, 'a'); // returns [['a' => 1], ['a' => 2], ['a' => 3]]
         *
         * @since  0.1.0
         *
         * @param array[] $array  the array of associative arrays to sort
         * @param string  $field  the associative array's field name (aka, key)
         * @param string  $sort   the sort order (possible values 'asc[ending]' or
         *                        'desc[ending]) (optional; if omitted, defaults to 'asc') (case-insensitive)
         *
         * @return  array[]  the sorted array
         *
         * @throws  BadMethodCallException    if $array, $field, or $sort is null
         * @throws  InvalidArgumentException  if $array is not an array
         * @throws  InvalidArgumentException  if $field is not a string
         * @throws  InvalidArgumentException  if $sort is not a string
         * @throws  InvalidArgumentException  if $sort is not the string 'asc[ending]' or
         *    'desc[ending]'
         * @throws  InvalidArgumentException  if $array is not an array of arrays with
         *    the key $field
         */
        public static function sortByField(array $array, string $field, string $sort = 'asc'): array
        {
            // if $array, $field, and $sort are given
            if ($array !== null && $field !== null && $sort !== null) {
                // if $array is actually an array
                if (is_array($array)) {
                    // if $field is a string
                    if (is_string($field)) {
                        // if $sort is a string
                        if (is_string($sort)) {
                            // if $sort is a valid sort
                            if (in_array(strtolower($sort), array('asc', 'ascending', 'desc', 'descending'))) {
                                // if $array is an array of arrays with $field key
                                $passed = array_filter($array, static function($v) use ($field) {
                                    return is_array($v) && array_key_exists($field, $v);
                                });
                                if (count($array) === count($passed)) {
                                    // sort the array using the field's value
                                    // by default, usort() will return results in ascending order
                                    //
                                    usort($array, static function($a, $b) use ($field) {
                                        if ($a[$field] < $b[$field]) {
                                            return -1;
                                        }

                                        if ($a[$field] > $b[$field]) {
                                            return 1;
                                        }

                                        return 0;
                                    });
                                    // if the sort order is descending
                                    $sort = strtolower($sort);
                                    if ($sort === 'desc' || $sort === 'descending') {
                                        $array = array_reverse($array);
                                    }
                                } else {
                                    throw new InvalidArgumentException(__METHOD__ . "() expects parameter one to be an array of arrays with the key '$field'");
                                }
                            } else {
                                throw new InvalidArgumentException(__METHOD__ . "() expects parameter three, sort, to be 'asc[ending]' or 'desc[ending]'");
                            }
                        } else {
                            throw new InvalidArgumentException(__METHOD__ . "() expects parameter three, sort, to be a string sort order");
                        }
                    } else {
                        throw new InvalidArgumentException(__METHOD__ . "() expects parameter two, field, to be a string field name");
                    }
                } else {
                    throw new InvalidArgumentException(__METHOD__ . "() expects parameter one, array, to be an array");
                }
            } else {
                throw new BadMethodCallException(__METHOD__ . "() expects two or three parameters");
            }

            return $array;
        }

        /**
         * Sorts an array of objects using a public property's value
         *
         * @since  0.1.0
         *
         * @param object[] $array     the array of objects to sort
         * @param string   $property  the object's public property name (may be a magic
         *                            public property via the object's __get() method)
         * @param string   $sort      the sort order (possible values 'asc[ending]' or
         *                            'desc[ending]) (optional; if omitted, defaults to 'asc') (case-insensitive)
         *
         * @return  object[]  the sorted array
         *
         * @throws  BadMethodCallException    if $array, $property, or $sort is null
         * @throws  InvalidArgumentException  if $array is not an array
         * @throws  InvalidArgumentException  if $property is not a string
         * @throws  InvalidArgumentException  if $sort is not a string
         * @throws  InvalidArgumentException  if $sort is not the string 'asc[ending]' or
         *    'desc[ending]'
         * @throws  InvalidArgumentException  if $array is not an array of objects with
         *    the public property $property
         */
        public static function sortByProperty(array $array, string $property, string $sort = 'asc'): array
        {
            // if $array, $property, and $sort are given
            if ($array !== null && $property !== null && $sort !== null) {
                // if $array is actually an array
                if (is_array($array)) {
                    // if $property is a string
                    if (is_string($property)) {
                        // if $sort is a string
                        if (is_string($sort)) {
                            // if $sort is a valid sort
                            if (in_array(strtolower($sort), array('asc', 'ascending', 'desc', 'descending'))) {
                                // if $array is an array of objects with $property
                                // use property_exists() to allow null values of explicit public properties
                                // use isset() to allow "magic" properties via the __get() magic method
                                //
                                $passed = array_filter($array, static function($v) use ($property) {
                                    return is_object($v) && (property_exists($v, $property) || isset($v->$property));
                                });
                                if (count($array) === count($passed)) {
                                    // sort the array using the property's value
                                    // by default, usort() will return results in ascending order
                                    //
                                    usort($array, static function($a, $b) use ($property) {
                                        if ($a->$property < $b->$property) {
                                            return -1;
                                        }

                                        if ($a->$property > $b->$property) {
                                            return 1;
                                        }

                                        return 0;
                                    });
                                    // if the sort order is descending
                                    $sort = strtolower($sort);
                                    if ($sort === 'desc' || $sort === 'descending') {
                                        $array = array_reverse($array);
                                    }
                                } else {
                                    throw new InvalidArgumentException(__METHOD__ . "() expects parameter one to be an array of objects with public property '$property'");
                                }
                            } else {
                                throw new InvalidArgumentException(__METHOD__ . "() expects parameter three, sort, to be 'asc[ending]' or 'desc[ending]'");
                            }
                        } else {
                            throw new InvalidArgumentException(__METHOD__ . "() expects parameter three, sort, to be a string sort order");
                        }
                    } else {
                        throw new InvalidArgumentException(__METHOD__ . "() expects parameter two, property, to be a string public property name");
                    }
                } else {
                    throw new InvalidArgumentException(__METHOD__ . "() expects parameter one, array, to be an array");
                }
            } else {
                throw new BadMethodCallException(__METHOD__ . "() expects two or three parameters");
            }

            return $array;
        }

        /**
         * Sorts an array of objects using a method's return value
         *
         * @since   0.1.0
         *
         * @param object[] $array    the array of objects to sort
         * @param string   $method   the name of the public method to use (may be a
         *                           "magic" method via the object's __call() magic method)
         * @param string   $sort     the sort order (possible values 'asc[ending]' or
         *                           'desc[ending]) (optional; if omitted, defaults to 'asc') (case-insensitive)
         *
         * @return  object[]  the sorted array
         *
         * @throws  BadMethodCallException    if $array, $property, or $sort is null
         * @throws  InvalidArgumentException  if $array is not an array
         * @throws  InvalidArgumentException  if $property is not a string
         * @throws  InvalidArgumentException  if $sort is not a string
         * @throws  InvalidArgumentException  if $sort is not the string 'asc[ending]' or
         *     'desc[ending]'
         * @throws  InvalidArgumentException  if $array is not an array of objects with
         *     the public property $property
         */
        public static function sortByMethod(array $array, string $method, string $sort = 'asc'): array
        {
            // if $array, $method, and $sort are given
            if ($array !== null && $method !== null && $sort !== null) {
                // if $array is actually an array
                if (is_array($array)) {
                    // if $method is a string
                    if (is_string($method)) {
                        // if $sort is a string
                        if (is_string($sort)) {
                            // if $sort is a valid sort
                            if (in_array(strtolower($sort), array('asc', 'ascending', 'desc', 'descending'))) {
                                // if $array is an array of objects with public method $method
                                // use is_callable() to allow "magic" methods
                                //
                                $passed = array_filter($array, static function($v) use ($method) {
                                    return is_object($v) && is_callable(array($v, $method));
                                });
                                if (count($array) === count($passed)) {
                                    // sort the array using the property's value
                                    // by default, usort() will return results in ascending order
                                    //
                                    usort($array, static function($a, $b) use ($method) {
                                        if ($a->$method() < $b->$method()) {
                                            return -1;
                                        }

                                        if ($a->$method() > $b->$method()) {
                                            return 1;
                                        }

                                        return 0;
                                    });
                                    // if the sort order is descending
                                    $sort = strtolower($sort);
                                    if ($sort === 'desc' || $sort === 'descending') {
                                        $array = array_reverse($array);
                                    }
                                } else {
                                    throw new InvalidArgumentException(__METHOD__ . "() expects parameter one to be an array of objects with public method '$method'");
                                }
                            } else {
                                throw new InvalidArgumentException(__METHOD__ . "() expects parameter three, sort, to be 'asc[ending]' or 'desc[ending]'");
                            }
                        } else {
                            throw new InvalidArgumentException(__METHOD__ . "() expects parameter three, sort, to be a string sort order");
                        }
                    } else {
                        throw new InvalidArgumentException(__METHOD__ . "() expects parameter two, method, to be the string name of a public method");
                    }
                } else {
                    throw new InvalidArgumentException(__METHOD__ . "() expects parameter one, array, to be an array");
                }
            } else {
                throw new BadMethodCallException(__METHOD__ . "() expects two or three parameters");
            }

            return $array;
        }

        /**
         * Function arrayQuickSort
         *
         * @param array $array
         *
         * @return array
         * @author   : 713uk13m <dev@nguyenanhung.com>
         * @copyright: 713uk13m <dev@nguyenanhung.com>
         * @time     : 08/18/2021 21:58
         */
        public static function arrayQuickSort(array $array = array()): array
        {
            // find array size
            $length = count($array);
            // base case test, if array of length 0 then just return array to caller
            if ($length <= 1) {
                return $array;
            }

            // select an item to act as our pivot point, since list is unsorted first position is easiest
            $pivot = $array[0];
            // declare our two arrays to act as partitions
            $left = array();
            $right = array();
            // loop and compare each item in the array to the pivot value, place item in appropriate partition
            for ($i = 1; $i < $length; $i++) {
                if ($array[$i] < $pivot) {
                    $left[] = $array[$i];
                } else {
                    $right[] = $array[$i];
                }
            }

            // use recursion to now sort the left and right lists
            return array_merge(static::arrayQuickSort($left), array(
                $pivot
            ),                 static::arrayQuickSort($right));
        }

        /**
         * Function objectToArray
         *
         * @param mixed $object
         *
         * @return mixed|string
         * @author   : 713uk13m <dev@nguyenanhung.com>
         * @copyright: 713uk13m <dev@nguyenanhung.com>
         * @time     : 08/18/2021 22:02
         */
        public static function objectToArray($object = '')
        {
            if (!is_object($object)) {
                return $object;
            }
            $object = json_encode($object);

            return json_decode($object, true);
        }

        /**
         * Function arrayToObject
         *
         * @param array|mixed $array
         * @param bool        $strToLower
         *
         * @return array|false|\stdClass
         * @author   : 713uk13m <dev@nguyenanhung.com>
         * @copyright: 713uk13m <dev@nguyenanhung.com>
         * @time     : 08/18/2021 23:49
         */
        public static function arrayToObject($array = [], bool $strToLower = false)
        {
            if (!is_array($array)) {
                return $array;
            }
            $object = new stdClass();
            if (count($array) > 0) {
                foreach ($array as $name => $value) {
                    $name = trim($name);
                    if ($strToLower === true) {
                        $name = strtolower($name);
                    }
                    if (!empty($name)) {
                        $object->$name = static::arrayToObject($value);
                    }
                }

                return $object;
            }

            return false;
        }

        /**
         * Converts an array to an object.
         *
         * ### to_object
         * Related global function (description see above).
         *
         * > #### [( jump back )](#available-php-functions)
         *
         * ```php
         * to_object( array $array ): object|null
         * ```
         *
         * #### Example
         * ```php
         * $array = [
         *     'foo' => [
         *          'bar' => 'baz'
         *     ]
         * ];
         *
         * $obj = to_object($array);
         * echo $obj->foo->bar;
         *
         * // baz
         * ```
         *
         * @param array $array
         * The array to be converted.
         *
         * @return object|null
         * A std object representation of the converted array.
         */
        public static function toObject($array)
        {
            $result = json_decode(json_encode($array), false);

            return is_object($result) ? $result : null;
        }

        /**
         * Converts a string or an object to an array.
         *
         * ### to_array
         * Related global function (description see above).
         *
         * > #### [( jump back )](#available-php-functions)
         *
         * ```php
         * to_array( string|object $var ): array|null
         * ```
         *
         * #### Example 1 (string)
         * ```php
         * $var = 'php';
         * to_array( $var );
         *
         * // (
         * //     [0] => p
         * //     [1] => h
         * //     [2] => p
         * // )
         *
         * ```
         * #### Example 2 (object)
         * ```php
         * $var = new stdClass;
         * $var->foo = 'bar';
         *
         * to_array( $var );
         *
         * // (
         * //     [foo] => bar
         * // )
         * ```
         *
         * @param string|object $var
         * String or object.
         *
         * @return array|null
         * An array representation of the converted string or object.
         * Returns null on error.
         */
        public static function dump($var)
        {
            if (is_string($var)) {
                return str_split($var);
            }

            if (is_object($var)) {
                return json_decode(json_encode($var), true);
            }

            return null;
        }

        /**
         * Returns the first element of an array.
         *
         * ### array_first
         * Related global function (description see above).
         *
         * > #### [( jump back )](#available-php-functions)
         *
         * ```php
         * array_first( array $array ): mixed
         * ```
         *
         * #### Example
         * ```php
         * $array = [
         *      'foo' => 'bar',
         *      'baz' => 'qux'
         * ];
         *
         * array_first( $array )
         *
         * // bar
         * ```
         *
         * @param array $array
         * The concerned array.
         *
         * @return mixed
         * The value of the first element, without key. Mixed type.
         *
         */
        public static function first($array)
        {
            return $array[array_keys($array)[0]];
        }

        /**
         * Returns the last element of an array.
         *
         * ### array_last
         * Related global function (description see above).
         *
         * > #### [( jump back )](#available-php-functions)
         *
         * ```php
         * array_last( array $array ): mixed
         * ```
         *
         * #### Example
         * ```php
         * $array = [
         *      'foo' => 'bar',
         *      'baz' => 'qux'
         * ];
         *
         * array_last( $array )
         *
         * // qux
         * ```
         *
         * @param array $array
         * The concerned array.
         *
         * @return mixed
         * The value of the last element, without key. Mixed type.
         */
        public static function last($array)
        {
            return $array[array_keys($array)[sizeof($array) - 1]];
        }

        /**
         * Gets a value in an array by dot notation for the keys.
         *
         * ### array_get
         * Related global function (description see above).
         *
         * > #### [( jump back )](#available-php-functions)
         *
         * ```php
         * array_get( string key, array $array ): mixed
         * ```
         *
         * #### Example
         * ```php
         * $array = [
         *      'foo' => 'bar',
         *      'baz' => [
         *          'qux => 'foobar'
         *      ]
         * ];
         *
         * array_get( 'baz.qux', $array );
         *
         * // foobar
         * ```
         *
         * @param string $key
         * The key by dot notation.
         * @param array  $array
         * The array to search in.
         *
         * @return mixed
         * The searched value, null otherwise.
         */
        public static function get($key, $array)
        {
            if (is_string($key) && is_array($array)) {
                $keys = explode('.', $key);

                while (sizeof($keys) >= 1) {
                    $k = array_shift($keys);

                    if (!isset($array[$k])) {
                        return null;
                    }

                    if (sizeof($keys) === 0) {
                        return $array[$k];
                    }

                    $array = &$array[$k];
                }
            }

            return null;
        }

        /**
         * Sets a value in an array using the dot notation.
         *
         * ### array_set
         * Related global function (description see above).
         *
         * > #### [( jump back )](#available-php-functions)
         *
         * ```php
         * array_set( string key, mixed value, array $array ): boolean
         * ```
         *
         * #### Example 1
         * ```php
         * $array = [
         *      'foo' => 'bar',
         *      'baz' => [
         *          'qux => 'foobar'
         *      ]
         * ];
         *
         * array_set( 'baz.qux', 'bazqux', $array );
         *
         * // (
         * //     [foo] => bar
         * //     [baz] => [
         * //         [qux] => bazqux
         * //     ]
         * // )
         * ```
         *
         * #### Example 2
         * ```php
         * $array = [
         *      'foo' => 'bar',
         *      'baz' => [
         *          'qux => 'foobar'
         *      ]
         * ];
         *
         * array_set( 'baz.foo', 'bar', $array );
         *
         * // (
         * //     [foo] => bar
         * //     [baz] => [
         * //         [qux] => bazqux
         * //         [foo] => bar
         * //     ]
         * // )
         * ```
         *
         * @param string $key
         * The key to set using dot notation.
         * @param mixed  $value
         * The value to set on the specified key.
         * @param array  $array
         * The concerned array.
         *
         * @return bool
         * True if the new value was successfully set, false otherwise.
         */
        public static function set($key, $value, &$array)
        {
            if (is_string($key) && !empty($key)) {

                $keys = explode('.', $key);
                $arrTmp = &$array;

                while (sizeof($keys) >= 1) {
                    $k = array_shift($keys);

                    if (!is_array($arrTmp)) {
                        $arrTmp = [];
                    }

                    if (!isset($arrTmp[$k])) {
                        $arrTmp[$k] = [];
                    }

                    if (sizeof($keys) === 0) {
                        $arrTmp[$k] = $value;

                        return true;
                    }

                    $arrTmp = &$arrTmp[$k];
                }
            }

            return false;
        }

        /**
         * Test if a value is an array with an additional check for array-like objects.
         *
         *     // Returns TRUE
         *     Arr::is_array(array());
         *     Arr::is_array(new ArrayObject);
         *
         *     // Returns FALSE
         *     Arr::is_array(FALSE);
         *     Arr::is_array('not an array!');
         *     Arr::is_array(Database::instance());
         *
         * @param mixed $value value to check
         *
         * @return  boolean
         */
        public static function isArray($value)
        {
            if (is_array($value)) {
                // Definitely an array
                return true;
            } else {
                // Possibly a Traversable object, functionally the same as an array
                return (is_object($value) and $value instanceof \Traversable);
            }
        }

        /**
         * Convert a multi-dimensional array into a single-dimensional array.
         *
         *     $array = array('set' => array('one' => 'something'), 'two' => 'other');
         *
         *     // Flatten the array
         *     $array = Arr::flatten($array);
         *
         *     // The array will now be
         *     array('one' => 'something', 'two' => 'other');
         *
         * [!!] The keys of array values will be discarded.
         *
         * @param array $array array to flatten
         *
         * @return  array
         * @since   3.0.6
         */
        public static function flatten($array)
        {
            $is_assoc = self::isAssoc($array);

            $flat = array();
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $flat = array_merge($flat, self::flatten($value));
                } else {
                    if ($is_assoc) {
                        $flat[$key] = $value;
                    } else {
                        $flat[] = $value;
                    }
                }
            }

            return $flat;
        }

        /**
         * Creates a callable function and parameter list from a string representation.
         * Note that this function does not validate the callback string.
         *
         *     // Get the callback function and parameters
         *     list($func, $params) = Arr::callback('Foo::bar(apple,orange)');
         *
         *     // Get the result of the callback
         *     $result = call_user_func_array($func, $params);
         *
         * @param string $str callback string
         *
         * @return  array   function, params
         */
        public static function callback($str)
        {
            // Overloaded as parts are found
            $command = $params = null;

            // command[param,param]
            if (preg_match('/^([^\(]*+)\((.*)\)$/', $str, $match)) {
                // command
                $command = $match[1];

                if ($match[2] !== '') {
                    // param,param
                    $params = preg_split('/(?<!\\\\),/', $match[2]);
                    $params = str_replace('\,', ',', $params);
                }
            } else {
                // command
                $command = $str;
            }

            if (strpos($command, '::') !== false) {
                // Create a static method callable command
                $command = explode('::', $command, 2);
            }

            return array($command, $params);
        }

        /**
         * Overwrites an array with values from input arrays.
         * Keys that do not exist in the first array will not be added!
         *
         *     $a1 = array('name' => 'john', 'mood' => 'happy', 'food' => 'bacon');
         *     $a2 = array('name' => 'jack', 'food' => 'tacos', 'drink' => 'beer');
         *
         *     // Overwrite the values of $a1 with $a2
         *     $array = Arr::overwrite($a1, $a2);
         *
         *     // The output of $array will now be:
         *     array('name' => 'jack', 'mood' => 'happy', 'food' => 'tacos')
         *
         * @param array $array1 master array
         * @param array $array2 input arrays that will overwrite existing values
         *
         * @return  array
         */
        public static function overwrite($array1, $array2)
        {
            foreach (array_intersect_key($array2, $array1) as $key => $value) {
                $array1[$key] = $value;
            }

            if (func_num_args() > 2) {
                foreach (array_slice(func_get_args(), 2) as $array2) {
                    foreach (array_intersect_key($array2, $array1) as $key => $value) {
                        $array1[$key] = $value;
                    }
                }
            }

            return $array1;
        }

        /**
         * Retrieves multiple paths from an array. If the path does not exist in the
         * array, the default value will be added instead.
         *
         *     // Get the values "username", "password" from $_POST
         *     $auth = Arr::extract($_POST, array('username', 'password'));
         *
         *     // Get the value "level1.level2a" from $data
         *     $data = array('level1' => array('level2a' => 'value 1', 'level2b' => 'value 2'));
         *     Arr::extract($data, array('level1.level2a', 'password'));
         *
         * @param array $array   array to extract paths from
         * @param array $paths   list of path
         * @param mixed $default default value
         *
         * @return  array
         */
        public static function extract($array, array $paths, $default = null)
        {
            $found = array();
            foreach ($paths as $path) {
                self::setPath($found, $path, self::path($array, $path, $default));
            }

            return $found;
        }

        /**
         * Retrieves muliple single-key values from a list of arrays.
         *
         *     // Get all of the "id" values from a result
         *     $ids = Arr::pluck($result, 'id');
         *
         * [!!] A list of arrays is an array that contains arrays, eg: array(array $a, array $b, array $c, ...)
         *
         * @param array  $array list of arrays to check
         * @param string $key   key to pluck
         *
         * @return  array
         */
        public static function pluck($array, $key)
        {
            $values = array();

            foreach ($array as $row) {
                if (isset($row[$key])) {
                    // Found a value in this row
                    $values[] = $row[$key];
                }
            }

            return $values;
        }

        /**
         * Adds a value to the beginning of an associative array.
         *
         *     // Add an empty value to the start of a select list
         *     Arr::unshift($array, 'none', 'Select a value');
         *
         * @param array  $array array to modify
         * @param string $key   array key name
         * @param mixed  $val   array value
         *
         * @return  array
         */
        public static function unshift(array &$array, $key, $val)
        {
            $array = array_reverse($array, true);
            $array[$key] = $val;
            $array = array_reverse($array, true);

            return $array;
        }

        /**
         * Recursive version of [array_map](http://php.net/array_map), applies one or more
         * callbacks to all elements in an array, including sub-arrays.
         *
         *     // Apply "strip_tags" to every element in the array
         *     $array = Arr::map('strip_tags', $array);
         *
         *     // Apply $this->filter to every element in the array
         *     $array = Arr::map(array(array($this,'filter')), $array);
         *
         *     // Apply strip_tags and $this->filter to every element
         *     $array = Arr::map(array('strip_tags',array($this,'filter')), $array);
         *
         * [!!] Because you can pass an array of callbacks, if you wish to use an array-form callback
         * you must nest it in an additional array as above. Calling Arr::map(array($this,'filter'), $array)
         * will cause an error.
         * [!!] Unlike `array_map`, this method requires a callback and will only map
         * a single array.
         *
         * @param mixed $callbacks array of callbacks to apply to every element in the array
         * @param array $array     array to map
         * @param array $keys      array of keys to apply to
         *
         * @return  array
         */
        public static function map($callbacks, $array, $keys = null)
        {
            foreach ($array as $key => $val) {
                if (is_array($val)) {
                    $array[$key] = self::map($callbacks, $array[$key], $keys);
                } elseif (!is_array($keys) or in_array($key, $keys)) {
                    if (is_array($callbacks)) {
                        foreach ($callbacks as $cb) {
                            $array[$key] = call_user_func($cb, $array[$key]);
                        }
                    } else {
                        $array[$key] = call_user_func($callbacks, $array[$key]);
                    }
                }
            }

            return $array;
        }

        /**
         * Recursively merge two or more arrays. Values in an associative array
         * overwrite previous values with the same key. Values in an indexed array
         * are appended, but only when they do not already exist in the result.
         *
         * Note that this does not work the same as [array_merge_recursive](http://php.net/array_merge_recursive)!
         *
         *     $john = array('name' => 'john', 'children' => array('fred', 'paul', 'sally', 'jane'));
         *     $mary = array('name' => 'mary', 'children' => array('jane'));
         *
         *     // John and Mary are married, merge them together
         *     $john = Arr::merge($john, $mary);
         *
         *     // The output of $john will now be:
         *     array('name' => 'mary', 'children' => array('fred', 'paul', 'sally', 'jane'))
         *
         * @param array $array1     initial array
         * @param array $array2,... array to merge
         *
         * @return  array
         */
        public static function merge($array1, $array2)
        {
            if (self::isAssoc($array2)) {
                foreach ($array2 as $key => $value) {
                    if (is_array($value) and isset($array1[$key]) and is_array($array1[$key])) {
                        $array1[$key] = self::merge($array1[$key], $value);
                    } else {
                        $array1[$key] = $value;
                    }
                }
            } else {
                foreach ($array2 as $value) {
                    if (!in_array($value, $array1, true)) {
                        $array1[] = $value;
                    }
                }
            }

            if (func_num_args() > 2) {
                foreach (array_slice(func_get_args(), 2) as $array2) {
                    if (self::isAssoc($array2)) {
                        foreach ($array2 as $key => $value) {
                            if (is_array($value) and isset($array1[$key]) and is_array($array1[$key])) {
                                $array1[$key] = self::merge($array1[$key], $value);
                            } else {
                                $array1[$key] = $value;
                            }
                        }
                    } else {
                        foreach ($array2 as $value) {
                            if (!in_array($value, $array1, true)) {
                                $array1[] = $value;
                            }
                        }
                    }
                }
            }

            return $array1;
        }

        /**
         * Gets a value from an array using a dot separated path.
         *
         *     // Get the value of $array['foo']['bar']
         *     $value = Arr::path($array, 'foo.bar');
         *
         * Using a wildcard "*" will search intermediate arrays and return an array.
         *
         *     // Get the values of "color" in theme
         *     $colors = Arr::path($array, 'theme.*.color');
         *
         *     // Using an array of keys
         *     $colors = Arr::path($array, array('theme', '*', 'color'));
         *
         * @param array  $array     array to search
         * @param mixed  $path      key path string (delimiter separated) or array of keys
         * @param mixed  $default   default value if the path is not set
         * @param string $delimiter key path delimiter
         *
         * @return  mixed
         */
        public static function path($array, $path, $default = null, $delimiter = null)
        {
            if (!self::isArray($array)) {
                // This is not an array!
                return $default;
            }

            if (is_array($path)) {
                // The path has already been separated into keys
                $keys = $path;
            } else {
                if (array_key_exists($path, $array)) {
                    // No need to do extra processing
                    return $array[$path];
                }

                if ($delimiter === null) {
                    // Use the default delimiter
                    $delimiter = self::$delimiter;
                }

                // Remove starting delimiters and spaces
                $path = ltrim($path, "{$delimiter} ");

                // Remove ending delimiters, spaces, and wildcards
                $path = rtrim($path, "{$delimiter} *");

                // Split the keys by delimiter
                $keys = explode($delimiter, $path);
            }

            do {
                $key = array_shift($keys);

                if (ctype_digit($key)) {
                    // Make the key an integer
                    $key = (int) $key;
                }

                if (isset($array[$key])) {
                    if ($keys) {
                        if (self::isArray($array[$key])) {
                            // Dig down into the next part of the path
                            $array = $array[$key];
                        } else {
                            // Unable to dig deeper
                            break;
                        }
                    } else {
                        // Found the path requested
                        return $array[$key];
                    }
                } elseif ($key === '*') {
                    // Handle wildcards

                    $values = array();
                    foreach ($array as $arr) {
                        if ($value = self::path($arr, implode('.', $keys))) {
                            $values[] = $value;
                        }
                    }

                    if ($values) {
                        // Found the values requested
                        return $values;
                    } else {
                        // Unable to dig deeper
                        break;
                    }
                } else {
                    // Unable to dig deeper
                    break;
                }
            }
            while ($keys);

            // Unable to find the value requested
            return $default;
        }

        /**
         * Set a value on an array by path.
         *
         * @see Arr::path()
         *
         * @param array  $array     Array to update
         * @param string $path      Path
         * @param mixed  $value     Value to set
         * @param string $delimiter Path delimiter
         */
        public static function setPath(&$array, $path, $value, $delimiter = null)
        {
            if (!$delimiter) {
                // Use the default delimiter
                $delimiter = self::$delimiter;
            }

            // The path has already been separated into keys
            $keys = $path;
            if (!is_array($path)) {
                // Split the keys by delimiter
                $keys = explode($delimiter, $path);
            }

            // Set current $array to inner-most array path
            while (count($keys) > 1) {
                $key = array_shift($keys);

                if (ctype_digit($key)) {
                    // Make the key an integer
                    $key = (int) $key;
                }

                if (!isset($array[$key])) {
                    $array[$key] = array();
                }

                $array = &$array[$key];
            }

            // Set key on inner-most array
            $array[array_shift($keys)] = $value;
        }

        /**
         * Fill an array with a range of numbers.
         *
         *     // Fill an array with values 5, 10, 15, 20
         *     $values = Arr::range(5, 20);
         *
         * @param integer $step stepping
         * @param integer $max  ending number
         *
         * @return  array
         */
        public static function range($step = 10, $max = 100)
        {
            if ($step < 1)
                return array();

            $array = array();
            for ($i = $step; $i <= $max; $i += $step) {
                $array[$i] = $i;
            }

            return $array;
        }
        //=============================| Protected methods =============================//

        /**
         * Returns the next permutation
         *
         * @param $p
         * @param $size
         *
         * @return bool
         * @author   : 713uk13m <dev@nguyenanhung.com>
         * @copyright: 713uk13m <dev@nguyenanhung.com>
         * @time     : 08/02/2020 38:12
         */
        protected static function getNextPermutation($p, $size): bool
        {
            // slide down the array looking for where we're smaller than the next guy
            for ($i = $size - 1; $i >= 0 && $p[$i] >= $p[$i + 1]; --$i) {
            }

            // if this doesn't occur, we've finished our permutations
            // the array is reversed: (1, 2, 3, 4) => (4, 3, 2, 1)
            if ($i === -1) {
                return false;
            }

            // slide down the array looking for a bigger number than what we found before
            for ($j = $size; $j >= 0 && $p[$j] <= $p[$i]; --$j) {
            }

            // swap them
            $tmp = $p[$i];
            $p[$i] = $p[$j];
            $p[$j] = $tmp;

            // now reverse the elements in between by swapping the ends
            for (++$i, $j = $size; $i < $j; ++$i, --$j) {
                $tmp = $p[$i];
                $p[$i] = $p[$j];
                $p[$j] = $tmp;
            }

            return $p;
        }
    }
}
