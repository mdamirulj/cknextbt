<?php
require_api('authentication_api.php');
require_api('access_api.php');
require_api('gpc_api.php');
require_api('database_api.php');
require_api('layout_api.php');
require_api('string_api.php');
require_api('user_api.php');
require_api('project_api.php');

auth_ensure_user_authenticated();
access_ensure_global_level( plugin_config_get('access_threshold') );

$t_id = gpc_get_int('id', 0);
if ($t_id <= 0) trigger_error( ERROR_GENERIC, ERROR );

$t_table = 'mantis_cksimpleform_table';
$t_rs = db_query("SELECT * FROM $t_table WHERE id = " . db_param(), array($t_id));
$t_row = db_fetch_array($t_rs);
if (!$t_row) trigger_error( ERROR_GENERIC, ERROR );

layout_page_header('Simple Form - View');
layout_page_begin();
?>
<div class="page-content">
  <div class="col-md-8 col-xs-12">
    <div class="widget-box widget-color-grey">
      <div class="widget-header widget-header-small">
        <h4 class="widget-title lighter">View Entry #<?php echo (int)$t_row['id']; ?></h4>
        <div class="widget-toolbar">
          <a class="btn btn-primary btn-white btn-round btn-sm" href="<?php echo plugin_page('list'); ?>">Back to List</a>
        </div>
      </div>
      <div class="widget-body">
        <div class="widget-main">
          <dl class="dl-horizontal">
            <dt>Title</dt><dd><?php echo string_display_line($t_row['title']); ?></dd>
            <dt>Category</dt><dd><?php echo string_display_line((string)$t_row['category']); ?></dd>
            <dt>Status</dt><dd><?php echo string_display_line((string)$t_row['status']); ?></dd>
            <dt>Due Date</dt><dd><?php echo $t_row['due_date'] ?: '-'; ?></dd>
            <dt>Description</dt><dd><?php echo string_display((string)$t_row['description']); ?></dd>
            <dt>Submitted By</dt><dd><?php echo user_get_name((int)$t_row['user_id']); ?></dd>
            <dt>Submitted At</dt><dd><?php echo date('Y-m-d H:i', (int)$t_row['date_submitted']); ?></dd>
          </dl>
        </div>
      </div>
    </div>
  </div>
</div>
<?php layout_page_end();
