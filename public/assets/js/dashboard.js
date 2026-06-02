document.addEventListener('DOMContentLoaded', () => {
  if (window.AOS) {
    AOS.init({
      duration: 520,
      easing: 'ease-out-cubic',
      once: true,
      offset: 24
    });
  }

  if (!window.Chart) {
    return;
  }

  Chart.defaults.font.family = "'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
  Chart.defaults.color = '#667085';

  const velocityCanvas = document.getElementById('velocityChart');
  if (velocityCanvas) {
    new Chart(velocityCanvas, {
      type: 'line',
      data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [
          {
            label: 'Completed tasks',
            data: [32, 44, 38, 52, 61, 48, 68],
            borderColor: '#2357a6',
            backgroundColor: 'rgba(35, 87, 166, 0.14)',
            fill: true,
            tension: 0.42,
            pointRadius: 3,
            pointHoverRadius: 5
          },
          {
            label: 'Client approvals',
            data: [12, 18, 16, 24, 22, 30, 34],
            borderColor: '#16a085',
            backgroundColor: 'rgba(22, 160, 133, 0.1)',
            fill: true,
            tension: 0.42,
            pointRadius: 3,
            pointHoverRadius: 5
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          intersect: false,
          mode: 'index'
        },
        plugins: {
          legend: {
            labels: {
              boxWidth: 10,
              usePointStyle: true
            }
          }
        },
        scales: {
          x: {
            grid: {
              display: false
            }
          },
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(102, 112, 133, 0.14)'
            },
            border: {
              display: false
            }
          }
        }
      }
    });
  }

  const statusCanvas = document.getElementById('statusChart');
  if (statusCanvas) {
    new Chart(statusCanvas, {
      type: 'doughnut',
      data: {
        labels: ['Active', 'Review', 'On hold'],
        datasets: [
          {
            data: [58, 27, 15],
            backgroundColor: ['#2357a6', '#16a085', '#f59e0b'],
            borderWidth: 0,
            hoverOffset: 6
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  }
});
