<?php

use \Psr\Http\Message\ResponseInterface as Response;

// Responde un texto
 function messageResponse(Response $response, String $text, Int $status)
 {
     $responseBody = array('detail' => $text);
     $newResponse = $response
 ->withStatus($status)
 ->withJson($responseBody);
     return $newResponse;
 };

// Responde un objeto
function dataResponse(Response $response, object $data, Int $status)
{
    $newResponse = $response
->withStatus($status)
->withJson($data);
    return $newResponse;
}


// Devuelve un string aleatorio de largo especificado
 function random_str($length, $keyspace = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ')
 {
     $pieces = [];
     $max = mb_strlen($keyspace, '8bit') - 1;
     for ($i = 0; $i < $length; ++$i) {
         $pieces []= $keyspace[random_int(0, $max)];
     }
     return implode('', $pieces);
 };

 // Devuelve el usuario del login en base al token
  function verifyToken(String $access_token)
  {
      if (!empty($access_token)) {
          $sql = "SELECT * FROM login WHERE token = '$access_token'";
          try {
              $db = new db();
              $db = $db->connect();
              $stmt = $db->query($sql);
              $users = $stmt->fetchAll(PDO::FETCH_OBJ);
              $db = null;
              return $users;
          } catch (PDOException $e) {
              echo '{"error":{"text": '.$e->getMessage().'}}';
          }
      } else {
          return [];
      }
  };

  function verificarEquipoEnJuego(Int $equipo_id, Int $juego_id)
  {

      // Si no se envía el id del equipo o del juego devuelve false
      if (!$equipo_id || !$juego_id) {
          return false;
      }

      // Hace la consulta para ver si el equipo existe y pertenece al juego
      $sql = "SELECT id FROM equipo WHERE id = $equipo_id AND juego_id = $juego_id";

      try {
          $db = new db();
          $db = $db->connect();
          $stmt = $db->query($sql);
          $equipos = $stmt->fetchAll(PDO::FETCH_OBJ);

          if (count($equipos) == 0) {
              return false;
          }
          return true;
      } catch (PDOException $e) {
          echo '{"error":{"text": '.$e->getMessage().'}}';
      }
  }
