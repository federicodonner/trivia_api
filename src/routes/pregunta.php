<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Devuelve los detalles de la pregunta en base al hash
$app->get('/api/preguntahash/{hash}', function (Request $request, Response $response) {
    try {
        // Obtiene la pregunta de la base de datos
        $pregunta_hash = $request->getAttribute('hash');
        $sql = "SELECT p.*, c.id AS categoria_id, c.nombre AS categoria_nombre, c.juego_id FROM pregunta p LEFT JOIN categoria c ON p.categoria_id = c.id WHERE p.hash = '$pregunta_hash'";

        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $preguntas = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Verifica que la pregunta exista
        if (count($preguntas) == 0) {
            $db = null;
            return messageResponse($response, 'La pregunta seleccionada no existe.', 404);
        }

        // Devuelve los equipos para mostrar el puntaje en la pantalla de pregunta
        $juego_id = $preguntas[0]->juego_id;
        $sql="SELECT * FROM equipo WHERE juego_id = $juego_id";
        $stmt = $db->query($sql);
        $equipos = $stmt->fetchAll(PDO::FETCH_OBJ);

        $preguntas[0]->equipos = $equipos;

        // Si exsite la pregunta, devuelvo los datos;
        return dataResponse($response, $preguntas[0], 200);

        // $pregunta = $preguntas[0];
    } catch (PDOException $e) {
        $db = null;
        return respondWithError($response, $e->getMessage(), 500);
    }
});



// Devuelve los detalles de la pregunta
$app->get('/api/pregunta/{id}', function (Request $request, Response $response) {
    try {
        // Obtiene la pregunta de la base de datos
        $pregunta_id = $request->getAttribute('id');
        $sql = "SELECT p.*, c.id AS categoria_id, c.nombre AS categoria_nombre FROM pregunta p LEFT JOIN categoria c ON p.categoria_id = c.id WHERE p.id = '$pregunta_id'";

        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $preguntas = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Verifica que la pregunta exista
        if (count($preguntas) == 0) {
            $db = null;
            return messageResponse($response, 'La pregunta seleccionada no existe.', 404);
        }

        // Si exsite la pregunta, devuelvo los datos;
        return dataResponse($response, $preguntas[0], 200);

        // $pregunta = $preguntas[0];
    } catch (PDOException $e) {
        $db = null;
        return respondWithError($response, $e->getMessage(), 500);
    }
});
