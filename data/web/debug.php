<?php
require_once "inc/prerequisites.inc.php";

if (isset($_SESSION['mailcow_cc_role']) && $_SESSION['mailcow_cc_role'] == "admin") {
require_once "inc/header.inc.php";
$_SESSION['return_to'] = $_SERVER['REQUEST_URI'];

?>
<div class="container">

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#tab-containers" aria-controls="tab-containers" role="tab" data-toggle="tab">Containers & System</a></li>
    <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">Logs
      <span class="caret"></span></a>
      <ul class="dropdown-menu">
        <li role="presentation"><a href="#tab-postfix-logs" aria-controls="tab-postfix-logs" role="tab" data-toggle="tab">Postfix</a></li>
        <li role="presentation"><a href="#tab-dovecot-logs" aria-controls="tab-dovecot-logs" role="tab" data-toggle="tab">Dovecot</a></li>
        <li role="presentation"><a href="#tab-sogo-logs" aria-controls="tab-sogo-logs" role="tab" data-toggle="tab">SOGo</a></li>
        <li role="presentation"><a href="#tab-netfilter-logs" aria-controls="tab-netfilter-logs" role="tab" data-toggle="tab">Netfilter</a></li>
        <li role="presentation"><a href="#tab-rspamd-history" aria-controls="tab-rspamd-history" role="tab" data-toggle="tab">Rspamd</a></li>
        <li role="presentation"><a href="#tab-autodiscover-logs" aria-controls="tab-autodiscover-logs" role="tab" data-toggle="tab">Autodiscover</a></li>
        <li role="presentation"><a href="#tab-watchdog-logs" aria-controls="tab-watchdog-logs" role="tab" data-toggle="tab">Watchdog</a></li>
        <li role="presentation"><a href="#tab-acme-logs" aria-controls="tab-acme-logs" role="tab" data-toggle="tab">ACME</a></li>
        <li role="presentation"><a href="#tab-api-logs" aria-controls="tab-api-logs" role="tab" data-toggle="tab">API</a></li>
      </ul>
    </li>
  </ul>

	<div class="row">
		<div class="col-md-12">
      <div class="tab-content" style="padding-top:20px">

        <?php
          $exec_fields = array('cmd' => 'df', 'dir' => '/var/vmail');
          $vmail_df = explode(',', json_decode(docker('dovecot-mailcow', 'post', 'exec', $exec_fields), true));
        ?>
        <div role="tabpanel" class="tab-pane active" id="tab-containers">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Disk usage</h3>
            </div>
            <div class="panel-body">
              <div class="row">
                <div class="col-sm-3">
                  <p>/var/vmail on <?=$vmail_df[0];?></p>
                  <p><?=$vmail_df[2];?> / <?=$vmail_df[1];?> (<?=$vmail_df[4];?>)</p>
                </div>
                <div class="col-sm-9">
                  <div class="progress">
                    <div class="progress-bar progress-bar-info" role="progressbar" style="width:<?=$vmail_df[4];?>"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title">Container information</h3>
            </div>
            <div class="panel-body">
            <ul class="list-group">
            <?php
            $container_array = array(
              'nginx-mailcow',
              'rspamd-mailcow',
              'postfix-mailcow',
              'dovecot-mailcow',
              'sogo-mailcow',
              'acme-mailcow',
              'memcached-mailcow',
              'watchdog-mailcow',
              'unbound-mailcow',
              'redis-mailcow',
              'php-fpm-mailcow',
              'mysql-mailcow',
              'netfilter-mailcow',
              'clamd-mailcow'
            );
            $container_states = (docker($container, 'states'));
            foreach ($container_array as $container) {
                $container_state = $container_states[$container];
                ?>
                <li class="list-group-item">
                <?=$container;?>
                <?php
                date_default_timezone_set('UTC');
                $StartedAt = date_parse($container_state['StartedAt']);
                if ($StartedAt['hour'] !== false) {
                  $date = new \DateTime();
                  $date->setTimestamp(mktime(
                    $StartedAt['hour'],
                    $StartedAt['minute'],
                    $StartedAt['second'],
                    $StartedAt['month'],
                    $StartedAt['day'],
                    $StartedAt['year']));
                  $user_tz = new DateTimeZone(getenv('TZ'));
                  $date->setTimezone($user_tz);
                  $started = $date->format('r');
                }
                else {
                  $started = '?';
                }
                ?>
                <small>(Started on <?=$started;?>),
                <a href data-toggle="modal" data-container="<?=$container;?>" data-target="#RestartContainer">Restart</a></small>
                <span class="pull-right label label-<?=($container_state !== false && !empty($container_state)) ? (($container_state['Running'] == 1) ? 'success' : 'danger') : 'default'; ?>">&nbsp;&nbsp;&nbsp;</span>
                </li>
              <?php
              }
            ?>
            </ul>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-postfix-logs">
          <div class="panel panel-default">
            <div class="panel-heading">Postfix <span class="badge badge-info log-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="general_syslog" data-table="postfix_log" data-log-url="postfix" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="general_syslog" data-table="postfix_log" data-log-url="postfix" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-default" id="refresh_postfix_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="postfix_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-dovecot-logs">
          <div class="panel panel-default">
            <div class="panel-heading">Dovecot <span class="badge badge-info log-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="general_syslog" data-table="dovecot_log" data-log-url="dovecot" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="general_syslog" data-table="dovecot_log" data-log-url="dovecot" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-default" id="refresh_dovecot_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="dovecot_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-sogo-logs">
          <div class="panel panel-default">
            <div class="panel-heading">SOGo <span class="badge badge-info log-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="general_syslog" data-table="sogo_log" data-log-url="sogo" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="general_syslog" data-table="sogo_log" data-log-url="sogo" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-default" id="refresh_sogo_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="sogo_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-netfilter-logs">
          <div class="panel panel-default">
            <div class="panel-heading">Netfilter <span class="badge badge-info log-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="general_syslog" data-table="netfilter_log" data-log-url="netfilter" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="general_syslog" data-table="netfilter_log" data-log-url="netfilter" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-default" id="refresh_netfilter_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="netfilter_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-rspamd-history">
          <div class="panel panel-default">
            <div class="panel-heading">Rspamd history <span class="badge badge-info log-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="rspamd_history" data-table="rspamd_history" data-log-url="rspamd-history" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="rspamd_history" data-table="rspamd_history" data-log-url="rspamd-history" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-default" id="refresh_rspamd_history"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div id="rspamd_donut" style="height:400px;width:100%; "></div>
              <div class="table-responsive">
                <table class="table table-striped table-condensed log-table" id="rspamd_history"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-autodiscover-logs">
          <div class="panel panel-default">
            <div class="panel-heading">Autodiscover <span class="badge badge-info log-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="autodiscover_log" data-table="autodiscover_log" data-log-url="autodiscover" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="autodiscover_log" data-table="autodiscover_log" data-log-url="autodiscover" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-default" id="refresh_autodiscover_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="autodiscover_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-watchdog-logs">
          <div class="panel panel-default">
            <div class="panel-heading">Watchdog <span class="badge badge-info log-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="watchdog" data-table="watchdog_log" data-log-url="watchdog" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="watchdog" data-table="watchdog_log" data-log-url="watchdog" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-default" id="refresh_watchdog_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="watchdog_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-acme-logs">
          <div class="panel panel-default">
            <div class="panel-heading">ACME <span class="badge badge-info log-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="general_syslog" data-table="acme_log" data-log-url="acme" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="general_syslog" data-table="acme_log" data-log-url="acme" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-default" id="refresh_acme_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="acme_log"></table>
              </div>
            </div>
          </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="tab-api-logs">
          <div class="panel panel-default">
            <div class="panel-heading">API <span class="badge badge-info log-lines"></span>
              <div class="btn-group pull-right">
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="apilog" data-table="api_log" data-log-url="api" data-nrows="100">+ 100</button>
                <button class="btn btn-xs btn-default add_log_lines" data-post-process="apilog" data-table="api_log" data-log-url="api" data-nrows="1000">+ 1000</button>
                <button class="btn btn-xs btn-default" id="refresh_api_log"><?=$lang['admin']['refresh'];?></button>
              </div>
            </div>
            <div class="panel-body">
              <div class="table-responsive">
                <table class="table table-striped table-condensed" id="api_log"></table>
              </div>
            </div>
          </div>
        </div>

      </div> <!-- /tab-content -->
    </div> <!-- /col-md-12 -->
  </div> <!-- /row -->
</div> <!-- /container -->
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/modals/debug.php';
?>
<script type='text/javascript'>
<?php
$lang_admin = json_encode($lang['admin']);
echo "var lang = ". $lang_admin . ";\n";
echo "var csrf_token = '". $_SESSION['CSRF']['TOKEN'] . "';\n";
echo "var log_pagination_size = '". $LOG_PAGINATION_SIZE . "';\n";

?>
</script>
<script src="js/footable.min.js"></script>
<script src="js/debug.js"></script>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/footer.inc.php';
}
else {
	header('Location: /');
	exit();
}
?>
