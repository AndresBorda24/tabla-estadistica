export default () => ({
	info: null,
	/** @type {?HTMLDialogElement} */
	modal: null,
	timeline: '',

	init() {
		this.modal = this.$el;
	},

	/** @param event {CustomEvent} */
	handleData(event) {
		this.info = event.detail;
		this.modal?.showModal();
		this.timeline = generateModalTimeline(this.info);
	},

	/**
	 * Sirve para determinar si la informacion de la atenci'on de 
	 * urgencias del paciente tiene o no algo de info
	 */
	get emptyInfo() {
		if (this.info === null) return true;

		if (
      ! this.info.infoUrgencias.tratamiento?.trim().length &&
      ! this.info.infoUrgencias.lab.length &&
      ! this.info.infoUrgencias.imagenes.length &&
      ! this.info.infoUrgencias.intercon.length &&
      ! this.info.infoUrgencias.evolucion.length 
    ) return true;

		return false;
	}
});
