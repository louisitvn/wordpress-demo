<?php

/**
 * Campaign class.
 *
 * Model class for campaigns related functionalities.
 * This is the center of the application
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
use Acelle\Library\Log as MailLog;
use Acelle\Library\StringHelper;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use DB;
use Carbon\Carbon;
use Acelle\Model\SystemJob;

class Campaign extends Model
{
    // Campaign status
    const STATUS_NEW = 'new';
    const STATUS_READY = 'ready'; // equiv. to 'queue'
    const STATUS_SENDING = 'sending';
    const STATUS_ERROR = 'error';
    const STATUS_DONE = 'done';
    const STATUS_PAUSED = 'paused';

    // Campaign types
    const TYPE_REGULAR = 'regular';
    const TYPE_PLAIN_TEXT = 'plain-text';

    // Campaign settings
    const WORKER_DELAY = 1;
    const DKIM_SELECTOR = 'mailer';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'run_at'];

    public static $required_tags = array(
        array('name' => '{UNSUBSCRIBE_URL}', 'required' => true),
    );

    /**
     * Get the user that owns the phone.
     */
    public function mailList()
    {
        return $this->belongsTo('Acelle\Model\MailList');
    }

    /**
     * Get the segment of the campaign.
     */
    public function segment()
    {
        return $this->belongsTo('Acelle\Model\Segment');
    }

    /**
     * Get the links for campaign.
     */
    public function links()
    {
        return $this->belongsToMany('Acelle\Model\Link', 'campaign_links');
    }

    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo('Acelle\Model\User');
    }
    
    /**
     * Get campaign tracking logs.
     *
     * @return mixed
     */
    public function trackingLogs()
    {
        return $this->hasMany('Acelle\Model\TrackingLog');
    }
    
    /**
     * Get campaign bounce logs.
     *
     * @return mixed
     */
    public function bounceLogs()
    {
        return BounceLog::select('bounce_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'bounce_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }
    
    /**
     * Get campaign open logs.
     *
     * @return mixed
     */
    public function openLogs()
    {
        return OpenLog::select('open_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }
    
    /**
     * Get campaign click logs.
     *
     * @return mixed
     */
    public function clickLogs()
    {
        return ClickLog::select('click_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }
    
    /**
     * Get campaign feedback loop logs.
     *
     * @return mixed
     */
    public function feedbackLogs()
    {
        return FeedbackLog::select('feedback_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'feedback_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }
    
    /**
     * Get campaign unsubscribe logs.
     *
     * @return mixed
     */
    public function unsubscribeLogs()
    {
        return UnsubscribeLog::select('unsubscribe_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'unsubscribe_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }

    /**
     * Reset max_execution_time so that command can run for a long time without being terminated
     *
     * @return mixed
     */
    public static function resetMaxExecutionTime() {
        try {
            set_time_limit(0);
            ini_set('max_execution_time', 0);
        } catch (\Exception $e) {
            MailLog::warning('Cannot reset max_execution_time: '.$e->getMessage());
        }
    }

    /**
     * Start the campaign
     * 
     */
    public function start() {
        // Reserve: mark the campaign as sending, prevent other processes from sending it again
        try {
            $this->sending();
            MailLog::info('Starting campaign `'.$this->name.'`');

            // Reset max_execution_time so that command can run for a long time without being terminated
            self::resetMaxExecutionTime();

            // Only run multi-process if pcntl is enabled
            if (extension_loaded('pcntl')) {
                $this->runMultiProcesses();
            } else {
                MailLog::warning('Cannot fork processes');
                $this->run();
            }
            MailLog::info('Finish campaign `'.$this->name.'`');

            // runMultiProcesses() and run() are responsible for setting DONE status
        } catch (\Exception $ex) {
            // Set ERROR status
            $this->error($ex->getMessage());
            MailLog::error('Campaign failed. '.$ex->getMessage());
        }
    }

    /**
     * Mark the campaign as 'done' or 'sent'.
     */
    public function done()
    {
        $this->status = self::STATUS_DONE;
        $this->save();
    }

    /**
     * Mark the campaign as 'sending'.
     */
    public function sending()
    {
        $this->status = self::STATUS_SENDING;
        $this->delivery_at = \Carbon\Carbon::now();
        $this->save();
    }

    /**
     * Mark the campaign as 'ready' (which is equiv. to 'queued')
     */
    public function ready()
    {
        $this->status = self::STATUS_READY;
        $this->save();
    }

    /**
     * Mark the campaign as 'done' or 'sent'.
     */
    public function error($error = NULL)
    {
        $this->status = self::STATUS_ERROR;
        $this->last_error = $error;
        $this->save();
    }

    /**
     * Mark the campaign as 'done' or 'sent'.
     */
    public function refreshStatus()
    {
        $me = self::find($this->id);
        $this->status = $me->status;
        $this->save();
    }
    
    /**
     * Get campaign's email chunks.
     *
     * @return mixed
     */
    public function getChunks($collection, $count)
    {
        $result = [];
        if (sizeof($collection) == 0) {
            return $result;
        }
        $chunks = array_chunk(range(0, sizeof($collection) - 1), ceil(sizeof($collection) / (float) $count), true);
        foreach ($chunks as $key => $chunk) {
            $list = [];
            foreach (array_values($chunk) as $i) {
                $list[] = $collection[$i];
            }
            $result[] = $list;
        }

        return $result;
    }

    /**
     * Start the campaign, using PHP fork() to launch multiple processes.
     *
     * @return mixed
     */
    public function runMultiProcesses()
    {
        $count = (int) $this->user->getOption('frontend', 'max_process');
        $subscribers = $this->getPendingSubscribers();
        MailLog::info(sizeof($subscribers).' subscriber(s) selected');
        
        // split the big subscribers list into smaller ones
        // this is also the number of processes to fork
        $chunks = $this->getChunks($subscribers, $count);

        MailLog::info('Forking '.sizeof($chunks).' process(es)');
        $parentPid = getmypid();
        for ($i = 0; $i < sizeof($chunks); ++$i) {
            $pid = pcntl_fork();
            
            // for child process only
            if (!$pid) {
                // Reconnect to the DB to prevent connection closed issue when using fork
                DB::reconnect('mysql');
                
                // Re-initialize logging to capture the child process' PID
                MailLog::fork();

                MailLog::info('Start child process '.($i + 1).' of '.sizeof($chunks).' (forked from '.$parentPid.')');
                sleep(self::WORKER_DELAY);
                $this->run($chunks[$i]);
                exit($i + 1);
                // end child process
            }
        }
        
        // wait for child processes to finish
        while (pcntl_waitpid(0, $status) != -1) {
            $status = pcntl_wexitstatus($status);
            MailLog::info("Child $status completed");
        }
        
        // after all child processes are done
        $this->refreshStatus();
        
        // If all child processes finish sucessfully, just mark campaign as done
        // Otherwise, mark campaign with error status (by child process)
        //
        // There are conventions here: 
        //   + Child process does not update status from SENDING to DONE, it is left for the parent process
        //   + Child process only updates status from SENDING to ERROR
        //   + Parent process only updates status to DONE when current status is SENDING
        //     indicating that all child processes finish sucessfully
        //   + In case one child process update the status from SENDING to ERROR, it is left as the final status
        //     the latest error is kept
        if ($this->status == self::STATUS_SENDING) {
            $this->done();
        } else {
            // leave the error reported by child process
        }
    }

    /**
     * Send Campaign
     * Iterate through all subscribers and send email.
     */
    public function run($subscribers = NULL)
    {
        // check if the method is trigger by a child process (triggered by startMultiProcess method)
        $asChildProcess = !($subscribers == NULL);
        
        // try/catch to make sure child process does not stop without reporting any error
        try {
            if (!$asChildProcess) {
                $subscribers = $this->getPendingSubscribers();
            }

            $i = 0;
            foreach ($subscribers as $subscriber) {
                if ($this->user->overQuota()) {
                    throw new \Exception("Customer (ID: {$this->user->id}) has reached sending quota");
                }

                $i += 1;
                MailLog::info("Sending to subscriber `{$subscriber->email}` ({$i}/".sizeof($subscribers).')');

                // Pick up an available sending server
                // Throw exception in case no server available
                $server = $this->pickServer($this);

                list($message, $msgId) = $this->prepareEmail($subscriber, $server);

                $sent = $server->send($message, array(
                    'from_email' => $this->from_email,
                    'to' => $subscriber->email, // workaround: some sending engine requires the TO explicitly (not in MIME message)
                    'campaign' => $this,
                    'subscriber' => $subscriber,
                    'msgId' => $msgId
                ));

                $this->trackMessage($sent, $subscriber, $server, $msgId);
            }

            // only mark campaign as done when running as its own process
            // as a child process, just finish and leave the parent process to update campaign status
            if (!$asChildProcess) {
                $this->done();
            }

            $this->user->saveQuotaUsageInfo();
        } catch (\Exception $e) {
            MailLog::error($e->getMessage());
            $this->error($e->getMessage());
        } finally {
            // reset server pools: just in case DeliveryServerAmazonSesWebApi
            // --> setup SNS requires using from_email of the corresponding campaign
            // but SNS is only made once when the server is initiated
            //     SendingServer::resetServerPools();
        }
    }

    /**
     * Log delivery message, used for later tracking.
     *
     */
    private function trackMessage($response, $subscriber, $server, $msgId)
    {
        $params = array_merge(array(
                'campaign_id' => $this->id,
                'message_id' => $msgId,
                'subscriber_id' => $subscriber->id,
                'sending_server_id' => $server->id,
                'user_id' => $this->user->id,
            ), $response);

        if (!isset($params['runtime_message_id'])) {
            $params['runtime_message_id'] = $msgId;
        }
        
        // create tracking log for message
        TrackingLog::create($params);

        // increment user quota usage
        $this->user->countUsage();
        $this->user->quotaDebug();
    }

    /**
     * Get tagged Subject
     *
     * @return String
     */
    public function getSubject($subscriber, $msgId) {
        return $this->tagMessage($this->subject, $subscriber, $msgId);
    }
    
    /**
     * Pick up a delivery server for the campaign.
     *
     * @return mixed
     */
    public function pickServer($campaign)
    {
        return SendingServer::pickServer($campaign);
    }

    /**
     * Transform Tags
     * Transform tags to actual values before sending.
     */
    private function tagMessage($message, $subscriber, $msgId)
    {
        // @todo consider a solution for UNSUBSCRIBE_URL for test subscriber (also for other tags like: UPDATE_PROFILE_URL)

        $tags = array(
            'SUBSCRIBER_EMAIL' => $subscriber->email,            
            'CAMPAIGN_NAME' => $this->name,
            'CAMPAIGN_UID' => $this->uid,
            'CAMPAIGN_SUBJECT' => $this->subject,
            'CAMPAIGN_FROM_EMAIL' => $this->from_email,
            'CAMPAIGN_FROM_NAME' => $this->from_name,
            'CAMPAIGN_REPLY_TO' => $this->reply_to,
            'SUBSCRIBER_UID' => $subscriber->uid,
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DAY' => date('d'),
            'UNSUBSCRIBE_URL' => str_replace('MESSAGE_ID', StringHelper::base64UrlEncode($msgId), Setting::get('url_unsubscribe')),
            'CONTACT_NAME' => $this->mailList->contact->company,
            'CONTACT_COUNTRY' => $this->mailList->countryName(),
            'CONTACT_STATE' => $this->mailList->contact->state,
            'CONTACT_CITY' => $this->mailList->contact->city,
            'CONTACT_ADDRESS_1' => $this->mailList->contact->address_1,
            'CONTACT_ADDRESS_2' => $this->mailList->contact->address_2,
            'CONTACT_PHONE' => $this->mailList->contact->phone,
            'CONTACT_URL' => $this->mailList->contact->url,
            'CONTACT_EMAIL' => $this->mailList->contact->email,
            'LIST_NAME' => $this->mailList->name,
            'LIST_SUBJECT' => $this->mailList->default_subject,
            'LIST_FROM_NAME' => $this->mailList->from_name,
            'LIST_FROM_EMAIL' => $this->mailList->from_email,
        );
        
        // UPDATE_PROFILE_URL
        if(!$this->isStdClassSubscriber($subscriber)) {
            // in case of actually sending campaign
            $tags['UPDATE_PROFILE_URL'] = str_replace('LIST_UID', $this->mailList->uid,
                str_replace('SUBSCRIBER_UID', $subscriber->uid,
                str_replace('SECURE_CODE', $subscriber->getSecurityToken('update-profile'), Setting::get('url_update_profile'))));
        }

        // Update tags layout
        foreach ($tags as $tag => $value) {
            $message = str_replace('{'.$tag.'}', $value, $message);
        }

        if (!$this->isStdClassSubscriber($subscriber)) {
            // in case of actually sending campaign
            foreach ($this->mailList->fields as $field) {
                $message = str_replace('{SUBSCRIBER_'.$field->tag.'}', $subscriber->getValueByField($field), $message);
            }
        } else {
            // in case of sending test email
            // @todo how to manage such tags?
            $message = str_replace('{SUBSCRIBER_EMAIL}', $subscriber->email, $message);
        }

        return $message;
    }

    /**
     * Get Pending Subscribers
     * Select only subscribers that are ready for sending. Those whose status is `blacklisted`, `pending` or `unconfirmed` are not included.
     */
    public function getPendingSubscribers()
    {
        if (is_object($this->segment)) {
            $scoped = $this->segment;
        } else {
            $scoped = $this->mailList;
        }

        return $scoped->subscribers()
                ->leftJoin(DB::raw('(SELECT * FROM '.DB::getTablePrefix().'tracking_logs WHERE campaign_id = '.$this->id.') log'), 'subscribers.id', '=', 'subscriber_id')
                ->whereRaw('log.id IS NULL')
                ->whereRaw(DB::getTablePrefix()."subscribers.status = '".Subscriber::STATUS_SUBSCRIBED."'")
                ->select('subscribers.*')
                ->get();
    }

    /**
     * Append Open Tracking URL
     * Append open-tracking URL to every email message.
     */
    public function appendOpenTrackingUrl($body, $msgId)
    {
        $openTrackingBaseURL = Setting::get('url_open_track');
        $tracking_url = str_replace('MESSAGE_ID', StringHelper::base64UrlEncode($msgId), $openTrackingBaseURL);

        return $body.'<img src="'.$tracking_url.'" width="0" height="0" style="visibility:hidden" />';
    }

    /**
     * Build Email Custom Headers
     *
     * @return Hash list of custom headers
     */
    public function getCustomHeaders($subscriber, $server) {
        $msgId = StringHelper::generateMessageId(StringHelper::getDomainFromEmail($this->from_email));

        return array(
            'X-Acelle-Campaign-Id' => $this->uid,
            'X-Acelle-Subscriber-Id' => $subscriber->uid,
            'X-Acelle-User-Id' => $this->user->uid,
            'X-Acelle-Message-Id' => $msgId,
            'X-Acelle-Sending-Server-Id' => $server->uid,
            'List-Unsubscribe' => '<'.str_replace('MESSAGE_ID', StringHelper::base64UrlEncode($msgId), Setting::get('url_unsubscribe')).'>',
            'Precedence' => 'bulk'
        );
    }
    
    /**
     * Build Email HTML content
     *
     * @return String
     */
    public function getHtmlContent($subscriber, $msgId) {
        // @note: IMPORTANT: the order must be as follows
        // * addTrackingURL
        // * appendOpenTrackingUrl
        // * tagMessage

        // @note: addTrackingUrl() must go before appendOpenTrackingUrl()
        $body = $this->html;
        
        // Enable click tracking
        if ($this->track_click) {
            $body = $this->addTrackingUrl($body, $msgId);
        }
        
        // Enable open tracking
        if ($this->track_open) {
            $body = $this->appendOpenTrackingUrl($body, $msgId);
        }
        
        // Transform tags
        $body = $this->tagMessage($body, $subscriber, $msgId);
        
        // Transform CSS/HTML content to inline CSS
        $body = $this->inlineHtml($body);

        return $body;
    }

    /**
     * Build Email HTML content
     *
     * @return String
     */
    public function getPlainContent($subscriber, $msgId) {
        $plain = $this->tagMessage($this->plain, $subscriber, $msgId);

        return $plain;
    }

    /**
     * Prepare the email content using Swift Mailer.
     *
     * @input object subscriber
     * @input object sending server
     *
     * @return MIME text message
     */
    private function prepareEmail($subscriber, $server)
    {
        // build the message
        $customHeaders = $this->getCustomHeaders($subscriber, $this);
        $msgId = $customHeaders['X-Acelle-Message-Id'];

        $message = \Swift_Message::newInstance();
        $message->setId($msgId);
        
        if ($this->type == self::TYPE_REGULAR) {
            $message->setContentType('text/html; charset=utf-8');
        } else {
            $message->setContentType('text/plain; charset=utf-8');
        }

        foreach($customHeaders as $key => $value) {
            $message->getHeaders()->addTextHeader($key, $value);
        }

        // @TODO for AWS, setting returnPath requires verified domain or email address
        //$message->setReturnPath($server->getVerp($subscriber->email));
        $message->setSubject($this->getSubject($subscriber, $msgId));
        $message->setFrom(array($this->from_email => $this->from_name));
        $message->setTo($subscriber->email);
        $message->setReplyTo($this->reply_to);
        $message->setEncoder(\Swift_Encoding::get8bitEncoding());
        $message->setBody($this->getPlainContent($subscriber, $msgId), 'text/plain');
        if ($this->type == self::TYPE_REGULAR) {
            $message->addPart($this->getHtmlContent($subscriber, $msgId), 'text/html');
        }
        
        if ($this->sign_dkim) {
            $message = $this->sign($message);
        }

        // @todo attachment
        //$message->attach(Swift_Attachment::fromPath('/tmp/gaugau.csv'));
        return array($message, $msgId);
    }

    /**
     * Find sending domain from email.
     *
     * @return mixed
     */
    private function findSendingDomain($email)
    {
        $domain = substr(strrchr($email, '@'), 1);

        return SendingDomain::where('name', $domain)->first();
    }
    
    /**
     * Sign the message with DKIM.
     *
     * @return mixed
     */
    private function sign($message)
    {
        $sendingDomain = $this->findSendingDomain($this->from_email);

        if (empty($sendingDomain)) {
            return $message;
        }

        $privateKey = $sendingDomain->dkim_private;
        $domainName = $sendingDomain->name;
        $selector = self::DKIM_SELECTOR;
        $signer = new \Swift_Signers_DKIMSigner($privateKey, $domainName, $selector);
        $signer->ignoreHeader('Return-Path');
        $message->attachSigner($signer);

        return $message;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'mail_list_id', 'segment_id',
        'subject', 'from_name', 'from_email',
        'reply_to', 'track_open',
        'track_click', 'sign_dkim', 'track_fbl',
        'html', 'plain', 'template_source',
    ];

    /**
     * The rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'mail_list_uid' => 'required',
    );
    
    /**
     * The rules for validation.
     *
     * @var array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::select('campaigns.*');
    }

    /**
     * Get select options.
     *
     * @return array
     */
    public static function getSelectOptions($user = null, $status = null)
    {
        $query = self::getAll();
        if (is_object($user)) {
            $query = $query->where('user_id', '=', $user->id);
        }
        if (isset($status)) {
            $query = $query->where('status', '=', $status);
        }
        $options = $query->orderBy('created_at', 'DESC')->get()->map(function ($item) {
            return ['value' => $item->uid, 'text' => $item->name];
        });

        return $options;
    }

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
            while (Campaign::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;

            // Update custom order
            Campaign::getAll()->increment('custom_order', 1);
            $item->custom_order = 0;
        });
        
        // Created
        static::created(function ($item) {
            // Update links
            $item->updateLinks();
        });

        static::updating(function ($item) {
            // Update links
            $item->updateLinks();
        });
    }

    /**
     * Get current links of campaign.
     */
    public function getLinks()
    {
        return $this->links()->whereIn('url', $this->getUrls())->get();
    }

    /**
     * Get urls from campaign html.
     */
    public function getUrls()
    {
        // Find all links in campaign content
        preg_match_all('/<a[^>]*href=["\'](?<url>http[^"\']*)["\']/i', $this->html, $matches);
        $hrefs = array_unique($matches['url']);

        $urls = [];
        foreach ($hrefs as $href) {
            if (preg_match('/^http/i', $href) && strpos($href, '{UNSUBSCRIBE_URL}') === false) {
                $urls[] = strtolower(trim($href));
            }
        }

        return $urls;
    }

    /**
     * Update campaign links.
     */
    public function updateLinks()
    {
        foreach ($this->getUrls() as $url) {
            $link = Link::where('url', '=', $url)->first();
            if (!is_object($link)) {
                $link = new Link();
                $link->url = $url;
                $link->save();
            }

            // Campaign link
            if ($this->links()->where('url', '=', $url)->count() == 0) {
                $cl = new CampaignLink();
                $cl->campaign_id = $this->id;
                $cl->link_id = $link->id;
                $cl->save();
            }
        }
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
     * CHeck UNSUBSCRIBE_URL.
     *
     * @return object
     */
    public function unsubscribe_url_valid()
    {
        if($this->type != 'plain-text' &&
           \Auth::user()->getOption('frontend', 'unsubscribe_url_required') == 'yes' &&
            strpos($this->html, '{UNSUBSCRIBE_URL}') == false   
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get max step.
     *
     * @return object
     */
    public function step()
    {
        $step = 0;

        // Step 1
        if (is_object($this->mailList)) {
            $step = 1;
        } else {
            return $step;
        }

        // Step 2
        if (!empty($this->name) && !empty($this->subject) && !empty($this->from_name)
                && !empty($this->from_email) && !empty($this->reply_to)) {
            $step = 2;
        } else {
            return $step;
        }

        // Step 3
        if ((!empty($this->html) || $this->type == 'plain-text') && !empty($this->plain) && $this->unsubscribe_url_valid()) {            
            $step = 3;
        } else {
            return $step;
        }

        // Step 4
        if (isset($this->run_at) && $this->run_at != '0000-00-00 00:00:00') {
            $step = 4;
        } else {
            return $step;
        }
        
        // Step 5
        if ($this->subscribers()->count()) {
            $step = 5;
        } else {
            return $step;
        }

        return $step;
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $user = $request->user();
        $query = self::where('user_id', '=', $user->id);

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('name', 'like', '%'.$request->keyword.'%');
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
     * Subscribers.
     *
     * @return collect
     */
    public function subscribers($params=[])
    {
        if(!is_object($this->segment) && !is_object($this->mailList)) {
            return collect([]);
        }
        
        if (is_object($this->segment)) {
            $query = $this->segment->subscribers();
        } elseif (is_object($this->mailList)) {
            $query = $this->mailList->subscribers();
        }
        
        $query = $query->select("subscribers.*");
        
        // Filter
        $filters = isset($params["filters"]) ? $params["filters"] : null;
        
        if(isset($filters) || $this->status == "done") {
            $query = $query->leftJoin('tracking_logs', 'tracking_logs.subscriber_id', '=', 'subscribers.id');
        }
        
        if(isset($filters)) {            
            if(isset($filters["open"])) {
                $equal = ($filters["open"] == "opened") ? "!=" : "=";                
                $query = $query->leftJoin('open_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
                    ->where('open_logs.id', $equal, null);
            }
            if(isset($filters["click"])) {
                $equal = ($filters["click"] == "clicked") ? "!=" : "=";
                $query = $query->leftJoin('click_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')
                    ->where('click_logs.id', $equal, null);
            }
            if(isset($filters["tracking_status"])) {
                $val = ($filters["tracking_status"] == "not_sent") ? null : $filters["tracking_status"];
                $query = $query->where('tracking_logs.status', "=", $val);
            }
        }
        
        // keyword
        if (isset($params["keyword"]) && !empty(trim($params["keyword"]))) {
            foreach (explode(' ', trim($params["keyword"])) as $keyword) {
                $query = $query->leftJoin('subscriber_fields', 'subscribers.id', '=', 'subscriber_fields.subscriber_id');
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('subscribers.email', 'like', '%'.$keyword.'%')
                        ->orWhere('subscriber_fields.value', 'like', '%'.$keyword.'%');
                });
            }
        }
        
        // when status == done
        if($this->status == "done") {            
            $query = $query->where('tracking_logs.campaign_id', "=", $this->id);
            $query = $query->where('tracking_logs.id', "!=", "NULL");
        }

        return $query->distinct();
    }

    /**
     * Create customer action log.
     *
     * @param string $cat
     * @param User   $user
     * @param array  $add_datas
     */
    public function log($name, $user, $add_datas = [])
    {
        $data = [
                'id' => $this->id,
                'name' => $this->name,
        ];

        if (is_object($this->mailList)) {
            $data['list_id'] = $this->mail_list_id;
            $data['list_name'] = $this->mailList->name;
        }

        if (is_object($this->segment)) {
            $data['segment_id'] = $this->segment_id;
            $data['segment_name'] = $this->segment->name;
        }

        $data = array_merge($data, $add_datas);

        \Acelle\Model\Log::create([
                                'user_id' => $user->id,
                                'type' => 'campaign',
                                'name' => $name,
                                'data' => json_encode($data),
                            ]);
    }

    /**
     * Count delivery processed.
     *
     * @return number
     */
    public function trackingCount()
    {
        return $this->trackingLogs()->count();
    }

    /**
     * Count delivery processed.
     *
     * @return number
     */
    public function deliveredCount()
    {
        return $this->trackingLogs()->where('status', '=', 'sent')->count();
    }
    
    /**
     * Count failed processed.
     *
     * @return number
     */
    public function failedCount()
    {
        return $this->trackingLogs()->where('status', '=', 'failed')->count();
    }

    /**
     * Count delivery success rate.
     *
     * @return number
     */
    public function deliveredRate()
    {
        $tracking_count = $this->trackingCount();
        
        if ($tracking_count == 0) {
            return 0;
        }

        return round(($this->deliveredCount() / $tracking_count) * 100, 0);
    }

    /**
     * Count click.
     *
     * @return number
     */
    public function clickCount($start = null, $end = null)
    {
        $query = $this->clickLogs();
        
        if (isset($start)) {
            $query = $query->where('click_logs.created_at', '>=', $start);
        }
        if (isset($end)) {
            $query = $query->where('click_logs.created_at', '<=', $end);
        }

        return $query->count();
    }

    /**
     * Url count.
     *
     * @return number
     */
    public function urlCount()
    {
        return $this->links()->count();
    }
    
    /**
     * Click rate.
     *
     * @return number
     */
    public function clickedLinkCount()
    {
        return $this->clickLogs()->distinct('url')->count('url');
    }

    /**
     * Click rate.
     *
     * @return number
     */
    public function clickRate()
    {
        $url_count = $this->urlCount();
        
        if ($url_count == 0) {
            return 0;
        }

        return round(($this->clickedLinkCount() / $url_count) * 100, 0);
    }
    
    /**
     * Count unique clicked opened emails.
     *
     * @return number
     */
    public function clickedEmailsCount()
    {
        $query = $this->clickLogs();

        return $query->distinct('subscriber_id')->count('subscriber_id');
    }
    
    /**
     * Click a link rate.
     *
     * @return number
     */
    public function clickALinkRate()
    {
        $open_count = $this->openCount();
        
        if ($open_count == 0) {
            return 0;
        }

        return round(($this->clickCount() / $open_count) * 100, 0);
    }
    
    /**
     * Clicked emails count.
     *
     * @return number
     */
    public function clickedEmailsRate()
    {
        $open_count = $this->openCount();
        
        if ($open_count == 0) {
            return 0;
        }

        return round(($this->clickedEmailsCount() / $open_count) * 100, 0);
    }

    /**
     * Count click.
     *
     * @return number
     */
    public function clickPerUniqOpen()
    {
        $open_count = $this->openCount();
        
        if ($open_count == 0) {
            return 0;
        }

        return round(($this->clickCount() / $open_count) * 100, 0);
    }

    /**
     * Count abuse feedback.
     *
     * @return number
     */
    public function abuseFeedbackCount()
    {
        return $this->feedbackLogs()->where('feedback_type', '=', 'abuse')->count();
    }

    /**
     * Count open.
     *
     * @return number
     */
    public function openCount()
    {
        return $this->openLogs()->count();
    }
    
    /**
     * Not open count.
     *
     * @return number
     */
    public function notOpenCount()
    {
        return $this->subscribers()->count() - $this->openUniqCount();
    }

    /**
     * Count unique open.
     *
     * @return number
     */
    public function openUniqCount($start = null, $end = null)
    {
        $query = $this->openLogs();
        if (isset($start)) {
            $query = $query->where('open_logs.created_at', '>=', $start);
        }
        if (isset($end)) {
            $query = $query->where('open_logs.created_at', '<=', $end);
        }

        return $query->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Open rate.
     *
     * @return number
     */
    public function openRate()
    {
        $delivered_count = $this->deliveredCount();
        
        if ($delivered_count == 0) {
            return 0;
        }

        return round(($this->openCount() / $delivered_count) * 100, 0);
    }
    
    /**
     * Not open rate.
     *
     * @return number
     */
    public function notOpenRate()
    {
        $subcribers_count = $this->subscribers()->count();
        
        if ($subcribers_count == 0) {
            return 0;
        }

        return round(($this->notOpenCount() / $subcribers_count) * 100, 0);
    }

    /**
     * Count unique open rate.
     *
     * @return number
     */
    public function openUniqRate()
    {
        $delivered_count = $this->deliveredCount();
        
        if ($delivered_count == 0) {
            return 0;
        }

        return round(($this->openUniqCount() / $delivered_count) * 100, 0);
    }

    /**
     * Count bounce back.
     *
     * @return number
     */
    public function feedbackCount()
    {
        return $this->feedbackLogs()->distinct('subscriber_id')->count('subscriber_id');
    }
    
    /**
     * Count feedback rate.
     *
     * @return number
     */
    public function feedbackRate()
    {
        $delivered_count = $this->deliveredCount();
        
        if ($delivered_count == 0) {
            return 0;
        }

        return round(($this->feedbackCount() / $delivered_count) * 100, 0);
    }

    /**
     * Count bounce back.
     *
     * @return number
     */
    public function bounceCount()
    {
        return $this->bounceLogs()->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Count bounce rate.
     *
     * @return number
     */
    public function bounceRate()
    {
        $delivered_count = $this->deliveredCount();
        
        if ($delivered_count == 0) {
            return 0;
        }

        return round(($this->bounceCount() / $delivered_count) * 100, 0);
    }

    /**
     * Count hard bounce.
     *
     * @return number
     */
    public function hardBounceCount()
    {
        return $this->campaign_bounce_logs()->where('bounce_type', '=', 'hard')->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Count hard bounce rate.
     *
     * @return number
     */
    public function hardBounceRate()
    {
        $delivered_processed_count = $this->deliveryProcessedCount();
        
        if ($delivered_processed_count == 0) {
            return 0;
        }

        return round(($this->hardBounceCount() / $delivered_processed_count) * 100, 0);
    }

    /**
     * Count soft bounce.
     *
     * @return number
     */
    public function softBounceCount()
    {
        return $this->campaign_bounce_logs()->where('bounce_type', '=', 'soft')->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Count soft bounce rate.
     *
     * @return number
     */
    public function softBounceRate()
    {
        $tracking_count = $this->trackingCount();
        
        if ($tracking_count == 0) {
            return 0;
        }

        return round(($this->softBounceCount() / $tracking_count) * 100, 0);
    }

    /**
     * Count unsubscibe.
     *
     * @return number
     */
    public function unsubscribeCount()
    {
        return $this->unsubscribeLogs()->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Count unsubscibe rate.
     *
     * @return number
     */
    public function unsubscribeRate()
    {
        $delivered_count = $this->deliveredCount();
        
        if ($delivered_count == 0) {
            return 0;
        }

        return round(($this->unsubscribeCount() / $delivered_count) * 100, 0);
    }

    /**
     * Get last click.
     *
     * @param number $number
     *
     * @return collect
     */
    public function lastClick()
    {
        return $this->clickLogs()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Get last open.
     *
     * @param number $number
     *
     * @return collect
     */
    public function lastOpen()
    {
        return $this->openLogs()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Get last open list.
     *
     * @param number $number
     *
     * @return collect
     */
    public function lastOpens($number)
    {
        return $this->openLogs()->orderBy('created_at', 'desc')->limit($number);
    }

    /**
     * Get most open subscribers.
     *
     * @param number $number
     *
     * @return collect
     */
    public function mostOpenSubscribers($number)
    {
        return \Acelle\Web\Subscriber::selectRaw(\DB::getTablePrefix().'list_subscriber.*, COUNT('.\DB::getTablePrefix().'campaign_track_unsubscribe.id) AS openCount')
                            ->leftJoin('campaign_track_unsubscribe', 'campaign_track_unsubscribe.subscriber_id', '=', 'list_subscriber.subscriber_id')
                            ->where('campaign_track_unsubscribe.campaign_id', '=', $this->campaign_id)
                            ->groupBy('list_subscriber.subscriber_id')
                            ->orderBy('openCount', 'desc')
                            ->limit($number);
    }

    /**
     * Get last opened time.
     *
     * @return datetime
     */
    public function getLastOpen()
    {
        $last = $this->campaign_track_opens()->orderBy('created_at', 'desc')->first();

        return is_object($last) ? $last->created_at->timezone(\Auth::user()->getTimezone()) : null;
    }

    /**
     * Campaign top 5 opens.
     *
     * @return datetime
     */
    public static function topOpens($number = 5, $user = null)
    {
        $records = self::select(\DB::raw(\DB::getTablePrefix().'campaigns.*, count(*) as `aggregate`'))
            ->join('tracking_logs', 'tracking_logs.campaign_id', '=', 'campaigns.id')
            ->join('open_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id');

        if (isset($user)) {
            $records = $records->where('campaigns.user_id', '=', $user->id);
        }

        $records = $records->groupBy('campaigns.id')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 clicks.
     *
     * @return datetime
     */
    public static function topClicks($number = 5, $user = null)
    {
        $records = self::select(\DB::raw(\DB::getTablePrefix().'campaigns.*, count(*) as `aggregate`'))
            ->join('tracking_logs', 'tracking_logs.campaign_id', '=', 'campaigns.id')
            ->join('click_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id');

        if (isset($user)) {
            $records = $records->where('campaigns.user_id', '=', $user->id);
        }

        $records = $records->groupBy('campaigns.id')
                    ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 clicks.
     *
     * @return datetime
     */
    public static function topLinks($number = 5, $user = null)
    {
        $records = Link::select(\DB::raw(\DB::getTablePrefix().'links.*, count(*) as `aggregate`'))
            ->join('campaign_links', 'campaign_links.link_id', '=', 'links.id')
            ->join('tracking_logs', 'tracking_logs.campaign_id', '=', 'campaign_links.campaign_id')
            ->join('click_logs', function ($join) {
                $join->on('click_logs.message_id', '=', 'tracking_logs.message_id')
                ->on('click_logs.url', '=', 'links.url');
            });

        if (isset($user)) {
            $records = $records->join('campaigns', 'campaign_links.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.user_id', '=', $user->id);
        }

        $records = $records->groupBy('links.id')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 clicks.
     *
     * @return datetime
     */
    public function getTopLinks($number = 5)
    {
        $records = ClickLog::select(\DB::raw(\DB::getTablePrefix().'click_logs.*, count(*) as `aggregate`'))
            ->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
        
        $records = $records->groupBy('click_logs.url')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 clicks.
     *
     * @return datetime
     */
    public function getTopOpenSubscribers($number = 5)
    {
        $records = Subscriber::select(\DB::raw(\DB::getTablePrefix().'subscribers.*, count(*) as `aggregate`'))
            ->join('tracking_logs', 'tracking_logs.subscriber_id', '=', 'subscribers.id')
            ->join('open_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('campaign_id', '=', $this->id);

        $records = $records->groupBy('tracking_logs.message_id')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }
    
    /**
     * Recent subscriber opens.
     *
     * @return datetime
     */
    public function getRecentOpenSubscribers($number = 5)
    {
        $records = Subscriber::select(\DB::raw(\DB::getTablePrefix().'subscribers.*, count(*) as `aggregate`'))
            ->join('tracking_logs', 'tracking_logs.subscriber_id', '=', 'subscribers.id')
            ->join('open_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('campaign_id', '=', $this->id);

        $records = $records->groupBy('tracking_logs.message_id')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }    

    /**
     * Campaign top 5 open location.
     *
     * @return datetime
     */
    public function topLocations($number = 5, $user = null)
    {
        $records = IpLocation::select(\DB::raw(\DB::getTablePrefix().'ip_locations.*, count(*) as `aggregate`'))
            ->join('open_logs', 'open_logs.ip_address', '=', 'ip_locations.ip_address')
            ->join('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        if (isset($user)) {
            $records = $records->join('campaigns', 'tracking_logs.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.user_id', '=', $user->id);
        }

        $records = $records->groupBy('open_logs.ip_address')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }
    
    /**
     * Campaign top 5 open countries.
     *
     * @return datetime
     */
    public function topCountries($number = 5, $user = null)
    {
        $records = IpLocation::select(\DB::raw(\DB::getTablePrefix().'ip_locations.*, count(*) as `aggregate`'))
            ->join('open_logs', 'open_logs.ip_address', '=', 'ip_locations.ip_address')
            ->join('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        if (isset($user)) {
            $records = $records->join('campaigns', 'tracking_logs.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.user_id', '=', $user->id);
        }

        $records = $records->groupBy('ip_locations.country_name')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }
    
    /**
     * Campaign top 5 click countries.
     *
     * @return datetime
     */
    public function topClickCountries($number = 5, $user = null)
    {
        $records = IpLocation::select(\DB::raw(\DB::getTablePrefix().'ip_locations.*, count(*) as `aggregate`'))
            ->join('click_logs', 'click_logs.ip_address', '=', 'ip_locations.ip_address')
            ->join('tracking_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        if (isset($user)) {
            $records = $records->join('campaigns', 'tracking_logs.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.user_id', '=', $user->id);
        }

        $records = $records->groupBy('ip_locations.country_name')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign locations.
     *
     * @return datetime
     */
    public function locations()
    {
        $records = IpLocation::select('ip_locations.*', 'open_logs.created_at as open_at', 'subscribers.email as email')
            ->leftJoin('open_logs', 'open_logs.ip_address', '=', 'ip_locations.ip_address')
            ->leftJoin('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->leftJoin('subscribers', 'subscribers.id', '=', 'tracking_logs.subscriber_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        return $records;
    }

    /**
     * Replace link in text by click tracking url.
     *
     * @return text
     * @note addTrackingUrl() must go before appendOpenTrackingUrl()
     */
    public function addTrackingUrl($email_html_content, $msgId)
    {
        if (preg_match_all('/<a[^>]*href=["\'](?<url>http[^"\']*)["\']/i', $email_html_content, $matches)) {
            foreach ($matches[0] as $key => $href) {
                $url = $matches['url'][$key];

                $newUrl = str_replace('URL', StringHelper::base64UrlEncode($url), Setting::get('url_click_track'));
                $newUrl = str_replace('MESSAGE_ID', StringHelper::base64UrlEncode($msgId), $newUrl);
                $newHref = str_replace($url, $newUrl, $href);

                // if the link contains UNSUBSCRIBE URL tag
                if (strpos($href, '{UNSUBSCRIBE_URL}') !== false) {
                    // just do nothing
                } else if (preg_match("/{[A-Z0-9_]+}/", $href)) {
                    // just skip if the url contains a tag. For example: {UPDATE_PROFILE_URL}
                    // @todo: do we track these clicks?
                } else {
                    $email_html_content = str_replace($href, $newHref, $email_html_content);
                }
            }
        }
        return $email_html_content;
    }

    /**
     * Type of campaigns.
     *
     * @return object
     */
    public static function types()
    {
        return [
            'regular' => [],
            'plain-text' => [],
        ];
    }
    
    /**
     * Copy new campaign.
     */
    public function copy($name)
    {
        $copy = $this->replicate();
        $copy->name = $name;
        $copy->created_at = \Carbon\Carbon::now();
        $copy->updated_at = \Carbon\Carbon::now();
        $copy->status = self::STATUS_NEW;
        $copy->run_at = NULL;
        $copy->custom_order = 0;
        $copy->save();
    }
    
    /**
     * Convert html to inline.
     * @todo not very OOP here, consider moving this to a Helper instead
     *
     */
    public function inlineHtml($html)
    {
        // Convert to inline css if template source is builder
        if ($this->template_source == 'builder') {
            $cssToInlineStyles = new CssToInlineStyles();
            
            $css = file_get_contents(public_path("css/res_email.css"));
            
            // output
            $html = $cssToInlineStyles->convert(
                $html,
                $css
            );
        }
        
        return $html;
    }
    
    /**
     * Send a test email for testing campaign
     */
    public function sendTestEmail($email)
    {
        try {
            MailLog::info('Sending test email for campaign `' . $this->name . '`');
            MailLog::info('Sending test email to `' . $email . '`');

            // @todo: only send a test message when campaign sufficient information is available

            // build a temporary subscriber oject used to pass through the sending methods
            $subscriber = $this->createStdClassSubscriber(['email' => $email]);

            // Pick up an available sending server
            // Throw exception in case no server available
            $server = $this->pickServer($this);

            // build the message from campaign information
            list($message, $msgId) = $this->prepareEmail($subscriber, $server);
            
            // actually send
            // @todo consider using queue here
            $result = $server->send($message, array(
                'from_email' => $this->from_email,
                'to' => $subscriber->email, // workaround: some sending engine requires the TO explicitly (not in MIME message)
                'campaign' => $this,
                'subscriber' => $subscriber,
                'msgId' => $msgId
            ));
            
            // examine the result from sending server
            if(array_has($result, 'error')) {
                throw new \Exception($result['error']);
            }

            return [
                "status" => "success",
                "message" => trans('messages.campaign.test_sent')
            ];
        } catch (\Exception $e) {
            return [
                "status" => "error",
                "message" => $e->getMessage()
            ];
        }
    }

    /**
     * Get the delay time before sending
     *
     */
    public function getDelayInSeconds()
    {
        $now = Carbon::now();

        if ($this->run_at->timestamp <= $now->timestamp) {
            return 0;
        } else {
            return $this->run_at->diffInSeconds($now);
        }
    }

    /**
     * Queue campaign for sending
     *
     */
    public function queue()
    {
        $this->ready();

        $job = (new \Acelle\Jobs\RunCampaignJob($this))->delay($this->getDelayInSeconds());
        
        dispatch($job);
    }

    /**
     * Queue campaign for sending
     *
     */
    public function clearAllJobs()
    {
        // cleanup jobs and system_jobs
        // @todo data should be a JSON field instead
        $systemJobs = SystemJob::where('name', 'Acelle\Jobs\RunCampaignJob')->where('data', $this->id)->get();
        foreach($systemJobs as $systemJob) {
            // @todo what if jobs were already started? check `reserved` field?
            $systemJob->clear();
        }
    }

    /**
     * Re-queue the campaign for sending
     *
     */
    public function requeue()
    {
        // clear all campaign's sendign jobs which are being queue
        $this->clearAllJobs();

        // and queue again
        $this->queue();
    }

    /**
     * Overwrite the delete() method to also clear the pending jobs
     *
     */
    public function delete()
    {
        $this->clearAllJobs();
        parent::delete();
    }

    /**
     * Create a stdClass subscriber (for sending a campaign test email)
     * The campaign sending functions take a subscriber object as input
     * However, a test email address is not yet a subscriber object, so we have to build a fake stdClass object
     * which can be used as a real subscriber
     *
     * @param array $subscriber
     */
    private function createStdClassSubscriber($subscriber)
    {
        // default attributes that are required
        $jsonObj = [
            'uid' => uniqid()
        ];
        
        // append the user specified attributes and build a stdClass object
        $stdObj = json_decode(json_encode(array_merge($jsonObj, $subscriber)));

        return $stdObj;
    }
    
    /**
     * Check if the given variable is a subscriber object (for actually sending a campaign)
     * Or a stdClass subscriber (for sending test email)
     *
     * @param Object $object
     */
    private function isStdClassSubscriber($object) {
        return (get_class($object) == 'stdClass');
    }
}
