<?php
require_once( dirname(__FILE__) . '/../../../core.php' );
require_api('authentication_api.php');
require_api('filter_api.php');
require_api('helper_api.php');
require_api('config_api.php');
require_api('lang_api.php');

auth_ensure_user_authenticated();
date_default_timezone_set('Asia/Kuala_Lumpur');

# --- Configure the custom fields you want to show ---
$CF_CLIENT_ID         = 1; // e.g. "RANS"
$CF_DATE_COMPLETED_ID = 2; // e.g. UNIX timestamp or 'YYYY-MM-DD'
$CF_NOTES_ID          = 3; // long text / note

$t_project_id = helper_get_current_project();

$f_page_number = 1; $t_per_page = -1; // -1 = all
$t_page_count = 0;  $t_bug_count = 0;

$t_rows = filter_get_bug_rows(
    $f_page_number,
    $t_per_page,
    $t_page_count,
    $t_bug_count,
    null,           // use active filter
    $t_project_id,  // project
    null,           // user
    true            // sticky from session
);

# Build a list of bug_ids on the page
$bug_ids = array_map(function($b){ return (int)$b->id; }, $t_rows);

# Index custom fields for those bugs: $cf_index[bug_id][field_id] = ['value'=>..., 'text'=>...]
$cf_index = [];
if (!empty($bug_ids)) {
    $placeholders = implode(',', array_fill(0, count($bug_ids), db_param()));
    $q = "
        SELECT bug_id, field_id, value, text
        FROM mantis_custom_field_string_table
        WHERE bug_id IN ($placeholders)
    ";
    $res = db_query($q, $bug_ids);
    while ($row = db_fetch_array($res)) {
        $b = (int)$row['bug_id'];
        $f = (int)$row['field_id'];
        $cf_index[$b][$f] = ['value' => $row['text'], 'text' => $row['text']];
    }
}

# Helper: format a CF value by field id
function cf_val($cf_index, $bug_id, $field_id, $default='-') {
    return isset($cf_index[$bug_id][$field_id]['value']) && $cf_index[$bug_id][$field_id]['value'] !== ''
        ? $cf_index[$bug_id][$field_id]['value']
        : $default;
}

# Helper: format date (accepts unix ts or date string)
function fmt_date_guess($raw, $fallback='-') {
    if ($raw === '' || $raw === null) return $fallback;
    if (is_numeric($raw)) {
        $ts = (int)$raw;
    } else {
        $ts = strtotime($raw);
        if ($ts === false) return $fallback;
    }
    return date('d-M-Y', $ts);
}

ob_start();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>MantisBT Export</title>
<style>
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #ccc; padding: 6px; vertical-align: top; }
  th { background: #eee; }
  #pdf-header { margin-bottom: 8px; }
</style>
</head>
<body>

<div id="pdf-header">
  <hr />
  <div style="float: left;">CKNextBT • cknext bug tracker</div>
  <div style="float: right;">Event <?= date('Y M d, H:i:s') ?></div>
</div>
<div style="clear: both;"></div>

<h2>Issues — <?= string_display_line(project_get_name($t_project_id)); ?></h2>

<table>
  <tr>
    <th>ID</th>
    <th>Category</th>
    <th>Events</th>
    <th>Action</th>
    <th>Status</th>
    <th>Assigned To</th>
    <th>Updated</th>
    <th>Client (CF <?= (int)$CF_CLIENT_ID ?>)</th>
    <th>Completed Date (CF <?= (int)$CF_DATE_COMPLETED_ID ?>)</th>
    
  </tr>

<?php foreach ($t_rows as $t_bug): ?>
  <?php
    $bug_id   = (int)$t_bug->id;
    $category = $t_bug->category_id ? category_get_name((int)$t_bug->category_id) : '-';
    $assignee = $t_bug->handler_id  ? user_get_name((int)$t_bug->handler_id)     : '-';
    $status   = get_enum_element('status', (int)$t_bug->status);
    $updated  = date(config_get('short_date_format'), (int)$t_bug->last_updated);

    $cf_client   = cf_val($cf_index, $bug_id, $CF_CLIENT_ID, '-');
    $cf_done_raw = cf_val($cf_index, $bug_id, $CF_DATE_COMPLETED_ID, '');
    $cf_done     = $cf_done_raw === '' ? '-' : fmt_date_guess($cf_done_raw, '-');
    $cf_notes    = cf_val($cf_index, $bug_id, $CF_NOTES_ID, '-');
  ?>
  <tr>
    <td>#<?= $bug_id ?></td>
    <td><?= string_display_line($category) ?></td>
    <td><?= string_display_line($t_bug->summary) ?></td>
    <td><?= string_display_line($cf_notes) ?></td>
    <td><?= string_display_line($status) ?></td>
    <td><?= string_display_line($assignee) ?></td>
    <td><?= string_display_line($updated) ?></td>
    <td><?= string_display_line($cf_client) ?></td>
    <td><?= string_display_line($cf_done) ?></td>
    
  </tr>
<?php endforeach; ?>
</table>

</body></html>
<?php
$html = ob_get_clean();

require_once __DIR__ . '/../vendor/autoload.php';
$dompdf = new Dompdf\Dompdf(['isRemoteEnabled' => true]);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream('mantis-export.pdf', ['Attachment' => false]);
exit;
