import ApexCharts from "apexcharts";

export default () => ({
	/** @type chart ApexCharts */
	chart: null,
	promedios: {},

	init() {
		this.promedios = this.defaultCounters();
		this.chart = new ApexCharts(
			this.$el.querySelector('div[chart]'),
			this.getOptionsForChat()
		);

		this.chart.render();
	},

	/**  
	 * Listener para evento de refresh de información en tabla.
	 * @param event {CustomEvent}  
	 */
	handleRefreshData(event) {
		this.setUpData(event.detail);
		this.updateChartSeries();
	},

	/**
	 * Ordena la información sumando los minutos y el total para cada uno de los 
	 * casos. No realiza el calculo del promedio.
	 * @param data {Object[]}
	 */
	setUpData(data) {
		this.promedios = this.defaultCounters();
		data.forEach(({ clase_triage, steps }) => {
			const { triage, admision, hurge, egreso, digiturno } = steps;

			// Triage contra admisión
			if (clase_triage && admision.fecha) {
				this.promedios.triageAdmision.data[clase_triage][0] += triage.diff / 60 / 60;
				this.promedios.triageAdmision.data[clase_triage][1]++;
			}

			// Admisión contra Hoja de Urgencias
			if (admision.fecha && hurge.fecha) {
				this.promedios.admisionHurge.data[clase_triage][0] += admision.diff / 60 / 60;
				this.promedios.admisionHurge.data[clase_triage][1]++;
			}

			if (!egreso.fecha) return;

			// Cálculos triage vs egreso
			if (clase_triage) {
				this.promedios.triageEgreso.data[clase_triage][0] += (egreso.timestamp - triage.timestamp) / 60 / 60;
				this.promedios.triageEgreso.data[clase_triage][1]++;
			}

			if (digiturno && digiturno.fecha) {
				this.promedios.digiturnoEgreso.data[clase_triage][0] += (egreso.timestamp - digiturno.timestamp) / 60 / 60;
				this.promedios.digiturnoEgreso.data[clase_triage][1]++;
			}

			// Cálculo de admisión vs egreso
			if (admision.fecha) {
				this.promedios.admisionEgreso.data[clase_triage][0] += (egreso.timestamp - admision.timestamp) / 60 / 60;
				this.promedios.admisionEgreso.data[clase_triage][1]++;
			}
		});
	},

	/** Actualiza las series de la gráfica con los nuevos datos */
	updateChartSeries() {
		this.chart.updateOptions({
			series: this.buildSeriesForChart(),
			annotations: this.buildAnnotatinsForChart()
		});
	},

	/** Genera la configuración por defecto para la gráfica. */
	getOptionsForChat() {
		return {
			series: this.buildSeriesForChart(),
			annotations: this.buildAnnotatinsForChart(),
			chart: {
				type: 'bar',
				height: 700,
			},
			plotOptions: {
				bar: {
					horizontal: true,
					columnWidth: '300px',
					borderRadius: 5,
					borderRadiusApplication: 'end',
				},

			},
			stroke: {
				show: true,
				width: 2,
				colors: ['transparent']
			},
			xaxis: {
				categories: [
					this.promedios.digiturnoEgreso.title,
					this.promedios.triageAdmision.title,
					this.promedios.admisionHurge.title,
					this.promedios.admisionEgreso.title,
					this.promedios.triageEgreso.title,
				],
				title: {
					text: 'Horas'
				}
			},
			yaxis: {},
			fill: {
				opacity: 1
			},
			tooltip: {
				shared: true,
				intersect: false,
				y: {
					formatter: (val, { seriesIndex, dataPointIndex })  =>{
						const totalItems = Object.values(this.promedios)[dataPointIndex].data[seriesIndex][1];

						const parsed = parseFloat(val);
						const withMinutes = (parsed < 1 && parsed > 0)
							? ` &#10141; ${parsed * 60} Minutos`
							: '';

						return val + " Horas" + withMinutes + " | Registros: " + totalItems;
					}
				}
			}
		};
	},

	/** Genera las series para la gráfica */
	buildSeriesForChart() {
		const seriesName = {
			0: 'Sin Triage',
			1: 'Triage 1',
			2: 'Triage 2',
			3: 'Triage 3',
			4: 'Triage 4',
			5: 'Triage 5'
		};

		const series = Object.entries(seriesName).reduce((prev, [id, name]) => {
			id = parseInt(id);

			prev.push({
				name: name,
				data: [
					// Ojo *** Deben estar en el mismo orden que en `defaultCounters()`
					this.calcularPromedio(this.promedios.digiturnoEgreso.data[id]),
					this.calcularPromedio(this.promedios.triageAdmision.data[id]),
					this.calcularPromedio(this.promedios.admisionHurge.data[id]),
					this.calcularPromedio(this.promedios.admisionEgreso.data[id]),
					this.calcularPromedio(this.promedios.triageEgreso.data[id]),
				]
			});

			return prev;
		}, []);

		return series;
	},

	buildAnnotatinsForChart() {
		const yaxis = Object.keys(this.promedios).map((key) => {
			let totalItems = 0;
			const averageData = Object.values(this.promedios[key].data)
			const totalMinutes = averageData.reduce((x, [minutos, total]) => {
				if (total === 0) return x;

				x += parseFloat(this.calcularPromedio([minutos, total]));
				totalItems++;
				return x;
			}, 0);

			const average = totalMinutes / totalItems;
			const withMinutes = average < 1
				? ` |  ${(average * 60).toFixed(1)} Minutos`
				: '';
			return {
				y: this.promedios[key].title,
				label: {
					text: `Promedio: ${average.toFixed(1)} horas${withMinutes}`
				}
			}
		});

		return { yaxis };
	},

	/** Helper para calcular promedio */
	calcularPromedio([dividendo, divisor]) {
		const x = dividendo / divisor;
		return isNaN(x) ? 0 : x.toFixed(1);
	},

	defaultCounters() {
		return {
			digiturnoEgreso: {
				title: 'Digiturno vs Egreso',
				data: { 0: [0, 0], 1: [0, 0], 2: [0, 0], 3: [0, 0], 4: [0, 0], 5: [0, 0] },
			},
			triageAdmision: {
				title: 'TRIAGE vs Admisión',
				data: { 0: [0, 0], 1: [0, 0], 2: [0, 0], 3: [0, 0], 4: [0, 0], 5: [0, 0] },
			},
			admisionHurge: {
				title: 'Admisión vs Hoja Urgencias',
				data: { 0: [0, 0], 1: [0, 0], 2: [0, 0], 3: [0, 0], 4: [0, 0], 5: [0, 0] },
			},
			// El 0 en el triage son para las admisiones SIN triage
			admisionEgreso: {
				title: 'Admisión vs Egreso',
				data: { 0: [0, 0], 1: [0, 0], 2: [0, 0], 3: [0, 0], 4: [0, 0], 5: [0, 0] },
			},
			triageEgreso: {
				title: 'TRIAGE vs Egreso',
				data: { 0: [0, 0], 1: [0, 0], 2: [0, 0], 3: [0, 0], 4: [0, 0], 5: [0, 0] },
			},
		}
	}
});
