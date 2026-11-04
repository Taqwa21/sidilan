
import './bootstrap';
import 'bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';
import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    const letterCanvas = document.getElementById('letterChart');
    const statusCanvas = document.getElementById('statusChart');

    // Grafik jumlah surat berdasarkan periode
    if (letterCanvas) {
        const letterData = JSON.parse(
            letterCanvas.dataset.chartData || '[]'
        );

        const letterLabels = JSON.parse(
            letterCanvas.dataset.chartLabels || '[]'
        );

        new Chart(letterCanvas, {
            type: 'bar',
            data: {
                labels: letterLabels,
                datasets: [{
                    label: 'Jumlah Surat',
                    data: letterData,
                    backgroundColor: '#f79629',
                    borderRadius: 6,
                    maxBarThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Grafik jumlah surat berdasarkan status
    if (statusCanvas) {
        const statusData = JSON.parse(
            statusCanvas.dataset.chartData || '[]'
        );

        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Menunggu', 'Dalam Proses', 'Selesai'],
                datasets: [{
                    data: statusData,
                    backgroundColor: [
                        '#f79629',
                        '#3b82f6',
                        '#16a34a'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
