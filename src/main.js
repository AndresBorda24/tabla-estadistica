import './app.css';
import TriageMinDiffChart from './components/TriageMinDiffChart';
import Alpine from 'alpinejs';
window.Alpine = Alpine
 
document.addEventListener('alpine:init', () => {
	Alpine.data('TriageMinDiffChart', TriageMinDiffChart);
});

Alpine.start();
