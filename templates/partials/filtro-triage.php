<div class="flex flex-column align-items-center">
  <span class="small">Triage</span>
  <div class="radio-group-3">
    <?php foreach(['Sin Traige','1','2','3','4','5'] as $i => $triage): ?>
      <div class="radio-item-3 text-nowrap">
        <input type="radio" id="filtro-triage-<?= $i ?>" name="filtro-triage" value="<?= $i ?>">
        <label for="filtro-triage-<?= $i ?>"><?= $triage ?></label>
      </div>
    <?php endforeach ?>
    <div class="radio-item-3">
      <input type="radio" id="filtro-triage-all" name="filtro-triage" value="" checked>
      <label for="filtro-triage-all">Todo</label>
    </div>
  </div>
</div>
