<?php

  require "database.php";

  session_start();

  if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    return;
  }

  $error = null;

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["name"]) || empty($_POST["street"])) {
      $error = "Please fill all the fields.";
    } else {
      $name = $_POST["name"];
      $street = $_POST["street"];
      $contactId = $_POST["contact_id"];
      //Buscar direcciones con ese nombre o la misma calle

      $statement = $conn->prepare("SELECT * FROM addresses WHERE (name = :name OR street = :street) AND contact_id = :contact_id LIMIT 1");
      $statement->execute([
        ":name" => $name,
        ":street" => $street,
        ":contact_id" => $contactId,
      ]);
      if ($statement->rowCount() > 0) {
        $error = "This address already exists.";
      }else{
        $statement = $conn->prepare("INSERT INTO addresses (user_id, contact_id, name, street) VALUES ({$_SESSION['user']['id']}, :contact_id, :name, :street)");
        $statement->execute([
          ":name" => $name,
          ":street" => $street,
          ":contact_id" => $contactId,
        ]);
  
        //Buscar el nombre del contacto
        $statement = $conn->prepare("SELECT * FROM contacts WHERE id = :contactId LIMIT 1");
        $statement->bindParam(":contactId", $contactId);
        $statement->execute();
        $contact = $statement->fetch(PDO::FETCH_ASSOC);
  
        $_SESSION["flash"] = ["message" => "New address to {$contact['name']} added."];
  
        header("Location: home.php");
      }
    }
  }
?>

<?php require "partials/header.php" ?>

<div class="container pt-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Add New Address</div>
        <div class="card-body">
          <?php if ($error): ?>
            <p class="text-danger">
              <?= $error ?>
            </p>
          <?php endif ?>
          <form method="POST" action="newAddress.php">
            <div class="mb-3 row">
              <label for="name" class="col-md-4 col-form-label text-md-end">Address name</label>

              <div class="col-md-6">
                <input id="name" type="text" class="form-control" name="name" autocomplete="name" autofocus>
                <input hidden id="contact_id" type="text" class="form-control" name="contact_id" value= <?= $_GET["id"]?>>
              </div>
            </div>

            <div class="mb-3 row">
              <label for="street" class="col-md-4 col-form-label text-md-end">Street</label>

              <div class="col-md-6">
                <input id="street" type="text" class="form-control" name="street" autocomplete="street" autofocus>
              </div>
            </div>

            <div class="mb-3 row">
              <div class="col-md-6 offset-md-4">
                <button type="submit" class="btn btn-primary">Submit</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require "partials/footer.php" ?>

