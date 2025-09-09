<?php
/**
 * PDF Export - Event List Template
 * plugins/PdfExport/pages/pdf_custom.php
 */

require_once( dirname(__FILE__) . '/../../../core.php' );

require_api('authentication_api.php');
require_api('filter_api.php');
require_api('helper_api.php');
require_api('config_api.php');
require_api('lang_api.php');
require_api('string_api.php');

require_api('category_api.php');
require_api('user_api.php');
require_api('bugnote_api.php');

auth_ensure_user_authenticated();
date_default_timezone_set('Asia/Kuala_Lumpur');

/** Helpers */
function h($s){ return string_display_line($s ?? ''); }
function dash_if_empty($s){ $s=trim((string)$s); return $s!=='' ? $s : '-'; }
function fmt_dt($ts){ return $ts ? date('d M Y h:i:s A', (int)$ts) : '-'; }
function fmt_dt_short($ts){ return $ts ? date('d M Y h:i A', (int)$ts) : '-'; }
function is_resolved_or_closed($status){
    $resolved = config_get('bug_resolved_status_threshold'); // usually 80
    return (int)$status >= (int)$resolved;
}
function build_event_id($bug_id, $submitted_ts){
    return 'E' . date('ymd', (int)$submitted_ts) . str_pad((string)$bug_id, 4, '0', STR_PAD_LEFT);
}
function hours_between($from_ts, $to_ts){
    if(!$from_ts || !$to_ts) return '-';
    $diff = max(0, (int)$to_ts - (int)$from_ts);
    return number_format($diff / 3600, 2);
}

$t_project_id = helper_get_current_project();

/** Use active "View Issues" filter */
$f_page_number = 1; $t_per_page = -1; // -1 => all in filter
$t_page_count = 0; $t_bug_count = 0;

$t_rows = filter_get_bug_rows(
    $f_page_number, $t_per_page, $t_page_count, $t_bug_count,
    null,             // p_custom_filter (null -> active)
    $t_project_id,    // project
    null,             // user
    true              // use sticky filter
);

/** Collect a light structure we can render cleanly */
$events = [];
$counter = 1;

foreach ($t_rows as $row) {
    $bug_id        = (int)$row->id;
    $category_name = $row->category_id ? category_get_name((int)$row->category_id) : '-';
    $assignee      = $row->handler_id ? user_get_name((int)$row->handler_id) : '-';
    $reporter      = $row->reporter_id ? user_get_name((int)$row->reporter_id) : '-';
    $status_name   = get_enum_element('status', (int)$row->status);

    // Action: latest visible bugnote (fallback "-")
    $action_text = '-';
    $notes = bugnote_get_all_visible_bugnotes($bug_id, auth_get_current_user_id(), 0);
    
    if (!empty($notes)) {
        // take latest note text
        $last = end($notes);
        $action_text = string_display_links($last->note ?? '-');
    }

    $finish_ts = is_resolved_or_closed($row->status) ? (int)$row->last_updated : 0;
    $events[] = [
        'no'          => $counter++,
        'event_id'    => build_event_id($bug_id, (int)$row->date_submitted),
        'system'      => $category_name,
        'xref'        => '-',
        'event'       => $row->summary ?? '',
        'action'      => $action_text,
        'date_occur'  => (int)$row->date_submitted,
        'fault'       => 'No', // adjust if you have a custom rule
        'finish_time' => $finish_ts ?: null,
        'timestamp'   => (int)$row->last_updated,
        'crew'        => $assignee,
        'complainant' => $reporter,
        'downtime'    => $finish_ts ? hours_between((int)$row->date_submitted, $finish_ts) : '-',
        'status'      => $status_name
    ];
}

