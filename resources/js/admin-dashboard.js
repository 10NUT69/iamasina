import Chart from 'chart.js/auto';

window.Chart = Chart;
window.dispatchEvent(new CustomEvent('iaauto:admin-chart-ready'));
