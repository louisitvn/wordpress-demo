<?php

namespace Acelle\Jobs;

class ImportExportJob extends SystemJob
{    
    protected $mailList;
    protected $user;
    protected $path;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mailList, $user)
    {
        // call parent's constructor
        parent::__construct();        
        
        $this->mailList = $mailList;
        $this->user = $user;
        $systemJob = $this->getSystemJob();
        
        // first init data
        $systemJob->data = json_encode([
            "mail_list_uid" => $mailList->uid,
            "status" => "new",
            "message" => trans('messages.starting'),
            "total" => 0,
            "success" => 0,
            "error" => 0,
            "percent" => 0
        ]);
        $systemJob->save();
        
        // folder for job file
        $path = storage_path('job/');
    
        // mkdir if not exist
        if (!file_exists($path)) {
            $oldmask = umask(0);
            mkdir($path, 0775, true);
            umask($oldmask);
        }
        
        // mkdir if not exist
        $path = $path.$systemJob->id."/";                
        if (!file_exists($path)) {
            $oldmask = umask(0);
            \Acelle\Library\Tool::xdelete($path);
            mkdir($path, 0775, true);
            umask($oldmask);
        }
        
        $this->path = $path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
    
    /**
     * Get job files path.
     *
     * @return void
     */
    public function getPath() {
        return $this->path;
    }
}