/** ---------- HTML ---------- */
ob_start();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Event List — <?= h(project_get_name($t_project_id)); ?></title>
<style>
  @page { margin: 24px 28px; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111; }
  .header {
      border-bottom: 1px solid #999; padding-bottom: 6px; margin-bottom: 10px;
      display: flex; justify-content: space-between; align-items: center;
  }
  .brand { font-weight: 600; }
  .event-title { font-size: 12px; }
  .muted { color: #666; }
  .block { margin: 10px 0 16px; }
  .rowline { border-top: 1px solid #cfcfcf; margin: 10px 0; }
  .grid {
      width: 100%; border-collapse: collapse;
  }
  .grid td {
      vertical-align: top; padding: 4px 6px;
  }
  .grid .label { width: 12%; color: #444; }
  .grid .colon { width: 1%; }
  .grid .value { width: 37%; }
  .num { width: 2%; text-align: right; padding-right: 8px; color: #666; }
  .section-head { display: flex; justify-content: space-between; }
  .section-head .muted { font-size: 10px; }
  .action {
      margin-top: 6px; padding: 8px; background: #fafafa; border: 1px solid #e5e5e5; border-radius: 4px;
  }
  .footer-note { margin-top: 6px; font-size: 10px; color: #666; }
</style>
</head>
<body>

<!-- <div class="header">
  <div class="brand"><?= h(project_get_name($t_project_id)); ?> • cknext bug tracker</div>
  <div class="event-title">Event <span class="muted"><?= date('d-M-Y H:i:s') ?></span></div>
</div> -->

<div id="pdf-header">
  <hr />
  <div style="float: left;">CKNextBT • CKNext Bug Tracker</div>
  <div style="float: right;">Event <?= date('Y M d, H:i:s') ?></div>
</div>

<div style="clear: both;"></div>
<hr />

<?php foreach($events as $ev): ?>
  <div class="block">
    <div class="section-head">
      <div><strong><?= (int)$ev['no'] ?></strong></div>
    </div>

    <table class="grid">
      <tr>
        <td class="num"></td>
        <td class="label">EventID</td><td class="colon">:</td><td class="value"><?= h($ev['event_id']) ?></td>
        <td class="label">Timestamp</td><td class="colon">:</td><td class="value"><?= fmt_dt($ev['timestamp']) ?></td>
      </tr>
      <tr>
        <td class="num"></td>
        <td class="label">Category</td><td class="colon">:</td><td class="value"><?= dash_if_empty(h($ev['system'])) ?></td>
        <td class="label">Assigned to</td><td class="colon">:</td><td class="value"><?= dash_if_empty(h($ev['crew'])) ?></td>
      </tr>
      <tr>
        <td class="num"></td>
        <td class="label">X-Ref</td><td class="colon">:</td><td class="value">-</td>
        <td class="label">Complainant</td><td class="colon">:</td><td class="value"><?= dash_if_empty(h($ev['complainant'])) ?></td>
      </tr>
      <tr>
        <td class="num"></td>
        <td class="label">Subject</td><td class="colon">:</td><td class="value"><?= dash_if_empty(h($ev['event'])) ?></td>
        <td class="label">Downtime</td><td class="colon">:</td><td class="value"><?= h($ev['downtime']) ?></td>
      </tr>

      <tr>
        <td class="num"></td>
        <td class="label">Status</td><td class="colon">:</td><td class="value"><?= h($ev['status']) ?></td>
        <!-- <td class="label">Downtime</td><td class="colon">:</td><td class="value"><?= h($ev['downtime']) ?></td> -->
      </tr>
      
      <!-- <tr>
        <td class="num"></td>
        <td class="label">Status</td><td class="colon">:</td>
        <td class="value" colspan="3">
          <div class="action"><?= h($ev['status']) ?></div>

          <div class="footer-note">Overall status derived from Mantis: <em><?= h($ev['status']) ?></em>.</div>
        </td>
      </tr> -->
      <tr>
        <td class="num"></td>
        <td class="label">DateOccur</td><td class="colon">:</td><td class="value"><?= fmt_dt($ev['date_occur']) ?></td>
        <td class="label">Fault</td><td class="colon">:</td><td class="value"><?= h($ev['fault']) ?></td>
      </tr>
      <tr>
        <td class="num"></td>
        <td class="label">FinishTime</td><td class="colon">:</td><td class="value"><?= fmt_dt($ev['finish_time']) ?></td>
        <td class="label"></td><td class="colon"></td><td class="value"></td>
      </tr>
    </table>
    <div class="rowline"></div>
  </div>
<?php endforeach; ?>

</body>
</html>
<?php
$html = ob_get_clean();

/** Dompdf */
require_once __DIR__ . '/../vendor/autoload.php';
$dompdf = new Dompdf\Dompdf(['isRemoteEnabled' => true]);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); // image 2 is portrait; switch to 'landscape' if you prefer
$dompdf->render();
$dompdf->stream('mantis-event-list.pdf', ['Attachment' => false]);
exit;
