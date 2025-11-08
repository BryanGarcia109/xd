<?php

namespace App\Controllers;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Controlador de Documentación Swagger
 */
class SwaggerController extends BaseController
{
    /**
     * Servir la interfaz de Swagger UI
     * GET /api/docs
     */
    public function index(): void
    {
        // Detectar el base path
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $scriptDir = dirname($scriptName);
        $basePath = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;
        
        // URL del archivo OpenAPI
        $openApiUrl = $basePath . '/api/docs/openapi.json';
        
        // HTML de Swagger UI
        $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Gestión de Canchas Sintéticas</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui.css" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin:0;
            background: #fafafa;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.10.3/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "{$openApiUrl}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                tryItOutEnabled: true
            });
        };
    </script>
</body>
</html>
HTML;

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }

    /**
     * Servir el archivo OpenAPI en formato JSON
     * GET /api/docs/openapi.json
     */
    public function openapi(): void
    {
        $yamlFile = __DIR__ . '/../../docs/openapi.yaml';
        
        // Verificar que el archivo existe
        if (!file_exists($yamlFile)) {
            $this->errorResponse('Documentación OpenAPI no encontrada en: ' . $yamlFile, 404);
            return;
        }

        // Verificar que podemos leer el archivo
        if (!is_readable($yamlFile)) {
            $this->errorResponse('No se puede leer el archivo de documentación', 500);
            return;
        }

        try {
            // Verificar que la clase Yaml existe
            if (!class_exists('Symfony\Component\Yaml\Yaml')) {
                $this->errorResponse('Librería Symfony YAML no está disponible', 500);
                return;
            }

            // Leer el contenido del archivo primero para debugging
            $yamlContent = file_get_contents($yamlFile);
            if ($yamlContent === false) {
                $this->errorResponse('Error al leer el archivo YAML', 500);
                return;
            }

            // Parsear YAML usando Symfony YAML
            $openApi = Yaml::parse($yamlContent);
            
            if ($openApi === null || !is_array($openApi)) {
                $this->errorResponse('Error al parsear el archivo YAML: formato inválido', 500);
                return;
            }
            
            // Detectar el base path para actualizar el servidor URL
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
            $scriptDir = dirname($scriptName);
            $basePath = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;
            
            // Obtener el protocolo y host
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = "{$protocol}://{$host}{$basePath}";
            
            // Actualizar el servidor en el OpenAPI
            $openApi['servers'] = [
                [
                    'url' => $baseUrl,
                    'description' => 'Servidor actual'
                ]
            ];
            
            // Convertir a JSON
            $json = json_encode($openApi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            if ($json === false) {
                $error = json_last_error_msg();
                $this->errorResponse('Error al convertir a JSON: ' . $error, 500);
                return;
            }
            
            header('Content-Type: application/json; charset=utf-8');
            echo $json;
            exit;
        } catch (ParseException $e) {
            $this->errorResponse('Error de sintaxis YAML: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            $this->errorResponse('Error al procesar la documentación: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine(), 500);
        }
    }
}

