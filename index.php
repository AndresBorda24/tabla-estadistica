<?php
  session_start();
  if (! isset($_SESSION['usuario'])) {
    $ruta = '/asotrauma/servicios/estadisticas/v2/';
    header('Location: /login/login.php?ruta='.$ruta, response_code: 302) ;
    exit;
  }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous"
  >
  <link rel="stylesheet" href="assets/estadisticas.css" />
  <link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
  <link rel="stylesheet" href="//unicons.iconscout.com/release/v3.0.6/css/line.css">
  <link href="//cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
  <link
    rel="stylesheet"
    type="text/css"
    href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"
  >
  <link
    rel="stylesheet"
    type="text/css"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css"
  >
  <link
    rel="stylesheet"
    href="https://cdn.datatables.net/buttons/1.7.0/css/buttons.dataTables.min.css"
  >
  <!-- jQuery -->
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.3/jquery-ui.min.js"></script>
  <script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
  <!-- Estadisticas -->
  <script src="./assets/estadisticas.js"></script>
  <title>Estadisticas</title>
</head>

<body>
  <div class="gateway-background"></div>
  <div class="sticky-top bg-body-tertiary border-bottom">
    <div class="main-container py-2">
      <label class="small text-muted d-flex align-items-center lh-1">
        Día de atención:
        <input
          onchange="valida_fecha(this.value);"
          id="fecha"
          name="fecha"
          placeholder="Fecha nacimiento"
          data-type="datepicker"
          data-datepicker="true"
          class="form-control form-control-sm"
          role="input"
          type="date"
        >
      </label>
    </div>
  </div>
  <div class="main-container pt-4">
    <div class="d-flex gap-3 pb-2 overflow-x-auto">
      <button class="btn-warning custom-button" data-toggle="tooltip" data-placement="left" title="Numero de advertencias" disabled><img src="Image/advertencia.png"><span id="adv" style="color: #362F35;" class="mx-md-1"></span></button>
      <button class="custom-button" data-toggle="tooltip" data-placement="left" title="Cantidad de Hombres" style="background-color: #BBB8FF;" disabled><img src="Image/hombre.png"><span id="chombre" style="color: #362F35;" class="mx-md-1"></span></button>
      <button class="custom-button" data-toggle="tooltip" data-placement="left" title="Cantidad de Mujeres" style="background-color: #FFA4A4;" disabled><img src="Image/mujer.png"><span id="cmujer" style="color: #362F35;" class="mx-md-1"></span></button>
      <button class="custom-button" data-toggle="tooltip" data-placement="left" title="Total Admisiones" style="background-color: #FFF890;" disabled><img src="Image/admision.png"><span id="tadm" style="color: #362F35;" class="mx-md-1"></span></button>
      <button class="btn-primary custom-button" data-toggle="tooltip" data-placement="left" title="Sin Admision" style="background-color: #A9F9EB;" disabled><img src="Image/sinadm.png"><span id="stria" style="color: #362F35;" class="mx-md-1"></span></button>
      <button class="btn-primary custom-button" data-toggle="tooltip" data-placement="left" title="Sin H. Urgencias" style="background-color: #8BFFB5;" disabled><img src="Image/paciente.png"><span id="shur" style="color: #362F35;" class="mx-md-1"></span></button>
      <button class="custom-button btn-outline-dark" data-toggle="tooltip" data-placement="left" title="Turnos Pendientes" disabled><img src="Image/turnos.png"><span id="digi" style="color: #362F35;" class="mx-md-1"></span></button>
    </div>

    <?php if((int) $_SESSION['medico_id'] === 0): ?>
      <div>
        <span class="fw-bold small">Atenciones x Medico</span>
        <div
          id="contenido"
          class="d-flex overflow-x-auto gap-3 mb-4 pb-2"
        ></div>
      </div>
    <?php endif ?>

    <div class="d-flex mb-4">
      <!-- Grilla -->
      <div class="rounded border py-3 px-0 flex-grow-1 w-100 bg-body-tertiary">
        <div class="text-center" id="cargaInfo">
          <div class="spinner-border text-success" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
        <table id="gridEst" class="display compact small hover w-100">
          <thead>
            <tr>
              <th></th>
              <th>Paciente</th>
              <th>Triage</th>
              <!-- <th>Tipo Triage</th> -->
              <th></th> <!-- Cronometro de Triage-->
              <th>Admisión</th>
              <th></th> <!-- Cronometro de Urgencias -->
              <th>H. Urgencias</th>
              <th></th> <!-- Cronometro de Urgencias -->
              <th>Egreso Urgencias</th>
              <th></th> <!-- Cronometro de Urgencias -->
              <th>Egreso Atención</th>
              <th>Medico</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>

    <div class="d-flex flex-column flex-lg-row gap-4">
      <div>
        <span class="fw-bold small">Conteo Triage</span>
        <div class="d-flex flex-column rounded border overflow-hidden small">
          <span class="bg-white border-bottom d-block small px-3 py-1">Triage 1: <b id="nt1" class="d-inline-block ms-2"></b></span>
          <span class="bg-white border-bottom d-block small px-3 py-1">Triage 2: <b id="nt2" class="d-inline-block ms-2"></b></span>
          <span class="bg-white border-bottom d-block small px-3 py-1">Triage 3: <b id="nt3" class="d-inline-block ms-2"></b></span>
          <span class="bg-white border-bottom d-block small px-3 py-1">Triage 4: <b id="nt4" class="d-inline-block ms-2"></b></span>
          <span class="bg-white d-block small px-3 py-1">Triage 5: <b id="nt5" class="d-inline-block ms-2"></b></span>
        </div>
      </div>

      <div>
        <span class="fw-bold small">Promedio TRIAGE vs Admisión</span>
        <div id="prom-admi-triage" class="d-flex overflow-x-auto gap-3 mb-2 pb-2"></div>

        <span class="fw-bold small">Promedio Admisión vs Hoja Urgencias</span>
        <div id="prom-admi-hurge" class="d-flex overflow-x-auto gap-3 mb-2 pb-2"></div>
      </div>
    </div>
  </div>

  <div class="fixed-top vh-100 vw-100 d-flex bg-dark" id="full-loader">
    <span class="text-light m-auto">Cargando...</span>
  </div>

  <div
    id="modal-info-urgencias"
    style="display: none; z-index: 2000;"
    class="vw-100 vh-100 bg-black bg-opacity-75 py-4 px-3 position-fixed top-0 start-0 flex"
  >
    <div
      class="m-auto bg-body-tertiary overflow-auto p-3 p-md-4 rounded"
      style="max-height: 80dvh; width: 450px; max-width: 90vw;"
    >
      <div class="d-flex gap-2">
        <div class="flex-fill lh-1">
          <span class="d-block fs-5 fw-bold mb-1" id="modal-info-nombre"></span>
          <p class="small mb-2">
            <span class="d-block small fw-bold" id="modal-info-cc"></span>
            <span class="d-block small" id="modal-info-docn"></span>
          </p>
          <p class="small text-muted">
            <span class="d-block small" id="modal-info-edad"></span>
            <span class="small">Triage</span>
            <span class="d-inline-block small fw-bold" id="modal-info-triage"></span> </br>
            <span class="small">Médico</span>
            <span class="d-inline-block small fw-bold" id="modal-info-medico"></span>
          </p>
        </div>
        <button
          type="button"
          id="modal-info-close"
          class="btn-sm btn-close small mt-1"
        ></button>
      </div>

      <div id="modal-info-timeline"></div>

      <span class="small d-block fw-bold">Imágenología</span>
      <ol id="modal-info-imagenes" class="list-group list-group-numbered small rounded-1 mb-3"> </ol>

      <span class="small d-block fw-bold">Laboratórios</span>
      <ol id="modal-info-lab" class="list-group list-group-numbered small rounded-1 mb-3"> </ol>

      <span class="small d-block fw-bold">Interconsultas</span>
      <ol id="modal-info-intercon" class="list-group list-group-numbered small rounded-1 mb-3"> </ol>

      <span class="small d-block fw-bold">Evolucion</span>
      <ol id="modal-info-evolucion" class="list-group list-group-numbered small rounded-1 mb-3"> </ol>
    </div>
  </div>

  <script src="//cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
  <script src="//cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="//cdn.datatables.net/buttons/1.7.0/js/buttons.html5.min.js"></script>
  <script src="//cdn.datatables.net/buttons/1.7.0/js/buttons.print.min.js"></script>
  <script src="//cdn.datatables.net/buttons/1.7.0/js/buttons.colVis.min.js"></script>
