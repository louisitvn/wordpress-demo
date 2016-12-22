<?php

/**
 * Setting class.
 *
 * Model class for applications settings
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   MVC Model
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

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        $settings = self::select('*')->get();
        $result = self::defaultSettings();

        foreach ($settings as $setting) {
            $result[$setting->name]['value'] = $setting->value;
        }

        return $result;
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function updateAll()
    {
        $settings = self::getAll();

        foreach ($settings as $key => $setting) {
            self::set($key, $setting['value']);
        }
    }

    /**
     * Get setting.
     *
     * @return object
     */
    public static function get($name)
    {
        $setting = self::where('name', $name)->first();
        if (is_object($setting)) {
            return $setting->value;
        } else {
            return self::defaultSettings()[$name]['value'];
        }
    }

    /**
     * Set setting value.
     *
     * @return object
     */
    public static function set($name, $val)
    {
        $option = self::where('name', $name)->first();

        if (is_object($option)) {
            $option->value = $val;
        } else {
            $option = new self();
            $option->name = $name;
            $option->value = $val;
        }
        $option->save();

        return $option;
    }

    /**
     * Get setting rules.
     *
     * @return object
     */
    public static function rules()
    {
        $rules = [];
        $settings = self::getAll();

        foreach ($settings as $name => $setting) {
            if (!isset($setting['not_required'])) {
                $rules[$name] = 'required';
            }
        }

        return $rules;
    }

    /**
     * Default setting.
     *
     * @return object
     */
    public static function defaultSettings()
    {
        return [
            'site_name' => [
                'cat' => 'general',
                'value' => 'Email Marketing Application',
                'type' => 'text',
            ],
            'site_keyword' => [
                'cat' => 'general',
                'value' => 'Email Marketing, Campaigns, Lists',
                'type' => 'text',
            ],
            'site_online' => [
                'cat' => 'general',
                'value' => 'true',
                'type' => 'checkbox',
                'options' => [
                    'false', 'true',
                ],
            ],
            'site_offline_message' => [
                'cat' => 'general',
                'value' => 'Application currently offline. We will come back soon!',
                'type' => 'textarea',
            ],
            'site_description' => [
                'cat' => 'general',
                'value' => 'Makes it easy for you to create, send, and optimize your email marketing campaigns.',
                'type' => 'textarea',
            ],
            'frontend_scheme' => [
                'cat' => 'general',
                'value' => 'default',
                'type' => 'select',
                'options' => self::colors(),
            ],
            'backend_scheme' => [
                'cat' => 'general',
                'value' => 'default',
                'type' => 'select',
                'options' => self::colors(),
            ],
            'sending_campaigns_at_once' => [
                'cat' => 'sending',
                'value' => '10',
                'type' => 'text',
                'class' => 'numeric',
            ],
            'sending_change_server_time' => [
                'cat' => 'sending',
                'value' => '300',
                'type' => 'text',
                'class' => 'numeric',
            ],
            'sending_emails_per_minute' => [
                'cat' => 'sending',
                'value' => '150',
                'type' => 'text',
                'class' => 'numeric',
            ],
            'sending_pause' => [
                'cat' => 'sending',
                'value' => '10',
                'type' => 'text',
                'class' => 'numeric',
            ],
            'sending_at_once' => [
                'cat' => 'sending',
                'value' => '50',
                'type' => 'text',
                'class' => 'numeric',
            ],
            'sending_subscribers_at_once' => [
                'cat' => 'sending',
                'value' => '100',
                'type' => 'text',
                'class' => 'numeric',
            ],
            'url_unsubscribe' => [
                'cat' => 'url',
                'value' => '', // action('CampaignController@unsubscribe', ["message_id" => trans("messages.MESSAGE_ID")]),
                'type' => 'text',
                'not_required' => true,
            ],
            'url_open_track' => [
                'cat' => 'url',
                'value' => '', // action('CampaignController@open', ["message_id" => trans("messages.MESSAGE_ID")]),
                'type' => 'text',
                'not_required' => true,
            ],
            'url_click_track' => [
                'cat' => 'url',
                'value' => '', // action('CampaignController@click', ["message_id" => trans("messages.MESSAGE_ID"), "url" => trans("messages.URL")]),
                'type' => 'text',
                'not_required' => true,
            ],
            'url_delivery_handler' => [
                'cat' => 'url',
                'value' => '', // action('DeliveryController@notify'),
                'type' => 'text',
                'not_required' => true,
            ],
            'url_update_profile' => [
                'cat' => 'url',
                'value' => '', // action('DeliveryController@notify'),
                'type' => 'text',
                'not_required' => true,
            ],
        ];
    }

    /**
     * Color array.
     *
     * @return array
     */
    public static function colors()
    {
        return [
            ['value' => 'default', 'text' => trans('messages.default')],
            ['value' => 'blue', 'text' => trans('messages.blue')],
            ['value' => 'green', 'text' => trans('messages.green')],
            ['value' => 'brown', 'text' => trans('messages.brown')],
            ['value' => 'pink', 'text' => trans('messages.pink')],
            ['value' => 'grey', 'text' => trans('messages.grey')],
            ['value' => 'white', 'text' => trans('messages.white')],
        ];
    }
    
    /**
     * Update setting one line.
     *
     * @return void
     */
    public static function setEnv($key, $value)
    {
        $file_path = base_path('.env');
        $data = file($file_path);
        $data = array_map(function($data) use ($key, $value) {
            if (stristr($value, " ")) {
                return stristr($data, $key) ? "$key='$value'\n" : $data;
            } else {
                return stristr($data, $key) ? "$key=$value\n" : $data;
            }
        }, $data);
        
        // Write file
        $env_file = fopen($file_path, 'w') or die('Unable to open file!');
        fwrite($env_file, implode($data, ""));
        fclose($env_file);
    }
}
