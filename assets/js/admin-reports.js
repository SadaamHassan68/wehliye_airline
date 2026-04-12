/* Wehliye Admin — Chart.js reports (reads #ofbms-report-data JSON) */
(function () {
  'use strict';

  var el = document.getElementById('ofbms-report-data');
  if (!el || typeof Chart === 'undefined') {
    return;
  }

  var data;
  try {
    data = JSON.parse(el.textContent || '{}');
  } catch (e) {
    return;
  }

  Chart.defaults.font.family = "'DM Sans', system-ui, sans-serif";
  Chart.defaults.color = '#64748b';

  var navy = '#0f172a';
  var sky = '#2563eb';
  var teal = '#0d9488';
  var amber = '#d97706';
  var rose = '#e11d48';
  var slate = '#94a3b8';

  var palette = [sky, teal, amber, rose, '#7c3aed', '#059669', slate, navy];

  function withAlpha(hex, a) {
    var m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    if (!m) {
      return hex;
    }
    return 'rgba(' + parseInt(m[1], 16) + ',' + parseInt(m[2], 16) + ',' + parseInt(m[3], 16) + ',' + a + ')';
  }

  var combo = data.combo;
  if (combo && combo.labels && combo.labels.length) {
    new Chart(document.getElementById('ofbmsChartCombo'), {
      type: 'bar',
      data: {
        labels: combo.labels,
        datasets: [
          {
            type: 'line',
            label: 'Paid revenue ($)',
            data: combo.revenue,
            borderColor: sky,
            backgroundColor: withAlpha(sky, 0.12),
            fill: true,
            tension: 0.35,
            yAxisID: 'y',
            pointRadius: 3,
            pointHoverRadius: 5
          },
          {
            type: 'bar',
            label: 'New bookings',
            data: combo.bookings,
            backgroundColor: withAlpha(teal, 0.55),
            borderRadius: 6,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: 'top', labels: { usePointStyle: true, padding: 16 } },
          tooltip: {
            callbacks: {
              label: function (ctx) {
                var v = ctx.parsed.y;
                if (ctx.dataset.yAxisID === 'y') {
                  return ctx.dataset.label + ': $' + (typeof v === 'number' ? v.toFixed(2) : v);
                }
                return ctx.dataset.label + ': ' + v;
              }
            }
          }
        },
        scales: {
          x: { grid: { display: false } },
          y: {
            position: 'left',
            title: { display: true, text: 'Revenue ($)' },
            ticks: {
              callback: function (val) {
                return '$' + val;
              }
            }
          },
          y1: {
            position: 'right',
            title: { display: true, text: 'Bookings' },
            grid: { drawOnChartArea: false }
          }
        }
      }
    });
  }

  function doughnut(canvasId, slice) {
    if (!slice || !slice.labels || !slice.labels.length) {
      return;
    }
    var node = document.getElementById(canvasId);
    if (!node) {
      return;
    }
    new Chart(node, {
      type: 'doughnut',
      data: {
        labels: slice.labels,
        datasets: [
          {
            data: slice.values,
            backgroundColor: slice.labels.map(function (_, i) {
              return palette[i % palette.length];
            }),
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 8
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '58%',
        plugins: {
          legend: { position: 'bottom', labels: { padding: 14, usePointStyle: true } }
        }
      }
    });
  }

  doughnut('ofbmsChartStatus', data.status);
  doughnut('ofbmsChartPayment', data.payment);

  var routes = data.routes;
  if (routes && routes.labels && routes.labels.length) {
    new Chart(document.getElementById('ofbmsChartRoutes'), {
      type: 'bar',
      data: {
        labels: routes.labels,
        datasets: [
          {
            label: 'Income ($)',
            data: routes.values,
            backgroundColor: withAlpha(sky, 0.75),
            borderRadius: 8
          }
        ]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (ctx) {
                return '$' + (typeof ctx.parsed.x === 'number' ? ctx.parsed.x.toFixed(2) : ctx.parsed.x);
              }
            }
          }
        },
        scales: {
          x: {
            ticks: {
              callback: function (val) {
                return '$' + val;
              }
            }
          },
          y: { grid: { display: false } }
        }
      }
    });
  }

  var load = data.load;
  if (load && load.labels && load.labels.length) {
    new Chart(document.getElementById('ofbmsChartLoad'), {
      type: 'bar',
      data: {
        labels: load.labels,
        datasets: [
          {
            label: 'Load %',
            data: load.values,
            backgroundColor: load.values.map(function (v) {
              if (v >= 85) {
                return withAlpha(teal, 0.85);
              }
              if (v >= 50) {
                return withAlpha(sky, 0.75);
              }
              return withAlpha(amber, 0.65);
            }),
            borderRadius: 8
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function (ctx) {
                return 'Load: ' + ctx.parsed.y + '%';
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            title: { display: true, text: 'Load %' },
            ticks: { callback: function (val) { return val + '%'; } }
          },
          x: { grid: { display: false } }
        }
      }
    });
  }
})();
