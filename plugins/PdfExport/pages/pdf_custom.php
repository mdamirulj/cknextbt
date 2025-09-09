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
require_api('custom_field_api.php');
require_api('category_api.php');
require_api('user_api.php');
require_api('bugnote_api.php');
require_api('database_api.php');      // <-- needed for db_get_table / db_query

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

/**
 * Read the custom field TEXT (fallback to VALUE if TEXT is empty) for a bug.
 * @param int $bug_id
 * @param int $field_id   Custom field id, e.g. 3 for "Action"
 * @return string
 */
function get_cf_text_for_bug(int $bug_id, int $field_id): string {
    $t_table = db_get_table('mantis_custom_field_string_table');
    $sql = 'SELECT COALESCE(NULLIF(text, \'\'), value) AS content
            FROM ' . $t_table . '
            WHERE bug_id = ' . db_param() . ' AND field_id = ' . db_param() . '
            LIMIT 1';
    $rs = db_query($sql, [$bug_id, $field_id]);

    if (db_num_rows($rs) < 1) {
        error_log("[PDF] CF row NOT FOUND for bug_id={$bug_id} field_id={$field_id}");
        return '';
    }
    $row = db_fetch_array($rs);
    $content = trim((string)$row['content']);

    if ($content === '') {
        error_log("[PDF] CF row EMPTY for bug_id={$bug_id} field_id={$field_id}");
    } else {
        error_log("[PDF] CF row FOUND for bug_id={$bug_id} field_id={$field_id} len=" . strlen($content));
    }
    return $content;
}

# --- Get the current filter rows (your existing code) ---
$t_project_id = helper_get_current_project();
$f_page_number = 1; $t_per_page = -1;
$t_page_count = 0; $t_bug_count = 0;

$t_rows = filter_get_bug_rows(
    $f_page_number, $t_per_page, $t_page_count, $t_bug_count,
    null, $t_project_id, null, /* user id */ null, true
);

$t_project_id = helper_get_current_project();

/** Use active "View Issues" filter */
$f_page_number = 1; $t_per_page = -1; // -1 => all in filter
$t_page_count = 0; $t_bug_count = 0;

/** Collect a light structure we can render cleanly */
$events = [];
$counter = 1;

// Resolve field id by name (or hardcode the id if you know it)
$action_field_id = custom_field_get_id_from_name('Action');
if ($action_field_id === false) {
    // fallback if your field is known to be id=3
    $action_field_id = 3;
    error_log('[PDF] CF "Action" not found by name; falling back to id=3');
}

foreach ($t_rows as $row) {
    $bug_id        = (int)$row->id;
    $category_name = $row->category_id ? category_get_name((int)$row->category_id) : '-';
    $assignee      = $row->handler_id ? user_get_name((int)$row->handler_id) : '-';
    $reporter      = $row->reporter_id ? user_get_name((int)$row->reporter_id) : '-';
    $status_name   = get_enum_element('status', (int)$row->status);

    // Action: latest visible bugnote (fallback "-")
    // $action_text = '-';
    // $notes = bugnote_get_all_visible_bugnotes($bug_id, auth_get_current_user_id(), 0);
    
    // if (!empty($notes)) {
    //     // take latest note text
    //     $last = end($notes);
    //     $action_text = string_display_links($last->note ?? '-');
    // }

    // --- read from custom field table ---
    $action_text = get_cf_text_for_bug($bug_id, (int)$action_field_id);
    if ($action_text === '') {
        error_log("[PDF] No Action CF for bug {$bug_id} (field {$action_field_id})");
        $action_text = '-';
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
  <div style="float: left;">CAAM • Trouble Ticket</div>
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
      <tr>
        <td class="num"></td>
        <td class="label">Event</td><td class="colon">:</td><td class="value"><?= dash_if_empty(h($ev['event'])) ?></td>
      </tr>
      <tr>
      <td class="num"></td>
      <td class="label">Action</td>
      <td class="colon">:</td>
      <!-- <td class="value" colspan="5" style="text-align:">
        Runway 1 in used : ILS 14L DVOR/DME : NOTAM : A3842/25
        <br>
        Runway 2 in used : ILS 14R Active Server : No.01
        <br>
        Runway 3 in used : ILS 15 CMS: Serviceable
        <br>
        Obs. Light Bukit Sg Linau : SVC Obs. Light Bukit Lada: SVC
        Times Check Obs.Light 1530 UTC
      </td> -->

      <td class="value" colspan="5">
    <?= nl2br(h($ev['action'])) ?>
  </td>
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
