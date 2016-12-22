<?php

namespace Acelle\Jobs;

class ImportSubscribersJob extends ImportExportJob
{
    protected $filename;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mailList, $user, $file)
    {
        // call parent's constructor
        parent::__construct($mailList, $user);
        
        // Upload csv
        $this->filename = 'data.csv';
        $file->move($this->path, $this->filename);
    }
    
    /**
     * Get import file name.
     *
     * @return void
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $job = $this;
            $job->mailList->import($job->user, $job);
        } catch (\Exception $e) {
            $systemJob = $this->getSystemJob();
            
            // set failed
            $systemJob->data = json_encode([
                "mail_list_uid" => $job->mailList->uid,
                "status" => "failed",
                "message" => $e->getMessage(),
                "total" => 0,
                "success" => 0,
                "error" => 0,
                "percent" => 0
            ]);
            $systemJob->save();
        }
    }
}
