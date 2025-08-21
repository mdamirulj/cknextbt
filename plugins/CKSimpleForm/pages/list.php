<?php
require_api('authentication_api.php');
require_api('access_api.php');
require_api('database_api.php');
require_api('layout_api.php');
require_api('helper_api.php');
require_api('string_api.php');

auth_ensure_user_authenticated();
access_ensure_global_level( plugin_config_get('access_threshold') );

$t_table = 'mantis_cksimpleform_table';
$t_project_id = (int) helper_get_current_project(); // 0 = ALL PROJECTS

if ( $t_project_id > 0 ) {
    // Filter by current project
    $t_query  = "SELECT id, title, category, status, due_date, user_id, project_id, date_submitted
                 FROM $t_table
                 WHERE project_id = " . db_param() . "
                 ORDER BY id DESC";
    $t_rs = db_query( $t_query, array( $t_project_id ) );
} else {
    // No filter
    $t_query  = "SELECT id, title, category, status, due_date, user_id, project_id, date_submitted
                 FROM $t_table
                 ORDER BY id DESC";
    $t_rs = db_query( $t_query );
}

layout_page_header('Simple Form - List');
layout_page_begin();
?>
<div class="page-content">
  <div class="widget-box widget-color-blue2">
    <div class="widget-header widget-header-small">
      <h4 class="widget-title lighter">Saved Entries</h4>
      <div class="widget-toolbar">
        <a class="btn btn-primary btn-white btn-round btn-sm" href="<?php echo plugin_page('form'); ?>">New Entry</a>
      </div>
    </div>
    <div class="widget-body">
      <div class="widget-main no-padding">
        <table class="table table-striped table-bordered table-condensed">
          <thead>
            <tr>
              <th style="width:70px">ID</th>
              <th>Title</th>
              <th style="width:120px">Category</th>
              <th style="width:130px">Status</th>
              <th style="width:120px">Due Date</th>
              <th style="width:160px">Submitted</th>
              <th style="width:80px">Action</th>
            </tr>
          </thead>
          <tbody>
          <?php while( $t_row = db_fetch_array($t_rs) ) {
              $t_id    = (int)$t_row['id'];
              $t_title = string_display_line( $t_row['title'] );
              $t_cat   = string_display_line( (string)$t_row['category'] );
              $t_stat  = string_display_line( (string)$t_row['status'] );
              $t_due   = $t_row['due_date'] ? string_display_line($t_row['due_date']) : '-';
              $t_when  = date('Y-m-d H:i', (int)$t_row['date_submitted']);
          ?>
            <tr>
              <td>#<?php echo $t_id; ?></td>
              <td><?php echo $t_title; ?></td>
              <td><?php echo $t_cat ?: '-'; ?></td>
              <td><?php echo $t_stat ?: '-'; ?></td>
              <td><?php echo $t_due; ?></td>
              <td><?php echo $t_when; ?></td>
              <td>
                <a class="btn btn-xs btn-white btn-primary"
                   href="<?php echo plugin_page('view') . '&id=' . $t_id; ?>">View</a>
              </td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php layout_page_end();
