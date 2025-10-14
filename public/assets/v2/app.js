$("#cargaInfo").hide();
let TABLA;
const myDate = $("#fecha");

$(async function () {
  const today = new Date().toLocaleDateString("en-CA").substring(0, 10);
  myDate.val(today);
  myDate.attr("max", today);

  TABLA = listar([]);
  setUpTriageFilter(TABLA);
  setUpTypeFilter(TABLA);
  // Los eventos para el filtrado por medico se establecen más adelante ...

  TABLA.on("dblclick", "tbody tr", function () {
    const data = TABLA.row(this).data();
    showInfoModal(data); // estadisticas.js
  });

  const informacion = await fetchDatosEstadistica(today);
  const { data, contadores } = informacion;

  if (data.length == 0) return $("#full-loader").remove();
  cagarDatos({ data, contadores });
});

function cagarDatos({ data, contadores }) {
  // Medicos
  calcularAtencionesPorMedico(data);
  setUpDoctorFilter(TABLA);
  // Promedios de atención
  calcularPromedios(data);
  // Conteo de los triage
  setContadores(contadores);

  $("#full-loader").remove();
  TABLA.clear();
  TABLA.rows.add(data);
  TABLA.draw();
  
  // Notificamos que la información ha sido cargada y compartimos esa info.
  document.dispatchEvent(
    new CustomEvent('table-data-refresh', { detail: data })
  );
}

/** Se encarga de pone los valores de "Atenciones x Medico" */
function calcularAtencionesPorMedico(data) {
  const arrayMedicos = {};
  const arrayMedicosSorted = {};

  data.forEach(({ medico }) => {
    if (!medico) return;
    const { cod, nombre } = medico;

    if (!arrayMedicos.hasOwnProperty(cod)) {
      arrayMedicos[cod] = {
        nombre: nombre,
        total: 0,
      };
    }

    arrayMedicos[cod].total++;
  });

  // Ordenamos medicos alfabeticamente
  Object.keys(arrayMedicos).sort((a, b) => a.localeCompare(b)).forEach(cod => {
    arrayMedicosSorted[cod] = arrayMedicos[cod];
  });

  const contenido = document.getElementById("contenido");
  contenido.innerHTML = "";

  Object.entries(arrayMedicosSorted).forEach(([cod, d]) => {
    contenido.innerHTML += `
      <div>
        <input type="checkbox" name="filtro-medico" id="cc-${cod}" value="${cod}" class="d-none">
        <label class="filtro-type-card-side" for="cc-${cod}" title="${d.nombre} - ${d.total}">
          <span class="filtro-type-label-side">${cod}</span>
          <div class="filtro-type-content-side">
            <span class="filtro-type-icon-side">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M12 11a4 4 0 1 0 0-8a4 4 0 0 0 0 8m-3.38 1.922l.374-.549a7 7 0 0 0 2.895.627h.223a7 7 0 0 0 2.505-.464l.263.386c.122.18.245.53.327.973c.08.427.102.832.087 1.055a.8.8 0 0 0 .04.3h-.378a.76.76 0 0 0-.688.439l-.691 1.48a.76.76 0 0 0 .689 1.081H15.4v-1.5h1.25v1.5h1.084a.76.76 0 0 0 .689-1.081l-.69-1.48a.76.76 0 0 0-.69-.439h-.293a.8.8 0 0 0 .04-.2c.026-.378-.012-.912-.108-1.43c-.093-.502-.262-1.101-.562-1.542l-.049-.072a2 2 0 0 1 .152-.006A4.777 4.777 0 0 1 21 16.777V21H3v-4.223c0-2.52 1.95-4.584 4.424-4.764l-.044.065c-.591.869-.681 1.946-.608 2.81c.025.297.07.59.132.866a1.5 1.5 0 1 0 1.47-.302a5 5 0 0 1-.108-.69c-.06-.706.04-1.379.354-1.84"/></svg>
            </span> 
            <span class="filtro-type-value-side" id="contador-${cod}">${d.total}</span>
          </div>
        </label>
      </div>
    `;
  });
}

/**
 * Establece el valor de los contadores.
 * @param {Object} contadores Objeto con el conteo de los diferentes items
 */
function setContadores(contadores) {
  $("#nt0").text(contadores.triage[0]);
  $("#nt1").text(contadores.triage[1]);
  $("#nt2").text(contadores.triage[2]);
  $("#nt3").text(contadores.triage[3]);
  $("#nt4").text(contadores.triage[4]);
  $("#nt5").text(contadores.triage[5]);

  $("#contador-no-emergency").html(contadores.sinHurge);
  $("#contador-no-admission").html(contadores.sinAdmision);
  $("#contador-admission").html(contadores.general);
  $("#contador-warning").html(contadores.alertas);
  $("#contador-man").html(contadores.hombres);
  $("#contador-woman").html(contadores.mujeres);
}

