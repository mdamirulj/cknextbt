<?php
require_once('core.php');
auth_ensure_user_authenticated();

layout_page_header('Charts');
layout_page_begin();
?>
<div class="col-md-12">
  <h1 class="page-title">Chart</h1>

  <div class="row">
    <div class="col-md-8">
      <div class="widget-box">
        <div class="widget-header"><h4>Open vs Resolved</h4></div>
        <div class="widget-body">
          <canvas id="openResolvedChart" height="340"></canvas>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="widget-box">
        <div class="widget-header"><h4>Status Distribution</h4></div>
        <div class="widget-body">
          <canvas id="statusPie" height="240"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
# Load JS from same origin (allowed by CSP)
html_javascript_link('assets/js/chart.umd.min.js');
html_javascript_link('assets/js/my_custom_charts.js');
layout_page_end();
