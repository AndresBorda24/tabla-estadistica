export default () => ({
	medicos: [],

	/** @param event {CustomEvent} */
	handleData(event) {
		const data = event.detail;
		const medicos = [];
		const listMedicos = {};

		// Conteo de medicos
	  data.forEach(({ medico }) => {
	    if (!medico) return;
	    const { cod, nombre } = medico;

	    if (!listMedicos.hasOwnProperty(cod)) {
	      listMedicos[cod] = {
	      	cod,
	        nombre: nombre,
	        total: 0,
	      };
	    }

	    listMedicos[cod].total++;
	  });

		Object
			.keys(listMedicos)
			.sort((a, b) => a.localeCompare(b))
			.forEach(cod => medicos.push(listMedicos[cod]))
		;

		this.medicos = medicos;
	}
});
