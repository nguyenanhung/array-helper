<?php
/**
 * Project array-helper
 * Created by PhpStorm
 * User: 713uk13m <dev@nguyenanhung.com>
 * Copyright: 713uk13m <dev@nguyenanhung.com>
 * Date: 09/22/2021
 * Time: 20:44
 */
if (!function_exists('arrayQuickSort')) {
    /**
     * Function arrayQuickSort
     *
     * @author: 713uk13m <dev@nguyenanhung.com>
     * @time  : 2019-01-02 10:56
     *
     * @param array|mixed $array
     *
     * @return array
     */
    function arrayQuickSort($array = array()): array
    {
        return nguyenanhung\Libraries\ArrayHelper\ArrayHelper::arrayQuickSort($array);
    }
}
if (!function_exists('arrayToObject')) {
    /**
     * Function arrayToObject
     *
     * @param array|mixed $array
     * @param bool        $strToLower
     *
     * @return array|bool|\stdClass
     * @author   : 713uk13m <dev@nguyenanhung.com>
     * @copyright: 713uk13m <dev@nguyenanhung.com>
     * @time     : 08/18/2021 23:40
     */
    function arrayToObject($array = array(), bool $strToLower = false)
    {
        return nguyenanhung\Libraries\ArrayHelper\ArrayHelper::arrayToObject($array, $strToLower);
    }
}
