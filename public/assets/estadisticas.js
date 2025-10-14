$(function() {
  renderTablasPromedios();
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

/** Genera las tablas donde se mostrará el promedio de atencion.  */
function renderTablasPromedios() {
  const idsTablasProm = {
    tri0: { n: 'Sin Triage', ids: [null, 't0', null, 'ae0'] },
    tri1: { n: 'Tri. 1', ids: ['ta1', 't1', 'te1', 'ae1'] },
    tri2: { n: 'Tri. 2', ids: ['ta2', 't2', 'te2', 'ae2'] },
    tri3: { n: 'Tri. 3', ids: ['ta3', 't3', 'te3', 'ae3'] },
    tri4: { n: 'Tri. 4', ids: ['ta4', 't4', 'te4', 'ae4'] },
    tri5: { n: 'Tri. 5', ids: ['ta5', 't5', 'te5', 'ae5'] },
    admi: { n: 'Tri. Admi.', ids: ['at', null, null, null] }
  }

  let admiTriage = '';
  let admiHurge = '';
  let triageEgre = '';
  let admiEgre = '';
  Object.entries(idsTablasProm).forEach(([, data]) => {
    const { n, ids } = data;
    const [admi, hurge, idTriageEgreso, idAdmisionEgreso] = ids;
    const template = `
        <div
          style="min-width: 70px"
          class="d-flex flex-column text-nowrap align-items-center py-1 px-3 bg-white border rounded small lh-sm"
        >
          <span class="fw-bold small">${n}</span>
          <span class="small" id="%s"></span>
        </div>
      `
    admiTriage = admi 
      ? admiTriage.concat(template.replace('%s', admi)) 
      : admiTriage;
    admiHurge = hurge 
      ? admiHurge.concat(template.replace('%s', hurge)) 
      : admiHurge;
    triageEgre = idTriageEgreso
      ? triageEgre.concat(template.replace('%s', idTriageEgreso)) 
      : triageEgre;
    admiEgre = idAdmisionEgreso
      ? admiEgre.concat(template.replace('%s', idAdmisionEgreso)) 
      : admiEgre;
  })
  $('#prom-admi-triage').html(admiTriage);
  $('#prom-admi-hurge').html(admiHurge);
  $('#prom-triage-egreso').html(triageEgre);
  $('#prom-admision-egreso').html(admiEgre);
}

/** Abre el modal para ver información más detallada sobre la atención del paciente */
function showInfoModal(data) {
  const { paciente, infoUrgencias, docn, medico, clase_triage } = data;

  if (data.steps.hurge.fecha === null) return;
  const { imagenes, intercon, evolucion, lab } = infoUrgencias;
  let [imgList, interList, evoList, labList] = ['', '', '', ''];

  // Setear información en el modal
  $('#modal-info-nombre').text(paciente.nombre);
  $('#modal-info-cc').text(paciente.documento);
  $('#modal-info-docn').text(docn);
  $('#modal-info-edad').html(`Edad: <b>${paciente.edad}</b>`);
  $('#modal-info-triage').text(clase_triage);
  $('#modal-info-medico').text(medico.nombre);

  const noItems = `
    <li class="list-group-item small text-muted no-before">
      <span>No hay registros para esta admisión</span>
    </li>
  `;

  // Imágenes
  if (imagenes?.length === 0) imgList = noItems;
  imagenes.forEach((item) => {
    imgList += `
        <li class="list-group-item small text-muted d-flex gap-2">
          <div class="flex-fill lh-1">
            <div class="d-flex gap-2">
              <div class="d-flex gap-2 flex-fill">
                <span class="fw-bold">${item.fechaSol}</span>
                <span>${item.horaSol}</span>
              </div>
              <span class="fw-bold">${item.tipo}</span>
              <span title="${item.ok ? 'Realizado' : 'Pendiente'}">
                ${item.ok ? '&check;' : '&#10061;'}
              </span>
            </div>
            <span style="font-size: 11px" class="text-lowercase">${item.nombre}</span>
          </div>
        </li>
      `
  })

  // Laboratorios
  if (lab?.length === 0) labList = noItems;
  lab.forEach((item) => {
    labList += `
        <li class="list-group-item small d-flex text-muted gap-2">
          <div class="d-flex flex-column lh-1 ms-2 flex-fill">
            <span class="fw-bold">${item.fechaSol}</span>
            <span>${item.horaSol}</span>
          </div>
          <span class="fw-bold">${item.diag}</span>
          <span title="${item.ok ? 'Realizado' : 'Pendiente'}">
            ${item.ok ? '&check;' : '&#10061;'}
          </span>
        </li>
      `
  })

  // Interconsultas
  if (intercon?.length === 0) interList = noItems;
  intercon.forEach((item) => {
    interList += `
        <li class="list-group-item small d-flex text-muted gap-2">
          <div title="Solicitud" class="d-flex flex-column lh-1 ms-2 flex-fill">
            <span class="fw-bold">${item.fechaSol}</span>
            <span>${item.horaSol}</span>
          </div>
          <div title="Realización" class="d-flex flex-column lh-1 ms-2 flex-fill">
            <span class="fw-bold">${item.fechaFin}</span>
            <span>${item.horaFin}</span>
          </div>
          <span title="${item.ok ? 'Realizado' : 'Pendiente'}">
            ${item.ok ? '&check;' : '&#10061;'}
          </span>
        </li>
      `
  })

  // Evolucones
  if (evolucion?.length === 0) evoList = noItems;
  evolucion.forEach((item) => {
    evoList += `
        <li class="list-group-item small d-flex text-muted gap-2">
          <div title="Solicitud" class="d-flex flex-column lh-1 ms-2 flex-fill">
            <span class="fw-bold">${item.fechaSol}</span>
            <span>${item.horaSol}</span>
          </div>
          <span title="${item.ok ? 'Realizado' : 'Pendiente'}">
            ${item.ok ? '&check;' : '&#10061;'}
          </span>
        </li>
      `
  })

  $('#modal-info-timeline').html(generateModalTimeline(data));
  $('#modal-info-imagenes').html(imgList);
  $('#modal-info-lab').html(labList);
  $('#modal-info-intercon').html(interList);
  $('#modal-info-evolucion').html(evoList);

  $('body').addClass('overflow-y-hidden');
  $('#modal-info-urgencias').show();
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
  document.querySelectorAll('input[type="checkbox"][name="filtro-medico"]').forEach(el => {
    el.addEventListener('change', () => TABLA.draw());
  });

  // Filtro para triage
  table.search.fixed('filterdoctor', function (searchStr, data, index) {
    const checked = []; 
    const totalMedicos = document.querySelectorAll('input[name="filtro-medico"]').length;
    document.querySelectorAll('input[name="filtro-medico"]:checked').forEach(el => checked.push(el.value));

    if ([0,totalMedicos].includes(checked.length)) return true;
    return checked.some(c => data.medico?.cod === c);
  });
}
