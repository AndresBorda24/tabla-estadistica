import './app.css';
import ModalUrgencias from './components/ModalUrgencias';
import TriageMinDiffChart from './components/TriageMinDiffChart';
import Alpine from 'alpinejs';
window.Alpine = Alpine
 
document.addEventListener('alpine:init', () => {
	Alpine.data('ModalUrgencias', ModalUrgencias);
	Alpine.data('TriageMinDiffChart', TriageMinDiffChart);
});

Alpine.start();
