document.addEventListener('DOMContentLoaded', function () {
    // Dummy data — replace with real counts later
    const labels7d = ['2025-08-14', '2025-08-15', '2025-08-16', '2025-08-17', '2025-08-18', '2025-08-19', '2025-08-20'];
    const open7d = [6, 5, 7, 4, 6, 5, 3];
    const resolved7d = [2, 3, 4, 5, 3, 4, 6];

    const statusLabels = ['new', 'assigned', 'acknowledged', 'confirmed', 'resolved', 'closed'];
    const statusCounts = [4, 3, 1, 2, 6, 2];

    // Stacked bar
    const ctx1 = document.getElementById('openResolvedChart');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: labels7d,
            datasets: [
                {
                    label: 'Open',
                    data: open7d,
                    backgroundColor: 'rgba(16,185,129,0.6)',
                    borderColor: 'rgb(16,185,129)',
                    borderWidth: 1
                },
                {
                    label: 'Resolved',
                    data: resolved7d,
                    backgroundColor: 'rgba(245,158,11,0.6)',
                    borderColor: 'rgb(245,158,11)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: { mode: 'index', intersect: false }
            }
        }
    });

    // Doughnut
    const ctx2 = document.getElementById('statusPie');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: [
                    'rgba(244,114,182,0.7)',
                    'rgba(99,102,241,0.7)',
                    'rgba(163,163,163,0.7)',
                    'rgba(20,184,166,0.7)',
                    'rgba(245,158,11,0.7)',
                    'rgba(34,197,94,0.7)'
                ],
                borderColor: [
                    'rgb(244,114,182)',
                    'rgb(99,102,241)',
                    'rgb(163,163,163)',
                    'rgb(20,184,166)',
                    'rgb(245,158,11)',
                    'rgb(34,197,94)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
});
