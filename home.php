<?php

require "database.php";

session_start();

if (!isset($_SESSION["user"])) {
  header("Location: login.php");
  return;
}

$contacts = $conn->query("SELECT * FROM contacts WHERE user_id = {$_SESSION['user']['id']}");

?>

<?php require "partials/header.php" ?>

<div class="container pt-4 p-3">
  <div class="row">
    
    <?php if ($contacts->rowCount() == 0): ?>
      <div class="col-md-4 mx-auto">
        <div class="card card-body text-center">
          <p>No contacts saved yet</p>
          <a href="add.php">Add One!</a>
        </div>
      </div>
    <?php endif ?>
    <?php foreach ($contacts as $contact): ?>
      <div class="col-md-4 mb-3">
        <div class="card text-center">
          <div class="card-body">
            <h3 class="card-title text-capitalize"><?= $contact["name"] ?></h3>
            <p class="m-2">Phone: <?= $contact["phone_number"] ?></p>
            
            <?php
            $addresses = $conn->query("SELECT * FROM addresses 
                                        WHERE 
                                        user_id = {$_SESSION['user']['id']} 
                                        AND contact_id = {$contact['id']} ");

            foreach ($addresses as $address): ?>
              
              <div class="card bg-secondary mb-3 text-left">
                <div class="card-body p-2">
                  <p class="m-0">
                    <b><?= $address['name']?>:</b> <?= $address["street"] ?>
                  </p>
                  <div class="mt-2 text-right">
                    <a href="editAddress.php?id=<?= $address["id"] ?>" class="btn btn-sm btn-info">Editar</a>
                    <a href="deleteAddress.php?id=<?= $address["id"] ?>" class="btn btn-sm btn-warning">Eliminar</a>
                  </div>
                </div>
              </div>
              <?php endforeach ?>
            <hr class="my-3">
            
            <a href="edit.php?id=<?= $contact["id"] ?>" class="btn btn-secondary mb-2">Edit Contact</a>
            <a href="delete.php?id=<?= $contact["id"] ?>" class="btn btn-danger mb-2">Delete Contact</a>
            <a href="addresses.php?id=<?= $contact["id"] ?>" class="btn btn-success mb-2">Addresses</a>
          </div>
        </div>
      </div>
    <?php endforeach ?>

  </div>
</div>

<?php require "partials/footer.php" ?>
