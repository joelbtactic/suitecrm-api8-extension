<?php

function translate_by_lang($string, $mod = '', $selectedValue = '', $lang = '')
{
    //$test_start = microtime();
    //static $mod_strings_results = array();
    if (!empty($mod)) {
        global $current_language;
        if ($lang != ''){
            $current_language = $lang;
        }
        //Bug 31275
        else if (isset($_REQUEST['login_language'])) {
            $current_language = ($_REQUEST['login_language'] == $current_language) ? $current_language : $_REQUEST['login_language'];
        }
        $mod_strings = return_module_language($current_language, $mod);
        if ($mod == '') {
            echo 'Language is <pre>' . $mod_strings . '</pre>';
        }
    } else {
        global $mod_strings;
    }

    $returnValue = '';
    $app_list_strings = return_app_list_strings_language($current_language);
    $app_strings = return_application_language($current_language);

    if (isset($mod_strings[$string])) {
        $returnValue = $mod_strings[$string];
    } elseif (isset($app_strings[$string])) {
        $returnValue = $app_strings[$string];
    } elseif (isset($app_list_strings[$string])) {
        $returnValue = $app_list_strings[$string];
    } elseif (isset($app_list_strings['moduleList']) && isset($app_list_strings['moduleList'][$string])) {
        $returnValue = $app_list_strings['moduleList'][$string];
    }

    if (empty($returnValue)) {
        return $string;
    }

    // Bug 48996 - Custom enums with '0' value were not returning because of empty check
    // Added a numeric 0 checker to the conditional to allow 0 value indexed to pass
    if (is_array($returnValue) && (!empty($selectedValue) || (is_numeric($selectedValue) && $selectedValue == 0)) && isset($returnValue[$selectedValue])) {
        return $returnValue[$selectedValue];
    }

    return $returnValue;
}

/**
 * This function retrieves an application language file and returns the array of strings included in the $app_list_strings var.
 *
 * @param string $language specific language to load
 *
 * @return array lang strings
 */
function return_app_list_strings_language_by_lang($language)
{
    global $app_list_strings;
    global $sugar_config;

    $cache_key = 'app_list_strings_by_lang.' . $language;

    // Check for cached value
    $cache_entry = sugar_cache_retrieve($cache_key);
    if (!empty($cache_entry)) {
        return $cache_entry;
    }

    $default_language = isset($sugar_config['default_language']) ? $sugar_config['default_language'] : 'en_us';
    $temp_app_list_strings = $app_list_strings;

    $langs = array();
    if ($language != 'en_us') {
        $langs[] = 'en_us';
    }
    if ($default_language != 'en_us' && $language != $default_language) {
        $langs[] = $default_language;
    }
    $langs[] = $language;

    $app_list_strings_array = array();

    foreach ($langs as $lang) {
        $app_list_strings = array();
        if (file_exists("include/language/$lang.lang.php")) {
            include "include/language/$lang.lang.php";
            $GLOBALS['log']->info("Found language file: $lang.lang.php");
        }
        if (file_exists("include/language/$lang.lang.override.php")) {
            include "include/language/$lang.lang.override.php";
            $GLOBALS['log']->info("Found override language file: $lang.lang.override.php");
        }
        if (file_exists("include/language/$lang.lang.php.override")) {
            include "include/language/$lang.lang.php.override";
            $GLOBALS['log']->info("Found override language file: $lang.lang.php.override");
        }
        if (file_exists("custom/application/Ext/Language/$lang.lang.ext.php")) {
            include "custom/application/Ext/Language/$lang.lang.ext.php";
            $GLOBALS['log']->info("Found extended language file: $lang.lang.ext.php");
        }
        if (file_exists("custom/include/language/$lang.lang.php")) {
            include "custom/include/language/$lang.lang.php";
            $GLOBALS['log']->info("Found custom language file: $lang.lang.php");
        }

        $app_list_strings_array[] = $app_list_strings;
    }

    $app_list_strings = array();
    foreach ($app_list_strings_array as $app_list_strings_item) {
        $app_list_strings = sugarLangArrayMerge($app_list_strings, $app_list_strings_item);
    }

    if (!isset($app_list_strings)) {
        $GLOBALS['log']->fatal("Unable to load the application language file for the selected language ($language) or the default language ($default_language) or the en_us language");

        return;
    }

    $return_value = $app_list_strings;
    $app_list_strings = $temp_app_list_strings;

    sugar_cache_put($cache_key, $return_value);

    return $return_value;
}