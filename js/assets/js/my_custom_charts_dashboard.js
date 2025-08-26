(function () {
  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  onReady(function () {
    // Ensure Chart is loaded
    if (!window.Chart) {
      console.error('[my_custom_charts] Chart.js not loaded');
      return;
    }

    // Register datatable plugin:
    if (window.ChartDataLabels) {
      Chart.register(ChartDataLabels);
    }

    var c1 = document.getElementById('myOpenResolvedChart');
    var c2 = document.getElementById('myStatusChart');

    if (!c1) { console.warn('[my_custom_charts] #myOpenResolvedChart not found'); return; }
    if (!c2) { console.warn('[my_custom_charts] #myStatusChart not found'); return; }

    // Avoid double-create on auto-refresh
    if (window._mantisMyViewCharts) {
      window._mantisMyViewCharts.forEach(function (ch) { try { ch.destroy(); } catch (e) {} });
    }
    window._mantisMyViewCharts = [];

    // Dummy data
    var labels7d    = ['2025-08-20','2025-08-21','2025-08-22','2025-08-23','2025-08-24','2025-08-25','2025-08-26'];
    var opened7d    = [6,5,7,4,6,5,3];
    var resolved7d  = [2,3,4,5,3,4,6];

    var statusLabels = ['new','assigned','acknowledged','confirmed','resolved','closed'];
    var statusCounts = [4,3,1,2,6,2];

    // Chart 1
    var ch1 = new Chart(c1.getContext('2d'), {
      type: 'bar',
      data: {
        labels: labels7d,
        datasets: [
          { label: 'Open', data: opened7d, borderWidth: 1 },
          { label: 'Resolved', data: resolved7d, borderWidth: 1 }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'top' },
          title: { display: true, text: 'Open vs Resolved (Last 7 days)' }
        },
        scales: {
          y: { beginAtZero: true, ticks: { precision: 0 } }
        }
      }
    });
    window._mantisMyViewCharts.push(ch1);

    // Chart 2
    var ch2 = new Chart(c2.getContext('2d'), {
      type: 'doughnut',
      data: { labels: statusLabels, datasets: [{ data: statusCounts }] },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'right' },
          title: { display: true, text: 'Status Distribution' }
        },
        cutout: '55%'
      }
    });
    window._mantisMyViewCharts.push(ch2);

    console.log('[my_custom_charts] charts ready');
  });
})();
