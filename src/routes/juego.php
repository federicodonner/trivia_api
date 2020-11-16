<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Devuelve los detalles del juego en base al hash
$app->get('/api/juego/{hash}', function (Request $request, Response $response) {
    try {
        // Obtiene el hash del request
        $juego_hash = $request->getAttribute('hash');

        $sql = "SELECT * FROM juego WHERE hash = '$juego_hash'";

        $db = new db();
        $db = $db->connect();

        $stmt = $db->query($sql);
        $juegos = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Si no hay registros, salgo con error
        if (count($juegos) == 0) {
            $db = null;
            return messageResponse($response, 'El hash no existe, verifícalo y vuelve a intentar.', 404);
        }

        // Si existe un juego, trae el resto de los datos
        $juego = $juegos[0];
        $juego_id = $juego->id;

        $sql = "SELECT id, nombre FROM categoria WHERE juego_id = $juego_id";
        $stmt = $db->query($sql);
        $categorias = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Por cada categoria, trae las preguntas correspondientes
        foreach ($categorias as $categoria) {
            $categoria_id = $categoria->id;

            $sql = "SELECT id, texto, puntaje, respondida FROM pregunta WHERE categoria_id = $categoria_id ";

            $stmt = $db->query($sql);
            $preguntas = $stmt->fetchAll(PDO::FETCH_OBJ);

            $categoria->preguntas = $preguntas;
        }

        $juego->categorias = $categorias;

        // Agrega la información de equipos
        $sql = "SELECT id, nombre, puntaje, activo FROM equipo WHERE juego_id = $juego_id";
        $stmt = $db->query($sql);
        $equipos = $stmt->fetchAll(PDO::FETCH_OBJ);

        $juego->equipos = $equipos;


        return dataResponse($response, $juego, 200);
    } catch (PDOException $e) {
        $db = null;
        return respondWithError($response, $e->getMessage(), 500);
    }
});