</body>

</html>

<script>
  $("#cargaInfo").hide();
  let TABLA;
  const myDate = $("#fecha")

  $(async function() {
    const today = new Date().toJSON().substring(0, 10);
    myDate.val(today);
    myDate.attr("max", today);

    TABLA = listar([]);
    TABLA.on('dblclick', 'tbody tr', function () {
      const data  = TABLA.row(this).data();
      showInfoModal(data); // estadisticas.js
    });

    const data = await tablaEstadisticas(today);
    if (data.length == 0) return $("#full-loader").remove();

    cagarDatos(data);
  });

  function cagarDatos(data) {
    data.sort((a, b) => {
      const adv = a.marca;
      return (adv == "S") ? -1 : 1;
    });

    var arrayMedicos = {};

    data.forEach(x => {
      if (!arrayMedicos.hasOwnProperty(x.medico)) {
        arrayMedicos[x.medico] = {
          nmedicos: []
        }
      }

      arrayMedicos[x.medico].nmedicos.push({
        nombre: x.medico,
        docn: x.docn,
        nombreComp: x.nombreMedico,
      })

    });

    let arrayM = Object.entries(arrayMedicos);

    const contenido = document.getElementById("contenido");
    contenido.innerHTML = "";
    var temp = "";
    for (let it of arrayM) {
      var c = it[1, 1].nmedicos;
      temp = c.nombre;
      for (let it2 of c) {
        if (!Boolean(it2.nombre) || temp != c.nombre) continue;

        contenido.innerHTML += `
          <div
            style="width: 70px; min-width: 70px"
            class="d-flex flex-column align-items-center py-1 px-3 bg-white border rounded small lh-sm"
            title="${it2.nombreComp.trim()} - ${c.length}"
          >
            <span class="fw-bold small">${it2.nombre}</span>
            <span class="small">${c .length}</span>
          </div>
        `;
        break;
      }
    }

    var r = (data[data.length - 1]);
    var r2 = r["adv"];

    var promedioTriage = r["minutosT"] / data.length;
    var promedioT5 = r["mt5"] / r["t5"];
    var promedioT4 = r["mt4"] / r["t4"];
    var promedioT3 = r["mt3"] / r["t3"];
    var promedioT2 = r["mt2"] / r["t2"];
    var promedioT1 = r["mt1"] / r["t1"];

    var promedioTA5 = r["mta5"] / r["t5"];
    var promedioTA4 = r["mta4"] / r["t4"];
    var promedioTA3 = r["mta3"] / r["t3"];
    var promedioTA2 = r["mta2"] / r["t2"];
    var promedioTA1 = r["mta1"] / r["t1"];

    $("#ta5").text(isNaN(promedioTA5.toFixed(1)) ? 0 : promedioTA5.toFixed(1));
    $("#ta4").text(isNaN(promedioTA4.toFixed(1)) ? 0 : promedioTA4.toFixed(1));
    $("#ta3").text(isNaN(promedioTA3.toFixed(1)) ? 0 : promedioTA3.toFixed(1));
    $("#ta2").text(isNaN(promedioTA2.toFixed(1)) ? 0 : promedioTA2.toFixed(1));
    $("#ta1").text(isNaN(promedioTA1.toFixed(1)) ? 0 : promedioTA1.toFixed(1));

    $("#t5").text(isNaN(promedioT5.toFixed(1)) ? 0 : promedioT5.toFixed( 1));
    $("#t4").text(isNaN(promedioT4.toFixed(1)) ? 0 : promedioT4.toFixed( 1));
    $("#t3").text(isNaN(promedioT3.toFixed(1)) ? 0 : promedioT3.toFixed( 1));
    $("#t2").text(isNaN(promedioT2.toFixed(1)) ? 0 : promedioT2.toFixed( 1));
    $("#t1").text(isNaN(promedioT1.toFixed(1)) ? 0 : promedioT1.toFixed( 1));
    $("#at").text(isNaN(promedioTriage.toFixed(1)) ? 0 : promedioTriage .toFixed(1));

    var digi = r["nroDigi"];
    $("#digi").html(digi);

    var ch = r["chombre"];
    var cm = r["cmujeres"];
    var sh = r["churge"];
    var st = r["ctria"];
    var sa = r["sadm"];
    var t = ch + cm;

    $("#nt1").text(r["t1"]);
    $("#nt2").text(r["t2"]);
    $("#nt3").text(r["t3"]);
    $("#nt4").text(r["t4"]);
    $("#nt5").text(r["t5"]);

    $("#shur").html(sh, "m");
    $("#stria").html(sa);
    $("#tadm").html(t);
    $("#adv").html(r2);
    $("#chombre").html(ch);
    $("#cmujer").html(cm);


    $("#full-loader").remove();
    TABLA.clear()
    TABLA.rows.add(data);
    TABLA.draw();
  }

  const dataTableOptions = {
    decimal: ",",
    emptyTable: "No hay datos disponibles en la tabla",
    info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
    infoEmpty: "Mostrando 0 a 0 de 0 entradas",
    infoFiltered: "(filtrado de _MAX_ entradas totales)",
    infoPostFix: "",
    thousands: ".",
    loadingRecords: "Cargando...",
    processing: "Procesando...",
    search: "<i class='bi bi-search'>Buscar: </i>",
    zeroRecords: "No se encontraron registros coincidentes",
    paginate: {
      first: "Primero",
      last: "Último",
      next: "Siguiente",
      previous: "Anterior"
    },
    aria: {
      sortAscending: ": Activar para ordenar la columna en orden ascendente",
      sortDescending: ": Activar para ordenar la columna en orden descendente"
    },
  };

  async function tablaEstadisticas(f) {
    return await $.ajax({
      url: './cargar_hurgencia.php',
      dataType: 'json',
      method: "POST",
      data: {
        "fe": f
      }
    });
  }

  function listar(data) {
    const fechasClassName = "small text-nowrap pt-4 px-3 position-relative";
    return $("#gridEst").DataTable({
      language: dataTableOptions,
      scrollY: '50vh',
      scrollX: true,
      scrollCollapse: true,
      fixedColumns: { left: 0, right: 2 },
      dom: 'Bfrtip',
      buttons: [ 'excel' ],
      data: data,
      order: [
        [0, 'desc']
      ],
      columns: [
        {
          "data": "superaTiempo",
          className: "small"
        },
        {
          "data": "nombreced",
          /** @param data {string} */
          render: function(data, type, row, meta) {
            const { docn } = row
            const [,nombre,cc,edad] = data.match(/^(.+?)\s*-\s*(\d+)\s*Edad:(\d*)/);
            const nombreTrimmed = nombre?.trim();

            return `<span class="d-flex flex-column small text-nowrap">
              <span>${nombreTrimmed || '---'}</span>
              <span class="small text-muted">${cc} - Edad: ${edad}</span>
              <span class="small text-muted">Admisión: ${docn}</span>
            </span>`
          },
        },
        {
          "data": "fhtri",
          className: fechasClassName,
          render: (data, type, row) => {
            const { fhtri, fecha, ttriage } = row
            return renderColFecha({ data: `
              <span
                class="text-muted opacity-75 text-center position-absolute start-0 end-0 m-auto bottom-0"
              >${ttriage}</span>
              <span>${fhtri}</span>
            `, prev: true, next: Boolean(fecha) })
          }
        },
        {
          "data": "diferenciaAdm",
          className: "small position-absolute crono-class border-0 shadow-none",
          orderable: false
        },
        {
          "data": "fecha",
          className: fechasClassName,
          render: (data, type, row) => {
            const {fechaUrg} = row
            return  renderColFecha({ data, prev: true, next: fechaUrg })
          }
        },
        {
          "data": "diferenciaUrg",
          className: "small position-absolute crono-class border-0 shadow-none",
          orderable: false
        },
        {
          "data": "fechaUrg",
          className: fechasClassName,
          render: (data, type, row) => {
            const { fechaUrg, fecha, egresoUrgFecha } = row;
            return renderColFecha({
              data: fechaUrg,
              hoverable: true,
              prev: Boolean(fecha),
              next: Boolean(egresoUrgFecha)
            })
          },
        },
        {
          "data": "diferenciaEgrUrg",
          className: "small position-absolute crono-class border-0 shadow-none",
          orderable: false
        },
        {
          "data": "egresoUrgFecha",
          className: fechasClassName,
          render: function(data,type, row) {
            const {
              egresoUrgFecha,
              egresoUrgHora,
              horaUrg,
              egresoUrgDest,
              egresoFecha,
              fechaUrg
            } = row;
            const prev = Boolean(fechaUrg);
            const next = (Boolean(egresoFecha) && (egresoUrgDest === "1")) || !['0','1'].includes(egresoUrgDest);

            if (!egresoUrgDest)
              return renderColFecha({ data: '', prev, next });

            const _data = (egresoUrgDest === "0")
              ? ""
              : egresoUrgDest
                ? `${egresoUrgFecha} Hr ${egresoUrgHora || horaUrg}`
                : '';

            return renderColFecha({ data: _data, prev, next });
          }
        },
        {
          "data": "diferenciaEgrAdm",
          className: "small position-absolute crono-class border-0 shadow-none",
          orderable: false
        },
        {
          "data": "egresoFecha",
          className: fechasClassName,
          render: function(data,type, row) {
            const { egresoFecha, egresoHora, egresoUrgDest, egresoUrgFecha } = row;

            if (egresoUrgDest != "0") {
              const _data = (egresoUrgDest === "1")
                ? Boolean(egresoFecha)
                  ? `${egresoFecha || '' } Hr ${egresoHora || ''}`
                  : ''
                : 'Egreso Interno';

              return renderColFecha({ data: _data, prev: Boolean(egresoUrgFecha)}, true);
            }

            return renderColFecha({ data: '', prev: Boolean(egresoUrgFecha)}, true);
          }
        },
        {
          "data": "medico",
          className: "small",
          render: function(data, type, row) {
            const { nombreMedico } = row
            return `<span title="${nombreMedico.trim()}">${data}</span>`
          }
        }
      ]
    });
  };

  async function valida_fecha(f) {
    var date = myDate.val();
    const today = (new Date()).toJSON().substring(0, 10);

    if (Date.parse(date)) {
      if (date > today) {
        Swal.fire({
          position: 'top-end',
          icon: 'error',
          title: '!La fecha no puede ser mayor a la actual!',
          showConfirmButton: false,
          timer: 1500
        })
        myDate.val("");
      } else {
        $("#cargaInfo").show();
        const data = await tablaEstadisticas(f);
        cagarDatos(data);
        $("#cargaInfo").hide();
      }
    }
  }
</script>
