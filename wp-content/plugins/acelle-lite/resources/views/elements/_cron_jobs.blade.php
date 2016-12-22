<h3 class="text-teal-800"><i class="icon-puzzle2"></i> {{ trans('messages.setting_up_cron_jobs') }}</h3>
    
<div class="">
    {!! trans('messages.cron_jobs_guide') !!}    
</div>
<br/>

<?php
try {
    if (!isset(explode(" ", exec("whereis php"))[1])) {
        $php_bin = "<span class='text-danger'>{PHP_BIN_PATH}</span>";
?>
    <div class="alert alert-danger">
        Cannot find a PHP CLI on your server. Please check your server's setup it and replace {PHP_BIN_PATH} with the actual path to your PHP CLI.<br /> Ex: /usr/bin/php7.0, /usr/bin/php, /usr/lib/php.
    </div>
<?php
    } else {
        $php_bin = explode(" ", exec("whereis php"))[1];
    }
} catch (\Exception $e) {
    $php_bin = "<span class='text-danger'>{PHP_BIN_PATH}</span>";
?>
    <div class="alert alert-danger">
        Cannot find a PHP CLI on your server. Please check your server's setup it and replace {PHP_BIN_PATH} with the actual path to your PHP CLI.<br /> Ex: /usr/bin/php7.0, /usr/bin/php, /usr/lib/php.
    </div>
<?php
}
?>

<pre style="font-size: 16px;background:#f5f5f5"># {{ trans('messages.cron_jobs_comment') }}
* * * * * {!! $php_bin !!} -q {{ base_path() }}/artisan handler:run 2&gt;&amp;1
* * * * * {!! $php_bin !!} -q {{ base_path() }}/artisan queue:work --tries=3 2&gt;&amp;1
</pre>
