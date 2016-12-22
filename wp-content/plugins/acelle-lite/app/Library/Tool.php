<?php

/**
 * Tool class.
 *
 * Misc helper tool
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   Acelle Library
 *
 * @author     N. Pham <n.pham@acellemail.com>
 * @author     L. Pham <l.pham@acellemail.com>
 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Library;

class Tool
{
    /**
     * Copy a file, or recursively copy a folder and its contents.
     *
     * @param string $source      Source path
     * @param string $dest        Destination path
     * @param int    $permissions New folder creation permissions
     *
     * @return bool Returns true on success, false on failure
     */
    public static function xcopy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            self::xcopy("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();

        return true;
    }

    /**
     * Delete a file, or recursively delete a folder and its contents.
     *
     * @param string $source Source path
     *
     * @return bool Returns true on success, false on failure
     */
    public static function xdelete($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($dir.'/'.$object)) {
                        self::xdelete($dir.'/'.$object);
                    } else {
                        unlink($dir.'/'.$object);
                    }
                }
            }
            rmdir($dir);
        }

        return true;
    }

    /**
     * Get all time zone.
     *
     * @var array
     */
    public static function allTimeZones()
    {
        // Get all time zones with offset
        $zones_array = array();
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['zone'] = $zone;
            $zones_array[$key]['text'] = '(GMT'.date('P', $timestamp).') '.$zones_array[$key]['zone'];
            $zones_array[$key]['order'] = str_replace('-', '1', str_replace('+', '2', date('P', $timestamp))).$zone;
        }

        // sort by offset
        usort($zones_array, function ($a, $b) {
            return strcmp($a['order'], $b['order']);
        });

        return $zones_array;
    }

    /**
     * Get options array for select box.
     *
     * @var array
     */
    public static function getTimezoneSelectOptions()
    {
        $arr = [];
        foreach (self::allTimeZones() as $timezone) {
            $row = ['value' => $timezone['zone'], 'text' => $timezone['text']];
            $arr[] = $row;
        }

        return $arr;
    }

    /**
     * Format display datetime.
     *
     * @var string
     */
    public static function formatDateTime($datetime)
    {
        $result = self::dateTime($datetime)->format(trans('messages.datetime_format'));

        return $result;
    }

    /**
     * Format display datetime.
     *
     * @var string
     */
    public static function dateTime($datetime)
    {
        $timezone = \Auth::user()->getTimezone();
        $result = $datetime;
        if (!empty($timezone)) {
            $result = $result->timezone($timezone);
        }

        return $result;
    }

    /**
     * For mat human time.
     *
     * @param       DateTime
     *
     * @return string
     */
    public static function formatHumanTime($time)
    {
        return $time->diffForHumans();
    }

    /**
     * Change singular to plural.
     *
     * @param       string
     *
     * @return string
     */
    public static function getPluralPrase($phrase, $value)
    {
        $plural = '';
        if ($value > 1) {
            for ($i = 0; $i < strlen($phrase); ++$i) {
                if ($i == strlen($phrase) - 1) {
                    $plural .= ($phrase[$i] == 'y') ? 'ies' : (($phrase[$i] == 's' || $phrase[$i] == 'x' || $phrase[$i] == 'z' || $phrase[$i] == 'ch' || $phrase[$i] == 'sh') ? $phrase[$i].'es' : $phrase[$i].'s');
                } else {
                    $plural .= $phrase[$i];
                }
            }

            return $plural;
        }

        return $phrase;
    }

    /**
     * Get file/folder permissions.
     *
     * @param       string
     *
     * @return string
     */
    public static function getPerms($path)
    {
        return substr(sprintf('%o', fileperms($path)), -4);
    }
    
    /**
     * Get system time conversion.
     *
     * @param       string
     *
     * @return string
     */
    public static function systemTime($time)
    {
        return $time->setTimezone(config('app.timezone'));
    }
    
    /**
     * Get bytes from string.
     *
     * @param string
     *
     * @return string
     */
    public static function returnBytes($val) {
        //$val = trim($val);
        //$last = strtolower($val[strlen($val)-1]);
        //switch($last) 
        //{
        //    case 'g':
        //    $val *= 1024;
        //    case 'm':
        //    $val *= 1024;
        //    case 'k':
        //    $val *= 1024;
        //}
        return $val;
    }
    
    /**
     * Get max upload file.
     *
     * @param string
     *
     * @return string
     */
    public static function maxFileUploadInBytes() {
        //select maximum upload size
        $max_upload = self::returnBytes(ini_get('upload_max_filesize'));
        //select post limit
        $max_post = self::returnBytes(ini_get('post_max_size'));
        //select memory limit
        $memory_limit = self::returnBytes(ini_get('memory_limit'));
        // return the smallest of them, this defines the real limit
        return min($max_upload, $max_post);
    }
}
