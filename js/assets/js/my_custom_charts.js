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

    const ctx = document.getElementById('complainerPieChart').getContext('2d');

    // Dummy data for each filter
    const chartDataSets = {
        day: {
            labels: ['Faruqi', 'Fahmi', 'Zam', 'Mawi', 'Fatehah'],
            data: [5, 4, 3, 2, 1]
        },
        week: {
            labels: ['Fahmi', 'Faruqi', 'Fatehah', 'Mawi', 'Zam'],
            data: [20, 15, 12, 10, 8]
        },
        month: {
            labels: ['Faruqi', 'Zam', 'Fahmi', 'Mawi', 'Fatehah'],
            data: [50, 45, 40, 30, 25]
        },
        year: {
            labels: ['Zam', 'Fatehah', ' Faruqi', 'Mawi', 'Fahmi'],
            data: [200, 180, 160, 150, 120]
        }
    };

    // Professional color palette (no blue)
    const colors = [
        '#7CB5EC', // Soft Blue
        '#F15C80', // Soft Pink
        '#90ED7D', // Soft Green
        '#F7A35C', // Soft Orange
        '#A7A9AC'  // Soft Gray
    ];

    // Initial chart
    let chart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: chartDataSets.month.labels,
            datasets: [{
                label: 'Total Tickets',
                data: chartDataSets.month.data,
                backgroundColor: colors,
                borderColor: '#ffffff',
                borderWidth: 2,
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                ChartDataLabels,
                title: {
                    display: true,
                    text: 'Top 5 Complainers',
                    font: {
                        size: 18,
                        weight: 'bold'
                    },
                    padding: {
                        top: 10,
                        bottom: 30
                    }
                },
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.label || '';
                            let value = context.parsed;
                            return label + ': ' + value + ' tickets';
                        }
                    }
                },
                datalabels: {
                    color: '#000',
                    font: { weight: 'bold' },
                    formatter: function (value) {
                        return value + ' tickets'; // Show total tickets
                    }
                },
            }
        },
        plugins: [ChartDataLabels]
    });

    // Filter change event
    // document.getElementById('complainerFilter').addEventListener('change', function () {
    //     const selected = this.value;
    //     chart.data.labels = chartDataSets[selected].labels;
    //     chart.data.datasets[0].data = chartDataSets[selected].data;
    //     chart.update();
    // });





    // Time Taken to Resolve Chart (hours)
    const ctx5 = document.getElementById('ticketResolutionChart').getContext('2d');

    const labels = [
        'TCK-001', 'TCK-002', 'TCK-003', 'TCK-004', 'TCK-005',
        'TCK-006', 'TCK-007', 'TCK-008', 'TCK-009', 'TCK-010',
        'TCK-011', 'TCK-012', 'TCK-013', 'TCK-014', 'TCK-015',
        'TCK-016', 'TCK-017', 'TCK-018', 'TCK-019', 'TCK-020'
    ];

    const categories = ["General", "Technical", "Finance", "Dispute", "Other"];
    const resolvedBy = ["KC", "Raul", "Amirul", "Nazmie", "Jamari"];
    const priorities = ["1", "2", "3"];

    // Dummy data generator for given month & year
    function getDummyData(month, year) {
        const randomData = [];
        const metadata = [];

        for (let i = 0; i < labels.length; i++) {
            randomData.push((Math.random() * 24).toFixed(1)); // Hours
            metadata.push({
                category: categories[Math.floor(Math.random() * categories.length)],
                resolvedBy: resolvedBy[Math.floor(Math.random() * resolvedBy.length)],
                priority: priorities[Math.floor(Math.random() * priorities.length)]
            });
        }

        return { randomData, metadata };
    }

    let { randomData, metadata } = getDummyData('January', '2024');

    let chart1 = new Chart(ctx5, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Hours to Resolve',
                data: randomData,
                backgroundColor: 'rgba(76, 175, 80, 0.7)',
                borderColor: 'rgba(56, 142, 60, 1)',
                borderWidth: 1,
                hoverBackgroundColor: 'rgba(255, 152, 0, 0.8)',
                hoverBorderColor: 'rgba(245, 124, 0, 1)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Time Taken to Resolve (Hours)',
                    font: { size: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let i = context.dataIndex;
                            return [
                                "Hours: " + context.parsed.y + " hours",
                                "Category: " + metadata[i].category,
                                "Resolved By: " + metadata[i].resolvedBy,
                                "Priority: " + metadata[i].priority
                            ];
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Hours to Resolve' }
                },
                x: {
                    title: { display: true, text: 'Ticket Ref No' }
                }
            }
        }
    });

    function updateChart() {
        const selectedMonth = document.getElementById('monthFilter').value;
        const selectedYear = document.getElementById('yearFilter').value;
        const newData = getDummyData(selectedMonth, selectedYear);

        metadata = newData.metadata;
        chart1.data.datasets[0].data = newData.randomData;
        chart1.update();
    }

    document.getElementById('monthFilter').addEventListener('change', updateChart);
    document.getElementById('yearFilter').addEventListener('change', updateChart);

});
