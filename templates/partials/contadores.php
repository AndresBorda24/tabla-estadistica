<div class="d-flex gap-3 pb-2 overflow-x-auto justify-content-center">
	<span class="indicator-card-side indicator-warning">
		<span class="indicator-label-side">Advertencias</span>
		<div class="indicator-content-side">
			<span class="indicator-icon-side"><?= $this->fetch('./icons/warning.php') ?></span>	
	    	<span class="indicator-value-side" id="adv"></span>
		</div>
	</span>
	<span class="indicator-card-side indicator-men">
		<span class="indicator-label-side">Hombres</span>
		<div class="indicator-content-side">
			<span class="indicator-icon-side"><?= $this->fetch('./icons/man.php') ?></span>
	    	<span class="indicator-value-side" id="chombre"></span>
		</div>
	</span>
	<span class="indicator-card-side indicator-women">
		<span class="indicator-label-side">Mujeres</span>
		<div class="indicator-content-side">
			<span class="indicator-icon-side"><?= $this->fetch('./icons/woman.php') ?></span>	
	    	<span class="indicator-value-side" id="cmujer"></span>
		</div>
	</span>
	<span class="indicator-card-side indicator-admissions">
		<span class="indicator-label-side">Admisiones</span>
		<div class="indicator-content-side">
			<span class="indicator-icon-side"><?= $this->fetch('./icons/clipboard-plus.php') ?></span>	
	    	<span class="indicator-value-side" id="tadm"></span>
		</div>
	</span>
	<span class="indicator-card-side indicator-no-admission">
		<span class="indicator-label-side">Sin Admisión</span>
		<div class="indicator-content-side">
			<span class="indicator-icon-side"><?= $this->fetch('./icons/clipboard-late.php') ?></span>	
	    	<span class="indicator-value-side" id="stria"></span>
		</div>
	</span>
	<span class="indicator-card-side indicator-no-emergency">
		<span class="indicator-label-side">Sin H. Urgencias</span>
		<div class="indicator-content-side">
			<span class="indicator-icon-side"><?= $this->fetch('./icons/heart-monitor.php') ?></span>	
	    	<span class="indicator-value-side" id="shur"></span>
		</div>
	</span>
	<span class="indicator-card-side indicator-pending">
		<span class="indicator-label-side">Turnos Pendientes</span>
		<div class="indicator-content-side">
			<span class="indicator-icon-side"><?= $this->fetch('./icons/late.php') ?></span>	
	    	<span class="indicator-value-side" id="digi"></span>
		</div>
	</span>
</div>
