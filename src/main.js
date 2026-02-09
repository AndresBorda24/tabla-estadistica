import './app.css';
import FiltroMedicos from './components/FiltroMedicos';
import ModalUrgencias from './components/ModalUrgencias';
import TriageMinDiffChart from './components/TriageMinDiffChart';
import Alpine from 'alpinejs';

if (import.meta.env.DEV) window.Alpine = Alpine;
 
document.addEventListener('alpine:init', () => {
	Alpine.data('FiltroMedicos', FiltroMedicos);
	Alpine.data('ModalUrgencias', ModalUrgencias);
	Alpine.data('TriageMinDiffChart', TriageMinDiffChart);
});

Alpine.start();
