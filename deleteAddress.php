<?php

require "database.php";

session_start();

if (!isset($_SESSION["user"])) {
  header("Location: login.php");
  return;
}
$userId = $_SESSION['user']['id'];

//Verificar si se recibió el ID de la dirección a borrar
if (!isset($_GET["address_id"])) {
  header("Location: index.php");
  return;
}
$addressId = $_GET["address_id"];

$statement = $conn->prepare("SELECT * FROM addresses WHERE id = :id LIMIT 1");
$statement->execute([":id" => $addressId]);

if ($statement->rowCount() == 0) {
  http_response_code(404);
  echo("HTTP 404 ADDRESS NOT FOUND");
  return;
}

$address = $statement->fetch(PDO::FETCH_ASSOC);

if ($address["user_id"] !== $userId) {
  http_response_code(403);
  echo("HTTP 403 UNAUTHORIZED");
  return;
}
//borrar primero las dirrecciones
$conn->prepare("DELETE FROM addresses WHERE id = :id")->execute([":id" => $addressId]);

$_SESSION["flash"] = ["message" => "Address deleted."];

header("Location: home.php");
