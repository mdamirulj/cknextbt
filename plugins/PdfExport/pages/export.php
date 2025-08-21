<?php

require_once( dirname(__FILE__) . '/../../../core.php' );
require_api('authentication_api.php');
require_api('filter_api.php');
require_api('helper_api.php');
require_api('config_api.php');
require_api('lang_api.php');

auth_ensure_user_authenticated();

# Get current project
$t_project_id = helper_get_current_project();

# Pull issues using the current View Issues filter/cookies.
# Using filter_get_bug_rows() is the standard way; passing nulls and TRUE
# makes it use the active filter for the current user/session.
# See filter_api docs / examples discussed in community answers.
$f_page_number = 1; $t_per_page = -1; // -1 = all
$t_page_count = 0; $t_bug_count = 0;

$t_rows = filter_get_bug_rows(
    $f_page_number,
    $t_per_page,
    $t_page_count,
    $t_bug_count,
    null,             // p_custom_filter (null -> use active)
    $t_project_id,    // project
    null,             // user
    true              // use sticky filter from session
);

# Build a lightweight HTML table
ob_start();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>MantisBT Export</title>
<style>
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; }
  table { width: 100%; border-collapse: collapse; }
  th, td { border: 1px solid #ccc; padding: 6px; }
  th { background: #eee; }
</style>
</head>
<body>
<h2>Issues — <?= string_display_line(project_get_name($t_project_id)); ?></h2>
<table>
  <th>ID</th>
  <th>Category</th>
  <th>Summary</th>
  <th>Status</th>
  <th>Assigned To</th>
  <th>Updated</th>
</tr>
<?php foreach ($t_rows as $row): ?>
  <?php
    $category = $row->category_id ? category_get_name((int)$row->category_id) : '-';
    $assignee = $row->handler_id ? user_get_name((int)$row->handler_id) : '-';
    $status   = get_enum_element('status', (int)$row->status);
    $updated  = date(config_get('short_date_format'), (int)$row->last_updated);
  ?>
  <tr>
    <td>#<?= (int)$row->id ?></td>
    <td><?= string_display_line($category) ?></td>
    <td><?= string_display_line($row->summary) ?></td>
    <td><?= string_display_line($status) ?></td>
    <td><?= string_display_line($assignee) ?></td>
    <td><?= string_display_line($updated) ?></td>
  </tr>
<?php endforeach; ?>

</table>
</body></html>
<?php
$html = ob_get_clean();

# Render to PDF with Dompdf
require_once __DIR__ . '/../vendor/autoload.php';
$dompdf = new Dompdf\Dompdf([
  'isRemoteEnabled' => true
]);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

# Stream inline in the browser (not forced download)
$dompdf->stream('mantis-export.pdf', ['Attachment' => false]);
exit;
