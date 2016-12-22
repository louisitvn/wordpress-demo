<?php

/**
 * QuotaTracker class.
 *
 * Provide a data structure for storing and measure quota
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
 * @todo separate the time-series and the quota stuffs
 */

namespace Acelle\Library;
use Carbon\Carbon;

class QuotaTracker
{
    protected $series; 
    protected $interval;
    protected $quota; // in seconds

    const START_POINT = 0;

    /**
     * Constructor, modeling data from a JSON array 
     *
     * @param Array $series
     *
     * @return void
     */
    public function __construct($interval, $quota, $series = [])
    {
        $this->series = $series;
        $this->interval = \DateInterval::createFromDateString($interval);
        $this->quota = $quota;
    }

    /**
     * Check if over quota, add a time point
     *
     * @param Timestamps $timePoint
     *
     * @return void
     */
    public function add(Carbon $timePoint = NULL) {
        if (!isset($timePoint)) {
            $timePoint = Carbon::now();
        }

        if ($this->check($timePoint)) {
            $this->series[] = $timePoint->timestamp;
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if over quota
     *
     * @param Timestamps $timePoint
     *
     * @return void
     */
    public function check(Carbon $timePoint = NULL) {
        if (!isset($timePoint)) {
            $timePoint = Carbon::now();
        }

        $this->shiftBy($timePoint);
        //echo "Usage " . $this->usage() . "\nQuota " . $this->quota . "\n";

        if ($this->usage() >= $this->quota) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Shift the time series until its range fits the new time point
     *
     * @param Timestamps $timePoint
     *
     * @return void
     */
    private function shiftBy(Carbon $timePoint)
    {
        if (empty($this->series)) {
            return;
        }
        
        $cutOff = $timePoint->copy()->sub($this->interval)->timestamp;
        while($this->first() != NULL && $this->first() < $cutOff ) {
            array_shift($this->series);
        }
    }

    /**
     * Get the first data point of the time series
     *
     * @return Mixed data point
     */
    public function first()
    {
        return (empty($this->series)) ? NULL : $this->series[self::START_POINT];
    }

    /**
     * Get the last data point of the time series
     *
     * @return Mixed data point
     */
    public function last()
    {
        return (empty($this->series)) ? NULL : $this->series[sizeof($this->series) - 1];
    }

    /**
     * Count the time series length
     *
     * @return Integer count
     */
    public function usage()
    {
        return sizeof($this->series);
    }

    /**
     * Get usage percentage
     *
     * @return float
     */
    public function usagePercentage()
    {
        return (float) $this->usage() / $this->quota;
    }

    /**
     * Get the data series
     *
     * @return Array data series
     */
    public function getSeries()
    {
        return $this->series;
    }
}
