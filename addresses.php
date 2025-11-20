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

$contactId = $_GET["id"];
$userId = $_SESSION['user']['id'];


// Se verifica que el contacto exista Y pertenezca al usuario logueado.
$statementContact = $conn->prepare("SELECT * FROM contacts WHERE id = ? AND user_id = ? LIMIT 1");
$statementContact->execute([$contactId, $userId]);
$contact = $statementContact->fetch(PDO::FETCH_ASSOC);


// Si el contacto no existe o no pertenece al usuario, redirige
if (!$contact) { // PDO::fetch() devuelve false si no hay resultados
  http_response_code(404);
  echo "404: Contact not found";
  return;
}

$statementAddresses = $conn->prepare("SELECT * FROM addresses 
                                        WHERE 
                                        user_id = ? 
                                        AND contact_id = ? 
                                        ORDER BY name ASC");

$statementAddresses->execute([$userId, $contactId]);
$addresses = $statementAddresses->fetchAll(PDO::FETCH_ASSOC);

?>

<?php require "partials/header.php" ?>

<div class="container pt-4 p-3">
  <div class="row">
    
    <div class="col-md-8 mx-auto">
      <div class="card card-body">
        
        <h3 class="card-title text-center mb-4">
          Addresses from <?= $contact["name"] ?>
        </h3>

        <div class="mb-3 text-right">
            <a href="newAddress.php?contact_id=<?= $contactId ?>" class="btn btn-success">
                New address
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
                  <a href="editAddress.php?address_id=<?= $address["id"] ?>" class="btn btn-sm btn-info mr-2">Edit</a>
                  <a href="deleteAddress.php?address_id=<?= $address["id"] ?>" class="btn btn-sm btn-warning">Delete</a>
                </div>
              </li>
            <?php endforeach ?>
          </ul>

        <?php endif ?>
        
        <hr class="my-3">
        
        <a href="home.php" class="btn btn-secondary mt-2">Back to contacts</a>
      </div>
    </div>

  </div>
</div>

<?php require "partials/footer.php" ?>
