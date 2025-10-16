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
    document.dispatchEvent(
      new CustomEvent('open-modal-urgencias', { detail: data })
    );
  });

  const informacion = await fetchDatosEstadistica(today);
  const { data, contadores } = informacion;

  if (data.length == 0) return $("#full-loader").remove();
  cagarDatos({ data, contadores });
});

function cagarDatos({ data, contadores }) {
  setUpDoctorFilter(TABLA); // Medicos 
  setContadores(contadores); // Conteo de los triage

  $("#full-loader").remove();
  TABLA.clear();
  TABLA.rows.add(data);
  TABLA.draw();
  
  // Notificamos que la información ha sido cargada y compartimos esa info.
  document.dispatchEvent(
    new CustomEvent('table-data-refresh', { detail: data })
  );
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
  $("#contador-admission").html(contadores.general - contadores.sinAdmision);
  $("#contador-warning").html(contadores.alertas);
  $("#contador-man").html(contadores.hombres);
  $("#contador-woman").html(contadores.mujeres);
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
          const { docn, paciente, steps } = row;
          const { digiturno } = steps;

          return `<span class="d-flex flex-column small text-nowrap">
              <span class="fw-semibold">${paciente.nombre || "---"}</span>
              <span class="small text-muted">
                ${paciente.documento} - Edad: ${paciente.edad} ${docn ? `- ${docn}` : ''}
              </span>
              <span class="small text-muted">
                Digiturno: <b>${digiturno?.fecha || 'Sin registro'}</b>
              </span>
            </span>`;
        },
      },
      {
        data: "steps.digiturno.formatedDiff",
        className: "small position-absolute crono-class border-0 shadow-none w-0 p-0 text-nowrap",
        orderable: false,
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
