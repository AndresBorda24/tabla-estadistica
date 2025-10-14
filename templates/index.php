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
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <!-- Estadisticas -->
  <script src="./assets/estadisticas.js"></script>
  <script src="./assets/v2/app.js" defer></script>

  <?php if(App\Config::isProduction()): echo $vite->tags() ?>
  <?php else: ?>
    <script type="module" src="http://192.168.1.16:5173/@vite/client"></script>
    <script type="module" src="http://192.168.1.16:5173/src/main.js"></script> 
  <?php endif ?>

  <title>Estadísticas</title>
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

    <div class="d-flex position-relative">
      <!-- Grilla -->
      <div class="rounded border pt-2 pb-1 px-0 flex-grow-1 w-100 bg-body-tertiary">
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
      <div class="bg-body-tertiary rounded-bottom border small mb-4" style="
        padding-top: 1rem;
        padding-bottom: 6px;
        margin-top: -0.8rem;
      ">
        <span class="fw-bold small d-inline-block ms-2">Atenciones x Medico</span>
        <div
          id="contenido"
          class="d-flex overflow-x-auto gap-3 px-2"
        ></div>
      </div>
    <?php endif ?>

    <?= $this->fetch('./partials/TriageMinDiffChart.php') ?>
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

  <script src="//cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.5/js/dataTables.buttons.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.print.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.2.5/js/buttons.colVis.min.js"></script>
</body>
</html>
