<?php

declare(strict_types=1);

namespace App;

use Medoo\Medoo;

class DB8cho 
{
	public function __construct(
		public readonly Medoo $db
	) {}

	/**
	 * Busca la informacion de un único registro de Digiturno
	 * @param int $turnoId 
	 */
	public function findDigiturnoById(int $turnoId, string|array $campos = '*'): ?array 
	{
		return $this->db->get('digiturno', $campos, [
			'digiturno_id' => $turnoId
		]);
	}

	/**
	 * Busca la información de varios digiturnos por id.
	 * @param int[] $turnosId Listado de ids de turnos
	 */
	public function getDigiturnosById(array $turnosId, string|array $campos = '*'): array 
	{
		return $this->db->select('digiturno', $campos, [
			'digiturno_id' => $turnosId
		]);
	}
}
