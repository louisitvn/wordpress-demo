<?php

/**
 * SendingServerSendmail class.
 *
 * Abstract class for Sendmail sending server
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
use Acelle\Library\Log;

class SendingServerSendmail extends SendingServer
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
            $transport = \Swift_SendmailTransport::newInstance($this->sendmail_path.' -bs');

            // Create the Mailer using your created Transport
            $mailer = \Swift_Mailer::newInstance($transport);

            // Actually send
            $sent = $mailer->send($message);

            if ($sent) {
                Log::info('Sent!');

                return array(
                    'runtime_message_id' => $sent['MessageId'],
                    'status' => self::DELIVERY_STATUS_SENT,
                );
            } else {
                Log::warning('Sending failed');

                return array(
                    'status' => self::DELIVERY_STATUS_FAILED,
                    'error' => 'Unknown SMTP error',
                );
            }
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
