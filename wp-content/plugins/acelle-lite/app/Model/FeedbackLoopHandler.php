<?php

/**
 * FeedbackLoopHandler class.
 *
 * Model class for feedback loop handler
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

class FeedbackLoopHandler extends DeliveryHandler
{
    /**
     * Process feedback message, extract the feedback information (comply to RFC).
     *
     * @return mixed
     */
    public function processMessage($mbox, $msgNo)
    {
        try {
            $header = imap_fetchheader($mbox, $msgNo);
            $body = imap_body($mbox, $msgNo, FT_PEEK);
            $feedbackType = $this->getFeedbackType($header);
            if (empty($feedbackType)) {
                imap_setflag_full($mbox, $msgNo, '\\Seen \\Flagged');
                throw new \Exception('not an ARF message. Skipped');
            }

            $msgId = $this->getMessageId($body);
            if (empty($msgId)) {
                imap_setflag_full($mbox, $msgNo, '\\Seen \\Flagged');
                throw new \Exception('cannot find Message-ID, skipped');
            }

            Log::info('Processing abuse notification for message '.$msgId);

            $trackingLog = TrackingLog::where('message_id', $msgId)->first();
            if (empty($trackingLog)) {
                imap_setflag_full($mbox, $msgNo, '\\Seen \\Flagged');
                throw new \Exception('message-id not found in tracking_logs, skipped');
            }

            // record a bounce log, one message may have more than one
            $feedbackLog = new FeedbackLog();
            $feedbackLog->message_id = $msgId;
            $feedbackLog->feedback_type = $feedbackType;
            $feedbackLog->raw_feedback_content = $header.PHP_EOL.$body;
            $feedbackLog->save();

            // just delete the bounce notification email
            imap_delete($mbox, $msgNo);

            Log::info('Feedback recorded for message '.$msgId);
        } catch (\Exception $ex) {
            Log::warning($ex->getMessage());
        }
    }
    
    /**
     * Extract FeedbackType from feedback email.
     *
     * @return mixed
     */
    public function getFeedbackType($header)
    {
        preg_match('/(?<=Feedback-Type:)\s*[^\s]*/', $header, $matched);
        if (sizeof($matched) == 0) {
            return;
        } else {
            return trim($matched[0]);
        }
    }

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::select('*');
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $query = self::select('feedback_loop_handlers.*');

        if ($request->user()->getOption('backend', 'fbl_handler_read') == 'own') {
            $query = $query->where('feedback_loop_handlers.user_id', '=', $request->user()->id);
        }

        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('feedback_loop_handlers.name', 'like', '%'.$keyword.'%')
                        ->orWhere('feedback_loop_handlers.type', 'like', '%'.$keyword.'%')
                        ->orWhere('feedback_loop_handlers.host', 'like', '%'.$keyword.'%');
                });
            }
        }

        // filters
        $filters = $request->filters;
        if (!empty($filters)) {
            if (!empty($filters['type'])) {
                $query = $query->where('feedback_loop_handlers.type', '=', $filters['type']);
            }
        }

        return $query;
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function search($request)
    {
        $query = self::filter($request);

        $query = $query->orderBy($request->sort_order, $request->sort_direction);

        return $query;
    }

    /**
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (FeedbackLoopHandler::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;

            // FeedbackLoopHandler custom order
            FeedbackLoopHandler::getAll()->increment('custom_order', 1);
            $item->custom_order = 0;
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'host', 'port', 'username', 'password', 'protocol',
    ];

    /**
     * Get validation rules.
     *
     * @return object
     */
    public static function rules()
    {
        return [
            'name' => 'required',
            'host' => 'required',
            'port' => 'required',
            'username' => 'required',
            'password' => 'required',
            'protocol' => 'required',
        ];
    }

    /**
     * Get select options.
     *
     * @return array
     */
    public static function getSelectOptions()
    {
        $query = self::getAll();

        $options = $query->orderBy('name', 'asc')->get()->map(function ($item) {
            return ['value' => $item->id, 'text' => $item->name];
        });

        return $options;
    }
    
    /**
     * Protocol select options.
     *
     * @return array
     */
    public static function protocolSelectOptions()
    {
        return [
            ['value' => 'imap', 'text' => 'imap']
        ];
    }
}