$(function() {
  $('#modal-info-close').on('click', hideInfoModal);
})

/** Se encarga de generar adecuadamente el contenido de las celdas de la datatable */
function renderColFecha({ data = "", prev = false, next = false, hoverable = false, warning = false }, isLastOne = false) {
  return `
    <span
      style="min-width: 100px"
      class="d-block text-center ${(data && !next) ? 'fw-bold' : 'opacity-75'}"
    >${data ?? '&hellip;'}</span>
    ${
      (data)
        ? `<span class="timeline marker marker-on ${warning ? 'warning' : ''} ${hoverable && 'hover'} ${!next && 'current'}"></span>`
        : ''
    }
    <span class="timeline prev ${warning ? 'warning' : ''} ${Boolean(data) ? 'has-prev' : 'no-prev' }"></span>
    ${
      !isLastOne
        ? `<span class="timeline next ${warning ? 'warning' : ''} ${Boolean(data) && 'has-next'} ${!next && 'half'}"></span>`
        : ''
    }
  `
}

/**
 * Se encarga de generar una Linea de tiempo para el modal de información.
 * @param {Object} data Informacion de la atención
 */
function generateModalTimeline(data) {
  // Datos de Movimientos
  const { admision, triage, hurge, egresoHurge, egreso } = data.steps;

  // Diferencias
  const colDifTriage = renderDiferenciaCell(triage.formatedDiff);
  const colDifAdmUrg = renderDiferenciaCell(admision.formatedDiff);
  const colDifUrgEgr = renderDiferenciaCell(hurge.formatedDiff);
  const colDifEgreso = renderDiferenciaCell(egresoHurge.formatedDiff)

  // Fechas
  const colTriage = renderColFecha({ data: triage.fecha, prev: true, next: admision.fecha })
  const colAdmi   = renderColFecha({ data: admision.fecha, prev: Boolean(triage.fecha), next: Boolean(hurge.fecha) })
  const colUrge   = renderColFecha({ data: hurge.fecha, prev: Boolean(admision.fecha), next: Boolean(egresoHurge.fecha) })
  const colEgrUrg = renderColFecha({
    data: egresoHurge.fecha,
    prev: Boolean(hurge.fecha),
    next: Boolean(egreso.fecha)
  })
  const colEgrAdm = renderColFecha({
    data: egreso.fecha,
    prev: Boolean(egresoHurge.fecha),
    next: false
  }, true)

  const celAttrs = 'class="small position-relative px-2"'
  return `
    <div
      class="position-relative flex timeline overflow-x-auto text-nowrap overflow-y-hidden scrollbars mb-1"
      style="font-size: 12px; height: 54px; align-content: center; color: #727272"
    >
      <div ${celAttrs}>
        <b class="small">Triage</b> <br />
        ${colTriage}
      </div>
      <div class="position-relative">${colDifTriage}</div>
      <div ${celAttrs}>
        <b class="small">Admisión</b> <br />
        ${colAdmi}
      </div>
      <div class="position-relative">${colDifAdmUrg}</div>
      <div ${celAttrs}>
        <b class="small">Urgencias</b> <br />
        ${colUrge}
      </div>
      <div class="position-relative">${colDifUrgEgr}</div>
      <div ${celAttrs}>
        <b class="small">Egreso Urg.</b> <br />
        ${colEgrUrg}
      </div>
      <div class="position-relative">${colDifEgreso}</div>
      <div ${celAttrs}>
        <b class="small">Egreso Adm.</b> <br />
        ${colEgrAdm}
      </div>
    </div>
  `;
}

function renderDiferenciaCell(data) {
  return `
    <span
      class="position-absolute d-block text-center bottom-0 small"
      style="transform: translateX(-50%); color: #cfcfcf"
    > ${data} </span>
  `
}

function hideInfoModal() {
  $('#modal-info-urgencias').hide();
  $('body').removeClass('overflow-y-hidden');
}


/** Establece los eventos y la función de filtrado para los diferentes triages. */ 
function setUpTriageFilter(table) {
  // Listener para radios y filtrado por triage
  document.querySelectorAll('input[type="radio"][name="filtro-triage"]').forEach(el => {
    el.addEventListener('change', () => TABLA.draw());
  })

  // Filtro para triage
  table.search.fixed('range', function (searchStr, data, index) {
    const query = document.querySelector('input[name="filtro-triage"]:checked')?.value;
    const parsedQuery = parseInt(query);

    if (isNaN(parsedQuery)) return true;
    const triage = data.clase_triage;

    return parsedQuery === triage;
  });
}

/** Establece los eventos y la función de filtrado para los diferentes typos (hombre,mujer,advertencia...). */ 
function setUpTypeFilter(table) {
  // Listener para radios y filtrado por triage
  document.querySelectorAll('input[type="checkbox"][name="filtro-type"]').forEach(el => {
    el.addEventListener('change', () => TABLA.draw());
  })

  // Filtro para triage
  table.search.fixed('filtertype', function (searchStr, data, index) {
    const checked = []; 
    document.querySelectorAll('input[name="filtro-type"]:checked').forEach(el => checked.push(el.value));
    if ([0,6].includes(checked.length)) return true;

    const cases = {
      "warning": (item) => item.alerta,
      "man": (item) => item.paciente.genero === 'M',
      "woman": (item) => item.paciente.genero === 'F',
      "admission": (item) => Boolean(item.steps.admision.fecha),
      "no-admission": (item) => ! Boolean(item.steps.admision.fecha),
      "no-emergency": (item) => Boolean(item.steps.admision.fecha) && ! Boolean(item.steps.hurge.fecha)
    };

    return checked.some(c => cases[c](data));
  });
}

/** Establece los eventos y la función de filtrado para los diferentes typos (hombre,mujer,advertencia...). */ 
function setUpDoctorFilter(table) {
  // Filtro para triage
  table.search.fixed('filterdoctor', function (searchStr, data, index) {
    const checked = []; 
    const totalMedicos = document.querySelectorAll('input[name="filtro-medico"]').length;
    document.querySelectorAll('input[name="filtro-medico"]:checked').forEach(el => checked.push(el.value));

    if ([0,totalMedicos].includes(checked.length)) return true;
    return checked.some(c => data.medico?.cod === c);
  });
}
