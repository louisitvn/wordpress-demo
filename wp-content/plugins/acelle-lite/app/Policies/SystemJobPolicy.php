<?php

namespace Acelle\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class SystemJobPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    public function delete(\Acelle\Model\User $user, \Acelle\Model\SystemJob $item)
    {
        if($item->name == 'Acelle\Jobs\ImportSubscribersJob' || $item->name == 'Acelle\Jobs\ExportSubscribersJob' || $item->name == 'Acelle\Jobs\ImportWPUsersJob') {
            $data = json_decode($item->data);
            $list = \Acelle\Model\MailList::findByUid($data->mail_list_uid);
            return $list->user_id == $user->id &&
            in_array($data->status, ['failed', 'done']);
        }
        
        return false;
    }
    
    public function downloadImportLog(\Acelle\Model\User $user, \Acelle\Model\SystemJob $item)
    {
        $data = json_decode($item->data);
        $list = \Acelle\Model\MailList::findByUid($data->mail_list_uid);
        return $list->user_id == $user->id &&
            $item->name == 'Acelle\Jobs\ImportSubscribersJob' &&
            $data->status == 'done';
    }    
    
    public function downloadExportCsv(\Acelle\Model\User $user, \Acelle\Model\SystemJob $item)
    {
        $data = json_decode($item->data);
        $list = \Acelle\Model\MailList::findByUid($data->mail_list_uid);
        return $list->user_id == $user->id &&
            $item->name == 'Acelle\Jobs\ExportSubscribersJob' &&
            $data->status == 'done';
    }
}