/**
 * Aquí se canculan y cargan los promedios en minutos entre los diferentes tipos
 * de triage.
*/
function calcularPromedios(data) {
  const promedios = {
    triageAdmision: {1:[0,0], 2:[0,0], 3:[0,0], 4:[0,0], 5:[0,0]},
    triageEgreso:   {1:[0,0], 2:[0,0], 3:[0,0], 4:[0,0], 5:[0,0]},
    // El 0 en el triage son para las admisiones SIN triage
    admisionEgreso: {0:[0,0],1:[0,0], 2:[0,0], 3:[0,0], 4:[0,0], 5:[0,0]},
    admisionHurge:  {0:[0,0],1:[0,0], 2:[0,0], 3:[0,0], 4:[0,0], 5:[0,0]}
  };

  data.forEach(({ clase_triage, steps }) => {
    const {triage,admision,hurge,egreso} = steps;

    // Triage contra admisión
    if (clase_triage && admision.fecha) {
      promedios.triageAdmision[clase_triage][0] += triage.diff / 60;
      promedios.triageAdmision[clase_triage][1]++;
    }

    // Admisión contra Hoja de Urgencias
    if (admision.fecha && hurge.fecha) {
      promedios.admisionHurge[clase_triage][0] += admision.diff / 60;
      promedios.admisionHurge[clase_triage][1]++;
    }

    if (! egreso.fecha) return;

    // Cálculos triage vs egreso
    if (triage.fecha) {
      promedios.triageEgreso[clase_triage][0] += (egreso.timestamp - triage.timestamp) / 60;
      promedios.triageEgreso[clase_triage][1]++;
    }

    // Cálculo de admisión vs egreso
    if (admision.fecha) {
      promedios.admisionEgreso[clase_triage][0] += (egreso.timestamp - admision.timestamp) / 60;
      promedios.admisionEgreso[clase_triage][1]++;
    }
  });

  const calcularPromedio = ([dividendo, divisor]) => {
    const x = dividendo / divisor;
    return isNaN(x) ? 0 : x.toFixed(1);
  }

  const ta = promedios.triageAdmision;
  $("#ta5").text(calcularPromedio(ta[5]));
  $("#ta4").text(calcularPromedio(ta[4]));
  $("#ta3").text(calcularPromedio(ta[3]));
  $("#ta2").text(calcularPromedio(ta[2]));
  $("#ta1").text(calcularPromedio(ta[1]));

  const au = promedios.admisionHurge;
  $("#t5").text(calcularPromedio(au[5]));
  $("#t4").text(calcularPromedio(au[4]));
  $("#t3").text(calcularPromedio(au[3]));
  $("#t2").text(calcularPromedio(au[2]));
  $("#t1").text(calcularPromedio(au[1]));
  $("#t0").text(calcularPromedio(au[0]));

  const te = promedios.triageEgreso;
  $("#te5").text(calcularPromedio(te[5]));
  $("#te4").text(calcularPromedio(te[4]));
  $("#te3").text(calcularPromedio(te[3]));
  $("#te2").text(calcularPromedio(te[2]));
  $("#te1").text(calcularPromedio(te[1]));

  const ae = promedios.admisionEgreso;
  $("#ae5").text(calcularPromedio(ae[5]));
  $("#ae4").text(calcularPromedio(ae[4]));
  $("#ae3").text(calcularPromedio(ae[3]));
  $("#ae2").text(calcularPromedio(ae[2]));
  $("#ae1").text(calcularPromedio(ae[1]));
  $("#ae0").text(calcularPromedio(ae[0]));
}

async function fetchDatosEstadistica(f) {
  return await $.ajax({
    url: "./estadisticas",
    dataType: "json",
    method: "POST",
    data: {
      fe: f,
    },
  });
}

