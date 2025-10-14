<div 
	x-data="TriageMinDiffChart" 
	@table-data-refresh.document="handleRefreshData"
	class="p-2 rounded bg-body-tertiary border mt-5 overflow-auto"
>
	<?= $this->fetch('./partials/ConteoTriage.php') ?>
	<hr class="border-dark-subtle">
	<div chart style="min-width: 600px;"></div>
</div>
