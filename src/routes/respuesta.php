<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Indica si la pregunta fur respondida correctamente por el equipo o no
$app->put('/api/respuesta/{id}', function (Request $request, Response $response) {
    try {
        // Obtiene la pregunta de la base de datos
        $pregunta_id = $request->getAttribute('id');

        $sql = "SELECT p.id, p.puntaje, c.juego_id, j.resta_incorrectas FROM pregunta p LEFT JOIN categoria c ON p.categoria_id = c.id LEFT JOIN juego j ON c.juego_id = j.id WHERE p.id = '$pregunta_id'";

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
        $resta_incorrectas = $preguntas[0]->resta_incorrectas;

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
            $sql="UPDATE equipo SET puntaje = puntaje + $puntaje_pregunta WHERE juego_id = $juego_id AND activo = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute();
        } elseif ($resta_incorrectas) {
            $sql="UPDATE equipo SET puntaje = puntaje - $puntaje_pregunta WHERE juego_id = $juego_id AND activo = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute();
        }

        // Actualizar cuál es el equipo activo
        $sql = "SELECT * FROM equipo WHERE juego_id = $juego_id ORDER BY id";
        $stmt = $db->query($sql);
        $equipos = $stmt->fetchAll(PDO::FETCH_OBJ);

        $nuevo_equipo_activo = 0;

        // Verifica si el último equipo es el activo
        if ($equipos[count($equipos)-1]->activo == 1) {
            // Si es, marca al primer equipo como siquiente activo
            $nuevo_equipo_activo = $equipos[0]->id;
        } else {
            // Si no, recorre todos los equipos hasta encontrar al activo
            $siguiente_equipo_activo = false;
            foreach ($equipos as $equipo) {
                // Si el anterior lo marcó como activo, selecciona al siguiente
                if ($siquiente_equipo_activo) {
                    $nuevo_equipo_activo = $equipo->id;
                    $siquiente_equipo_activo = false;
                    // print_r($equipo);
                }
                // Si encuentra un equipo activo, lo marca para seleccionar al siguiente
                if ($equipo->activo == 1) {
                    $siquiente_equipo_activo = true;
                }
            }
        }

        $sql="UPDATE equipo SET activo = 0 WHERE juego_id = $juego_id";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $sql="UPDATE equipo SET activo = 1 WHERE id = $nuevo_equipo_activo";
        $stmt = $db->prepare($sql);
        $stmt->execute();

        $db = null;
        return messageResponse($response, 'Respuesta registrada.', 200);
    } catch (PDOException $e) {
        $db = null;
        return respondWithError($response, $e->getMessage(), 500);
    }
});
