<?php

/**
 * SystemJob class.
 *
 * Model class for tracking system jobs.
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
use Illuminate\Foundation\Bus\DispatchesJobs;
use DB;
use Acelle\Model\Job;

class SystemJob extends Model
{
    // status
    const STATUS_NEW = 'new';
    const STATUS_RUNNING = 'running';
    const STATUS_DONE = 'done';
    const STATUS_FAILED = 'failed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status'
    ];

    /**
     * Set job as started.
     *
     * @var array
     */
    public function setStarted() {
        $this->status = self::STATUS_RUNNING;
        $this->start_at = \Carbon\Carbon::now();
        $this->save();
    }

    /**
     * Set job as finished.
     *
     * @var array
     */
    public function setDone() {
        $this->status = self::STATUS_DONE;
        $this->end_at = \Carbon\Carbon::now();
        $this->save();
    }

    /**
     * Set job as finished.
     *
     * @var array
     */
    public function setFailed() {
        $this->status = self::STATUS_FAILED;
        $this->end_at = \Carbon\Carbon::now();
        $this->save();
    }
    
    /**
     * Run time.
     *
     * @return collect
     */
    public function runTime()
    {
        return gmdate("H:i:s", -$this->updated_at->diffInSeconds($this->created_at, false));
    }

    /**
     * Stop the job as well as delete the Job records
     *
     * @return collect
     */
    public function clear()
    {
        // delete all system_jobs & jobs
        DB::transaction(function() {
            // delete jobs
            $jobs = Job::where('reserved', 0)->get();
            foreach($jobs as $job) {
                $json = json_decode($job->payload, true);
                try {
                    $j = unserialize($json['data']['command']);
                    if ($j->getSystemJob()->id == $this->id) {
                        Job::destroy($job->id);
                    }
                } catch (\Exception $ex) {
                    // delete orphan job
                    Job::destroy($job->id);
                }
            }

            // delete system_jobs
            self::destroy($this->id);
        });
    }
    
    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
		return ($this->data) ? json_decode($this->data, true) : [];
    }
	
	/**
     * Update data json.
     *
     * @return void
     */
    public function updateData(array $data)
    {
        $json = $this->getData();
        $this->data = json_encode(array_merge($json, $data));
        $this->save();
    }
}
