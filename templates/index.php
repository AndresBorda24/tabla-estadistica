<?php /** @var App\UserSession $user */ ?>
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
  <!-- <link rel="stylesheet" href="//unicons.iconscout.com/release/v3.0.6/css/line.css"> -->
  <link href="//cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
  <link rel="stylesheet" href="//cdn.datatables.net/2.3.4/css/dataTables.dataTables.min.css" />
  <link
    rel="stylesheet"
    type="text/css"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css"
  >
  <link
    rel="stylesheet"
    href="https://cdn.datatables.net/buttons/3.2.5/css/buttons.dataTables.min.css"
  >
  <!-- jQuery -->
  <script src="//cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <!-- <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.3/jquery-ui.min.js"></script> -->
  <!-- <script src="//cdn.datatables.net/2.2.2/js/dataTables.min.js"></script> -->
  <!-- Estadisticas -->
  <script src="./assets/estadisticas.js"></script>
  <script src="./assets/v2/app.js" defer></script>
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
    <div class="bg-body-tertiary rounded-top border small d-flex" style="
      padding-bottom: 1rem;
      padding-top: 6px;
      margin-bottom: -0.8rem;
    ">
      <?= $this->fetch('./partials/filtro-triage.php') ?>
      <?= $this->fetch('./partials/filtro-tipos.php') ?>
    </div>

    <div class="d-flex mb-4 position-relative">
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

    <?php if((int) $user->medicoId === 0): ?>
      <div>
        <span class="fw-bold small">Atenciones x Medico</span>
        <div
          id="contenido"
          class="d-flex overflow-x-auto gap-3 mb-4 pb-2"
        ></div>
      </div>
    <?php endif ?>

    <div class="d-flex flex-column flex-lg-row gap-4">
      <div>
        <span class="fw-bold small">Conteo Triage</span>
        <div class="d-flex flex-column rounded border overflow-hidden small">
          <span class="bg-white border-bottom d-block small px-3 py-1">Sin Triage: <b id="nt0" class="d-inline-block ms-2"></b></span>
          <span class="bg-white border-bottom d-block small px-3 py-1">Triage 1: <b id="nt1" class="d-inline-block ms-2"></b></span>
          <span class="bg-white border-bottom d-block small px-3 py-1">Triage 2: <b id="nt2" class="d-inline-block ms-2"></b></span>
          <span class="bg-white border-bottom d-block small px-3 py-1">Triage 3: <b id="nt3" class="d-inline-block ms-2"></b></span>
          <span class="bg-white border-bottom d-block small px-3 py-1">Triage 4: <b id="nt4" class="d-inline-block ms-2"></b></span>
          <span class="bg-white d-block small px-3 py-1">Triage 5: <b id="nt5" class="d-inline-block ms-2"></b></span>
        </div>
      </div>

      <div>
        <span class="fw-bold small">Promedio en Minútos: TRIAGE vs Admisión</span>
        <div id="prom-admi-triage" class="d-flex overflow-x-auto gap-3 mb-2 pb-2"></div>

        <span class="fw-bold small">Promedio en Minútos: Admisión vs Hoja Urgencias</span>
        <div id="prom-admi-hurge" class="d-flex overflow-x-auto gap-3 mb-2 pb-2"></div>


        <span class="fw-bold small">Promedio en Minútos: Triage vs Egreso</span>
        <div id="prom-triage-egreso" class="d-flex overflow-x-auto gap-3 mb-2 pb-2"></div>

        <span class="fw-bold small">Promedio en Minútos: Admisión vs Egreso</span>
        <div id="prom-admision-egreso" class="d-flex overflow-x-auto gap-3 mb-2 pb-2"></div>
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

  <!-- <script src="//cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script> -->
  <script src="//cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.colVis.min.js"></script>
</body>
</html>
