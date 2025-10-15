<dialog
  class="modal-urgencias bg-body-tertiary"
  x-data="ModalUrgencias"
  @open-modal-urgencias.document="handleData">
  <button
    @click="modal?.close()"
    type="button"
    class="btn-sm btn-close small"></button>

  <template x-if="info !== null">
    <div>
      <!-- Información del paciente -->
      <div class="flex-fill">
        <span class="d-block fs-5 fw-bold" x-text="info.paciente.nombre"></span>
        <p class="small text-muted">
          <span class="d-inline-block small">
            <span class="italic small text-muted">Documento:</span>
            <span class="fw-bold" x-text="info.paciente.documento"></span>
          </span> |
          <span class="d-inline-block small">
            <span class="italic small text-muted">Admisión:</span>
            <span class="fw-bold" x-text="info.docn"></span>
          </span> |
          <span class="d-inline-block small">
            <span class="italic small text-muted">Edad:</span>
            <span class="fw-bold" x-text="info.paciente.edad"></span>
          </span> |
          <span class="d-inline-block small">
            <span class="italic small text-muted">Triage:</span>
            <span class="fw-bold" x-text="info.clase_triage"></span>
          </span>
          <span class="d-block small">
            <span class="italic small text-muted">Médico</span>
            <span class="fw-bold" x-text="info.medico.nombre"></span>
          </span>
        </p>
      </div>

      <div
        class="bg-white px-2 pt-1 rounded"
        style="border: 1px dashed #d8d8d8"
        x-html="timeline"></div>

      <hr class="border-dark-subtle">

      <template x-if="emptyInfo">
        <p 
          class="px-3 py-2 bg-white border rounded-bottom-1 text-muted fw-semibold" 
          style="font-size: 12px"
        >
          El paciente no tiene registro de Tratamiento, Imágenes, Laboratorios, Interconsultas o Evoluciones.  
        </p>
      </template>

      <template x-if="info.infoUrgencias.tratamiento?.trim()">
        <div>
          <span class="small d-block fw-bold">Tratamiento:</span>
          <p 
            class="px-3 py-2 bg-white border rounded-bottom-1 text-capitalize text-muted fw-semibold" 
            style="white-space: break-spaces; font-size: 12px" 
            x-text="info.infoUrgencias.tratamiento.trim().toLowerCase()"></p> 
        </div>
      </template>

      <template x-if="info.infoUrgencias.imagenes.length">
        <div>
          <span class="small d-block fw-bold">Imágenología</span>
          <ol class="list-group list-group-numbered small rounded-1 mb-3">
            <template x-for="image in info.infoUrgencias.imagenes">
              <li class="list-group-item small text-muted d-flex gap-2">
                <div class="flex-fill lh-1">
                  <div class="d-flex gap-2">
                    <div class="d-flex gap-2 flex-fill">
                      <span class="fw-bold" x-text="image.fechaSol"></span>
                      <span x-text="image.horaSol"></span>
                    </div>
                    <span class="fw-bold" x-text="image.tipo">RX </span>
                    <span
                      :title="image.ok ? 'Realizado' : 'Pendiente'"
                      x-text="image.ok ? '&check;' : '&#10061;'"></span>
                  </div>
                  <span
                    style="font-size: 11px"
                    class="text-lowercase"
                    x-text="image.nombre"></span>
                </div>
              </li>
            </template>
          </ol>
        </div>
      </template>

      <template x-if="info.infoUrgencias.lab.length">
        <div>
          <span class="small d-block fw-bold">Laboratórios</span>
          <ol class="list-group list-group-numbered small rounded-1 mb-3">
            <template x-for="item in info.infoUrgencias.lab">
              <li class="list-group-item small d-flex text-muted gap-2">
                <div class="d-flex flex-column lh-1 ms-2 flex-fill">
                  <span class="fw-bold" x-text="item.fechaSol"></span>
                  <span x-text="item.horaSol"></span>
                </div>
                <span class="fw-bold" x-text="item.diag"></span>
                <span
                  :title="item.ok ? 'Realizado' : 'Pendiente'"
                  x-text="item.ok ? '&check;' : '&#10061;'"></span>
              </li>
            </template>
          </ol>
        </div>
      </template>

      <template x-if="info.infoUrgencias.intercon.length">
        <div>
          <span class="small d-block fw-bold">Interconsultas</span>
          <ol class="list-group list-group-numbered small rounded-1 mb-3">
            <template x-for="item in info.infoUrgencias.intercon">
              <li class="list-group-item small d-flex text-muted gap-2">
                <div title="Solicitud" class="d-flex flex-column lh-1 ms-2 flex-fill">
                  <span class="fw-bold" x-text="item.fechaSol"></span>
                  <span x-text="item.horaSol"></span>
                </div>
                <div title="Realización" class="d-flex flex-column lh-1 ms-2 flex-fill">
                  <span class="fw-bold" x-text="item.fechaFin"></span>
                  <span x-text="item.horaFin"></span>
                </div>
                <span
                  :title="item.ok ? 'Realizado' : 'Pendiente'"
                  x-text="item.ok ? '&check;' : '&#10061;'"></span>
              </li>
            </template>
          </ol>
        </div>
      </template>

      <template x-if="info.infoUrgencias.evolucion.length">
        <div>
          <span class="small d-block fw-bold">Evolucion</span>
          <ol class="list-group list-group-numbered small rounded-1 mb-3">
            <template x-for="item in info.infoUrgencias.evolucion">
              <li class="list-group-item small d-flex text-muted gap-2">
                <div title="Solicitud" class="d-flex flex-column lh-1 ms-2 flex-fill">
                  <span class="fw-bold" x-text="item.fechaSol"></span>
                  <span x-text="item.horaSol"></span>
                </div>
                <span
                  :title="item.ok ? 'Realizado' : 'Pendiente'"
                  x-text="item.ok ? '&check;' : '&#10061;'"></span>
              </li>
            </template>
          </ol>
        </div>
      </template>
    </div>
  </template>
</dialog>
