<?php

/**
 * UserGroupSendingServer class.
 *
 * Model class for user group
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

class UserGroupSendingServer extends Model
{
    /**
     * Associations.
     *
     * @var object | collect
     */
    public function user_group()
    {
        return $this->belongsTo('Acelle\Model\UserGroup');
    }

    public function sending_server()
    {
        return $this->belongsTo('Acelle\Model\SendingServer');
    }
}
