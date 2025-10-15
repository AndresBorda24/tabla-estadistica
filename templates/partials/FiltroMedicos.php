<?php 
/** @var App\UserSession $user */ 
$noEsMedico = ((int) $user->medicoId === 0);
?>
<div 
	class="d-flex overflow-x-auto gap-3 px-2" 
	x-data="FiltroMedicos"
	@table-data-refresh.document="handleData"
> 
	<template x-for="med in medicos" :key="med.cod">
    <div>
      <input 
      	@change="TABLA.draw()" 
      	type="checkbox" 
      	name="filtro-medico" 
      	:id="`cc-${med.cod}`" 
      	:value="med.cod" 
      	class="d-none">
      <label 
      	class="filtro-type-card-side" 
      	:for="`cc-${med.cod}`" 
      	:title="`${med.nombre}<?= $noEsMedico ? ' - ${med.total}' : '' ?>`"
      >
        <span class="filtro-type-label-side" x-text="med.cod"></span>
        <div class="filtro-type-content-side">
          <span class="filtro-type-icon-side"><?= $this->fetch('./icons/medico.php') ?></span> 

    			<?php if($noEsMedico): ?>
          	<span class="filtro-type-value-side" :id="`contador-${med.cod}`" x-text="med.total"></span>
          <?php endif ?>
        </div>
      </label>
    </div>
	</template>
</div>
