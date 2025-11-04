<?php

namespace App\Services;

use OpenAI;

class AnalisisIA
{
    /**
     * @var array
     */
    private $data;

    public function __construct(string $jsonFilePath)
    {
        if (!file_exists($jsonFilePath)) {
            throw new \Exception("El archivo no existe: {$jsonFilePath}");
        }

        $jsonContent = file_get_contents($jsonFilePath);
        $this->data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Error al decodificar el JSON: " . json_last_error_msg());
        }
    }

    /**
     * Realiza un análisis de los datos utilizando la API de OpenAI.
     *
     * @return string
     */
    public function analizar(): string
    {
        $apiKey = $_ENV['OPENAI_API_KEY'] ?? null;

        if (!$apiKey) {
            return "Error: La variable de entorno OPENAI_API_KEY no está configurada.";
        }

        try {
            $client = OpenAI::client($apiKey);

            $prompt = "Eres un analista experto en datos de servicios de salud y urgencias.\n\n";
            $prompt .= "Analiza los siguientes datos de un servicio de urgencias, que están en formato JSON. Quiero que identifiques puntos clave, cuellos de botella y me des recomendaciones concretas para mejorar el flujo de pacientes y reducir los tiempos de espera. El análisis debe estar en español.\n\n";
            $prompt .= "Quiero que la salida esté formateada en Markdown, incluyendo títulos, listas y texto en negrita para resaltar los puntos importantes.\n\n";
            $prompt .= "Aquí están los datos:\n\n";
            $prompt .= json_encode($this->data, JSON_PRETTY_PRINT);

            $response = $client->chat()->create([
                'model' => 'gpt-5-nano',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            return "Error al contactar la API de OpenAI: " . $e->getMessage();
        }
    }
}
