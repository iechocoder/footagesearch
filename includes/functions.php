<?php

if (!function_exists('clip_preview_download_link')) {

    /**
     * @param int $clipId
     *
     * @return string
     */
    function clip_preview_download_link($clipId) {
        global $footage_search;

        return $footage_search->previewDownload()->getLink($clipId);
    }
}

if (!function_exists('is_search_result_page')) {
    /**
     * true if page is search result page, false if not
     *
     * @param object|array|int|null $postId
     *
     * @return bool
     */
    function is_search_result_page($postId = null) {
        $id = null;

        if (is_object($postId) && isset($postId->ID)) {
            $id = $postId->ID;
        } elseif (is_array($postId) && array_key_exists('ID', $postId)) {
            $id = $postId['ID'];
        } else {
            $id = $postId;
        }

        if (!$id) {
            return false;
        }

        /**
         * @var FootageSearch
         */
        global $footage_search;


        return $footage_search->is_clips_page($id);
    }
}

if (!function_exists('get_clip_price_data')) {
    /**
     * get or output directly clip price data
     *
     * @param int $clipId
     * @param int $license
     * @param array $options
     * @param bool return
     *
     * @return array
     */
    function get_clip_price_data($clipId, $license, array $options = [], $return = true) {
        global $footagesearch_cart;

        return $footagesearch_cart->get_clip_price_data($clipId, $license, $options, $return);
    }
}

if (!function_exists('get_price_level_text')) {
    /**
     * get price level text value
     *
     * @param int $priceLevel
     *
     * @return string
     */
    function get_price_level_text($priceLevel) {
        global $footage_search;
        if (array_key_exists($priceLevel, $footage_search->prices_levels_names)) {
            return $footage_search->prices_levels_names[$priceLevel];
        }

        return '';
    }
}

if (!function_exists('is_rf_license_clip')) {
    /**
     * @param int $licenceId
     *
     * @return bool true if license is RF type license, false if not
     */
    function is_rf_license_clip($licenseId) {
        if ($licenseId == 1) {
            return true;
        }

        return false;
    }
}

if (!function_exists('get_license_name')) {
    /**
     * get license name by id
     *
     * @param int $licenseId
     *
     * @return string
     */
    function get_license_name($licenseId) {
        global $footage_search;
        if (array_key_exists($licenseId, $footage_search->licenses_names)) {
            return $footage_search->licenses_names[$licenseId];
        }

        return '';
    }
}

if (!function_exists('get_currency_mark')) {
    /**
     * @return string
     */
    function get_currency_mark() {
        return '$';
    }
}