<div class="d-flex gap-1 overflow-x-auto ms-auto me-2">
	<?php foreach([
		'warning' 	=> ['icon' => 'warning', 'text' => 'Advertencias'],
		'man' 		=> ['icon' => 'man', 'text' => 'Hombres'],
		'woman' 	=> ['icon' => 'woman', 'text' => 'Mujeres'],
		'admission' => ['icon' => 'clipboard-plus', 'text' => 'Admisiones']
	] as $id => $type): ?>
		<div>
			<input type="checkbox" name="filtro-type" id="cc-<?= $id ?>" value="<?= $id ?>" class="d-none">
			<label class="filtro-type-card-side" for="cc-<?= $id ?>">
				<span class="filtro-type-label-side"><?= $type['text'] ?></span>
				<div class="filtro-type-content-side">
					<span class="filtro-type-icon-side"><?= $this->fetch("./icons/$type[icon].php") ?></span>	
			    	<span class="filtro-type-value-side" id="contador-<?= $id ?>"></span>
				</div>
			</label>
		</div>
	<?php endforeach ?>		

	<?php if(false): // Por  ahora no mostramos esto  ?>
		<span class="indicator-card-side indicator-pending" type="button">
			<span class="indicator-label-side">Turnos Pendientes</span>
			<div class="indicator-content-side">
				<span class="indicator-icon-side"><?= $this->fetch('./icons/late.php') ?></span>	
		    	<span class="indicator-value-side" id="digi"></span>
			</div>
		</span>
	<?php endif ?>
</div>
