<?php

require "database.php";

session_start();

// Verifica si la sesión de usuario está iniciada
if (!isset($_SESSION["user"])) {
  header("Location: login.php");
  return;
}

// Verifica si se proporcionó un ID de contacto en la URL
if (!isset($_GET["id"])) {
  header("Location: index.php");
  return;
}

$contact_id = $_GET["id"];
$user_id = $_SESSION['user']['id'];


// Se verifica que el contacto exista Y pertenezca al usuario logueado.
$statement_contact = $conn->prepare("SELECT * FROM contacts WHERE id = ? AND user_id = ? LIMIT 1");
$statement_contact->execute([$contact_id, $user_id]);
$contact = $statement_contact->fetch(PDO::FETCH_ASSOC);


// Si el contacto no existe o no pertenece al usuario, redirige
if (!$contact) { // PDO::fetch() devuelve false si no hay resultados
  echo "404: Contact not found";
  return;
}

$statement_addresses = $conn->prepare("SELECT * FROM addresses 
                                        WHERE 
                                        user_id = ? 
                                        AND contact_id = ? 
                                        ORDER BY name ASC");

$statement_addresses->execute([$user_id, $contact_id]);
$addresses = $statement_addresses->fetchAll(PDO::FETCH_ASSOC);

?>

<?php require "partials/header.php" ?>

<div class="container pt-4 p-3">
  <div class="row">
    
    <div class="col-md-8 mx-auto">
      <div class="card card-body">
        
        <h3 class="card-title text-center mb-4">
          Direcciones de <?= $contact["name"] ?>
        </h3>

        <div class="mb-3 text-right">
            <a href="newAddress.php?contact_id=<?= $contact_id ?>" class="btn btn-success">
                Nueva dirección
            </a>
        </div>
        
        <?php if (count($addresses) == 0): ?>
          <div class="alert alert-warning text-center" role="alert">
            No hay direcciones guardadas para este contacto.
          </div>
        <?php else: ?>
          
          <ul class="list-group">
            <?php foreach ($addresses as $address): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center bg-secondary mb-2">
                <div>
                  <h5 class="mb-1">
                    <b> <?= $address['name'] ?>:</b>
                  </h5>
                  <p class="mb-1">
                    <?= $address["street"] ?>
                  </p>
                </div>
                <div>
                  <a href="editAddress.php?id=<?= $address["id"] ?>" class="btn btn-sm btn-info mr-2">Editar</a>
                  <a href="deleteAddress.php?id=<?= $address["id"] ?>" class="btn btn-sm btn-warning">Eliminar</a>
                </div>
              </li>
            <?php endforeach ?>
          </ul>

        <?php endif ?>
        
        <hr class="my-3">
        
        <a href="home.php" class="btn btn-secondary mt-2">Volver a Contactos</a>
      </div>
    </div>

  </div>
</div>

<?php require "partials/footer.php" ?>
