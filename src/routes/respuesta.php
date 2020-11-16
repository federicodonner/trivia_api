<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Indica si la pregunta fur respondida correctamente por el equipo o no
$app->put('/api/respuesta/{id}', function (Request $request, Response $response) {
    try {
        // Obtiene la pregunta de la base de datos
        $pregunta_id = $request->getAttribute('id');

        $sql = "SELECT p.id, p.puntaje, c.juego_id FROM pregunta p LEFT JOIN categoria c ON p.categoria_id = c.id WHERE p.id = '$pregunta_id'";

        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $preguntas = $stmt->fetchAll(PDO::FETCH_OBJ);


        // Verifica que la pregunta exista
        if (count($preguntas) == 0) {
            $db = null;
            return messageResponse($response, 'La pregunta seleccionada no existe.', 404);
        }

        $juego_id = $preguntas[0]->juego_id;
        $puntaje_pregunta = $preguntas[0]->puntaje;

        // Verifica que haya indicado el equipo que respondió y que sea correcto
        $equipo_id = $request->getParam('equipo_id');

        if (!$equipo_id) {
            $db = null;
            return messageResponse($response, 'Equipo no especificado.', 400);
        }

        if (!verificarEquipoEnJuego($equipo_id, $juego_id)) {
            $db = null;
            return messageResponse($response, 'El equipo especificado no corresponde al juego en curso.', 404);
        }

        // Verifica que haya indicado si respondió correctamente
        $respuesta_correcta = $request->getParam('respuesta_correcta');

        if ($respuesta_correcta != 0 && $respuesta_correcta != 1) {
            $db = null;
            return messageResponse($response, 'Formato incorrecto, respuesta correcta debe ser 1 o 0.', 400);
        }

        // Actualiza la pregunta marcándola como respondida
        $sql="UPDATE pregunta SET respondida = 1, correcta = :respuesta_correcta WHERE id = :pregunta_id";

        $stmt = $db->prepare($sql);

        $stmt->bindParam(':respuesta_correcta', $respuesta_correcta);
        $stmt->bindParam(':pregunta_id', $pregunta_id);
        $stmt->execute();

        // Si fue respondida correctamente, le suma los puntos al equipo
        if ($respuesta_correcta) {
            // Obtiene el puntaje del equipo
            $sql="UPDATE equipo SET puntaje = puntaje + $puntaje_pregunta WHERE id = $equipo_id";
            $stmt = $db->prepare($sql);
            $stmt->execute();
        }

        $db = null;
        return messageResponse($response, 'Respuesta registrada.', 200);
    } catch (PDOException $e) {
        $db = null;
        return respondWithError($response, $e->getMessage(), 500);
    }
});