function listar(data) {
  const fechasClassName = "small text-nowrap pt-4 px-4 position-relative";

  return $("#gridEst").DataTable({
    language: {
      url: "es-MX.json",
    },
    scrollY: "50vh",
    scrollX: true,
    scrollCollapse: true,
    fixedColumns: { left: 0, right: 2 },
    dom: "Bfrtip",
    buttons: ["excel"],
    data: data,
    order: [[0, "asc"]],
    columns: [
      {
        data: "alerta",
        className: "small",
        render: function (data, type, row) {
          const { paciente, alerta } = row;
          const genero = paciente?.genero || "F";
          const edad = paciente?.edad || 18;

          const icon =
            genero === "F"
              ? edad > 14
                ? "Image/mujer.png"
                : "Image/nina.png"
              : edad > 14
              ? "Image/hombre.png"
              : "Image/nino.png";

          return `<img src="${icon}"> ${
            alerta ? `<img src="Image/advertencia.png">` : ""
          }`;
        },
      },
      {
        data: "paciente.nombre",
        /** @param data {string} */
        render: function (data, type, row, meta) {
          const { docn, paciente } = row;
          return `<span class="d-flex flex-column small text-nowrap">
              <span>${paciente.nombre || "---"}</span>
              <span class="small text-muted">${paciente.documento} - Edad: ${
            paciente.edad
          }</span>
              <span class="small text-muted">Admisión: ${docn}</span>
            </span>`;
        },
      },
      {
        data: "steps.triage.fecha",
        className: fechasClassName,
        render: (data, type, row) => {
          const { fecha, warning } = row.steps.triage;
          const { fecha: fechaAdmision } = row.steps.admision;
          const { clase_triage } = row;

          return renderColFecha({
            data: `
              <span
                class="text-muted opacity-75 text-center position-absolute start-0 end-0 m-auto bottom-0"
              >${clase_triage}</span>
              <span>${fecha || "---"}</span>
            `,
            prev: true,
            next: Boolean(fechaAdmision),
            warning: Boolean(warning),
          });
        },
      },
      {
        data: "steps.triage.formatedDiff",
        className: "small position-absolute crono-class border-0 shadow-none w-0 p-0 text-nowrap",
        orderable: false,
      },
      {
        data: "steps.admision.fecha",
        className: fechasClassName,
        render: (data, type, row) => {
          const { fecha: fechaUrg } = row.steps.hurge;
          const { admision } = row.steps;
          return renderColFecha({
            data: data,
            prev: true,
            next: fechaUrg,
            warning: Boolean(admision.warning),
          });
        },
      },
      {
        data: "steps.admision.formatedDiff",
        className: "small position-absolute crono-class w-0 p-0 border-0 shadow-none",
        orderable: false,
      },
      {
        data: "steps.hurge.fecha",
        className: fechasClassName,
        render: (data, type, row) => {
          const { admision, egresoHurge, hurge } = row.steps;
          return renderColFecha({
            data,
            hoverable: true,
            prev: Boolean(admision.fecha),
            next: Boolean(egresoHurge.fecha),
            warning: Boolean(hurge.warning),
          });
        },
      },
      {
        data: "steps.hurge.formatedDiff",
        className: "small position-absolute crono-class w-0 p-0 border-0 shadow-none",
        orderable: false,
      },
      {
        data: "steps.egresoHurge.fecha",
        className: fechasClassName,
        render: function (data, type, row) {
          const { egreso_urge } = row;
          const { hurge, egreso, egresoHurge } = row.steps;

          const prev = Boolean(hurge.fecha);
          const next =
            (Boolean(egreso.fecha) && egreso_urge === "1") ||
            !["0", "1"].includes(egreso_urge);

          if (!egreso.fecha)
            return renderColFecha({
              data: data,
              prev,
              next,
              warning: egresoHurge.warning,
            });

          const _data = egreso_urge === "0" ? "" : egreso_urge ? data : "";

          return renderColFecha({
            data: _data,
            prev,
            next,
            warning: Boolean(egresoHurge.warning),
          });
        },
      },
      {
        data: "steps.egresoHurge.formatedDiff",
        className: "small position-absolute crono-class w-0 p-0 border-0 shadow-none",
        orderable: false,
      },
      {
        data: "steps.egreso.fecha",
        className: fechasClassName,
        render: function (data, type, row) {
          const { egreso_urge } = row;
          const { egresoHurge, egreso } = row.steps;

          if (egreso_urge != "0") {
            data = (egreso_urge === "1") ? data : "Egreso Interno";
          }

          return renderColFecha(
            {
              data: data,
              prev: Boolean(egresoHurge.fecha),
              warning: Boolean(egreso.warning),
            },
            true
          );
        },
      },
      {
        data: "medico.nombre",
        className: "small",
        render: function (data, type, row) {
          const medico = row.medico;
          return medico ? `<span title="${data}">${medico.cod}</span>` : "";
        },
      },
    ],
  });
}

async function valida_fecha(f) {
  var date = myDate.val();
  const today = new Date().toJSON().substring(0, 10);

  if (Date.parse(date)) {
    if (date > today) {
      Swal.fire({
        position: "top-end",
        icon: "error",
        title: "!La fecha no puede ser mayor a la actual!",
        showConfirmButton: false,
        timer: 1500,
      });
      myDate.val("");
    } else {
      $("#cargaInfo").show();
      const data = await fetchDatosEstadistica(f);
      cagarDatos(data);
      $("#cargaInfo").hide();
    }
  }
}
