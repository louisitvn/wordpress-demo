<?php

/**
 * SendingServerMailgunApi class.
 *
 * Abstract class for Mailgun API sending server
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

use Acelle\Library\Log;
use Acelle\Library\StringHelper;

class SendingServerMailgunApi extends SendingServerMailgun
{
    protected $table = 'sending_servers';
    
    /**
     * Send the provided message.
     *
     * @return boolean
     * @param message
     */
    public function send($message, $params = array())
    {
        try {
            $this->setupWebhooks($this->domain);
            $result = $this->client()->sendMessage(
                $this->domain, array(
                    'from' => $params['from_email'],
                    'to' => $params['to'],
                    //'to' => 'em3ng0384304893@gmail.com'
                ),
                $message->toString()
            );

            Log::info('Sent!');

            return array(
                'runtime_message_id' => StringHelper::cleanupMessageId($result->http_response_body->id),
                'status' => self::DELIVERY_STATUS_SENT,
            );
        } catch (\Exception $e) {
            Log::warning('Sending failed');
            Log::warning($e->getMessage());

            return array(
                'status' => self::DELIVERY_STATUS_FAILED,
                'error' => $e->getMessage(),
            );
        }
    }
}
