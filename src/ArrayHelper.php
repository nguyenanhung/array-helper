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
                        $ad         = $dm[$i - 1][$j - 1];
                        $dm[$i][$j] = $ad + 1;
                    } else {
                        $a1         = $dm[$i - 1][$j];
                        $a2         = $dm[$i][$j - 1];
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
                        throw new InvalidArgumentException(
                            __METHOD__ . "() expects parameter two to be a callable function"
                        );
                    }
                } else {
                    throw new InvalidArgumentException(
                        __METHOD__ . "() expects paramater one to be an array"
                    );
                }
            } else {
                throw new BadMethodCallException(
                    __METHOD__ . "() expects two parameters, an array and a callable function"
                );
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
                            $filtered = self::filterByKey($array, function ($k) use ($prefix) {
                                return strpos($k, $prefix) === 0;
                            });
                        }
                    } else {
                        throw new InvalidArgumentException(
                            __METHOD__ . "() expects parameter two to be a string prefix"
                        );
                    }
                } else {
                    throw new InvalidArgumentException(
                        __METHOD__ . "() expects parameter one to be an array"
                    );
                }
            } else {
                throw new BadMethodCallException(
                    __METHOD__ . "() expects two parameters, an array and a string prefix"
                );
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
                                $endsWith   = Str::endsWith($needle, $wildcard);
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
                            throw new InvalidArgumentException(
                                __METHOD__ . "() expects parameter three, the wildcard character, to be a string"
                            );
                        }
                    } else {
                        throw new InvalidArgumentException(
                            __METHOD__ . "() expects parameter two, the haystack, to be an array"
                        );
                    }
                } else {
                    throw new InvalidArgumentException(
                        __METHOD__ . "() expects parameter one, the needle, to be a string"
                    );
                }
            } else {
                throw new BadMethodCallException(
                    __METHOD__ . "() expects two or three parameters: needle, haystack, and wildcard"
                );
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
                            throw new InvalidArgumentException(
                                __METHOD__ . "() expects parameter three, allow zeros, to be a bool"
                            );
                        }
                    } else {
                        throw new InvalidArgumentException(
                            __METHOD__ . "() expects parameter two, array, to be an array"
                        );
                    }
                } else {
                    throw new InvalidArgumentException(
                        __METHOD__ . "() expects parameter one, key, to be a string key name"
                    );
                }
            } else {
                throw new BadMethodCallException(
                    __METHOD__ . "() expects two parameters, a string key name and an array"
                );
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
                                $replaced = array_map(static function ($v) use ($search, $replace) {
                                    return str_ireplace($search, $replace, $v);
                                }, $replaced);
                                $replaced = array_flip($replaced);
                            }
                        } else {
                            throw new InvalidArgumentException(
                                __METHOD__ . "() expects the third parameter, array, to be an array"
                            );
                        }
                    } else {
                        throw new InvalidArgumentException(
                            __METHOD__ . "() expects the second parameter, replace, to be a string or array"
                        );
                    }
                } else {
                    throw new InvalidArgumentException(
                        __METHOD__ . "() expects the first parameter, search, to be a string or array"
                    );
                }
            } else {
                throw new BadMethodCallException(
                    __METHOD__ . "() expects three parameters: search, replace, and array"
                );
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

            $j    = 0;
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
                                $passed = array_filter($array, static function ($v) use ($field) {
                                    return is_array($v) && array_key_exists($field, $v);
                                });
                                if (count($array) === count($passed)) {
                                    // sort the array using the field's value
                                    // by default, usort() will return results in ascending order
                                    //
                                    usort($array, static function ($a, $b) use ($field) {
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
                                    throw new InvalidArgumentException(
                                        __METHOD__ . "() expects parameter one to be an array of arrays with the key '$field'"
                                    );
                                }
                            } else {
                                throw new InvalidArgumentException(
                                    __METHOD__ . "() expects parameter three, sort, to be 'asc[ending]' or 'desc[ending]'"
                                );
                            }
                        } else {
                            throw new InvalidArgumentException(
                                __METHOD__ . "() expects parameter three, sort, to be a string sort order"
                            );
                        }
                    } else {
                        throw new InvalidArgumentException(
                            __METHOD__ . "() expects parameter two, field, to be a string field name"
                        );
                    }
                } else {
                    throw new InvalidArgumentException(
                        __METHOD__ . "() expects parameter one, array, to be an array"
                    );
                }
            } else {
                throw new BadMethodCallException(
                    __METHOD__ . "() expects two or three parameters"
                );
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
                                $passed = array_filter($array, static function ($v) use ($property) {
                                    return is_object($v)
                                           && (property_exists($v, $property) || isset($v->$property));
                                });
                                if (count($array) === count($passed)) {
                                    // sort the array using the property's value
                                    // by default, usort() will return results in ascending order
                                    //
                                    usort($array, static function ($a, $b) use ($property) {
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
                                    throw new InvalidArgumentException(
                                        __METHOD__ . "() expects parameter one to be an array of objects with public property '$property'"
                                    );
                                }
                            } else {
                                throw new InvalidArgumentException(
                                    __METHOD__ . "() expects parameter three, sort, to be 'asc[ending]' or 'desc[ending]'"
                                );
                            }
                        } else {
                            throw new InvalidArgumentException(
                                __METHOD__ . "() expects parameter three, sort, to be a string sort order"
                            );
                        }
                    } else {
                        throw new InvalidArgumentException(
                            __METHOD__ . "() expects parameter two, property, to be a string public property name"
                        );
                    }
                } else {
                    throw new InvalidArgumentException(
                        __METHOD__ . "() expects parameter one, array, to be an array"
                    );
                }
            } else {
                throw new BadMethodCallException(
                    __METHOD__ . "() expects two or three parameters"
                );
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
                                $passed = array_filter($array, static function ($v) use ($method) {
                                    return is_object($v) && is_callable(array($v, $method));
                                });
                                if (count($array) === count($passed)) {
                                    // sort the array using the property's value
                                    // by default, usort() will return results in ascending order
                                    //
                                    usort($array, static function ($a, $b) use ($method) {
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
                                    throw new InvalidArgumentException(
                                        __METHOD__ . "() expects parameter one to be an array of objects with public method '$method'"
                                    );
                                }
                            } else {
                                throw new InvalidArgumentException(
                                    __METHOD__ . "() expects parameter three, sort, to be 'asc[ending]' or 'desc[ending]'"
                                );
                            }
                        } else {
                            throw new InvalidArgumentException(
                                __METHOD__ . "() expects parameter three, sort, to be a string sort order"
                            );
                        }
                    } else {
                        throw new InvalidArgumentException(
                            __METHOD__ . "() expects parameter two, method, to be the string name of a public method"
                        );
                    }
                } else {
                    throw new InvalidArgumentException(
                        __METHOD__ . "() expects parameter one, array, to be an array"
                    );
                }
            } else {
                throw new BadMethodCallException(
                    __METHOD__ . "() expects two or three parameters"
                );
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
            $left  = array();
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
            ), static::arrayQuickSort($right));
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
            $tmp   = $p[$i];
            $p[$i] = $p[$j];
            $p[$j] = $tmp;

            // now reverse the elements in between by swapping the ends
            for (++$i, $j = $size; $i < $j; ++$i, --$j) {
                $tmp   = $p[$i];
                $p[$i] = $p[$j];
                $p[$j] = $tmp;
            }

            return $p;
        }
    }
}
