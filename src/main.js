import './app.css';
import Alpine from 'alpinejs';
window.Alpine = Alpine
 
document.addEventListener('alpine:init', () => {
	Alpine.data('test', () => ({
		init() {
			console.log('esto es un componente de alpinejs');
		}
	}));
})

Alpine.start()
