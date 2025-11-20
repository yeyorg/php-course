<?php

require "database.php";

session_start();

if (!isset($_SESSION["user"])) {
  header("Location: login.php");
  return;
}

$error = null;
$userId = $_SESSION['user']['id'];

//Verificar si se recibió el ID de la dirección a editar
if (!isset($_GET["address_id"])) {
  header("Location: index.php");
  return;
}

$addressId = $_GET["address_id"];

//Oobtener la dirección 
$statementSelect = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1");
$statementSelect->execute([$addressId, $userId]);
$address = $statementSelect->fetch(PDO::FETCH_ASSOC);

// Si la dirección no existe o no pertenece al usuario
if (!$address) {
  http_response_code(404);
  echo "404: Address not found";
  return;
}

// Guardamos el ID del contacto al que pertenece esta dirección
$contactId = $address['contact_id'];


// Lógica para manejar el envío del formulario (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
  //Validar los campos del formulario
  if (empty($_POST["name"]) || empty($_POST["street"])) {
    $error = "Please fill all the fields.";
  } else {
    $name = trim($_POST["name"]);
    $street = trim($_POST["street"]);
    
    // verificar duplicados (excluyendo el registro actual)
    $statementCheck = $conn->prepare("SELECT * FROM addresses 
                                        WHERE 
                                        (name = :name OR street = :street) 
                                        AND contact_id = :contact_id 
                                        AND id != :address_id 
                                        LIMIT 1");
    $statementCheck->execute([
      ":name" => $name,
      ":street" => $street,
      ":contact_id" => $contactId,
      ":address_id" => $addressId,
    ]);
    
    if ($statementCheck->rowCount() > 0) {
      $error = "Another address with the same name or street already exists for this contact.";
    } else {
      
      // UPDATE
      $statementUpdate = $conn->prepare("UPDATE addresses SET name = :name, street = :street WHERE id = :id AND user_id = :user_id");
      $statementUpdate->execute([
        ":id" => $addressId,
        ":user_id" => $userId,
        ":name" => $name,
        ":street" => $street,
      ]);
      
      $_SESSION["flash"] = ["message" => "Address '{$name}' updated successfully."];
      
      // Redirigir a la lista de direcciones del contacto
      header("Location: addresses.php?id={$contactId}");
      return;
    }
    
    // Si hubo un error de validación (duplicado), se actualizan los datos en el array $address
    // para que los campos del formulario mantengan los valores que el usuario intentó enviar
    $address['name'] = $name;
    $address['street'] = $street;
  }
}

?>

<?php require "partials/header.php" ?>

<div class="container pt-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">Edit Address: <b><?= htmlspecialchars($address['name']) ?></b></div>
        <div class="card-body">
          <?php if ($error): ?>
            <p class="text-danger">
              <?= $error ?>
            </p>
          <?php endif ?>
          
          <form method="POST" action="editAddress.php?address_id=<?= htmlspecialchars($addressId) ?>">
            
            <div class="mb-3 row">
              <label for="name" class="col-md-4 col-form-label text-md-end">Address name</label>

              <div class="col-md-6">
                <input id="name" type="text" class="form-control" name="name" autocomplete="name" autofocus value="<?= htmlspecialchars($address['name']) ?>">
              </div>
            </div>

            <div class="mb-3 row">
              <label for="street" class="col-md-4 col-form-label text-md-end">Street</label>

              <div class="col-md-6">
                <input id="street" type="text" class="form-control" name="street" autocomplete="street" autofocus value="<?= htmlspecialchars($address['street']) ?>">
              </div>
            </div>

            <div class="mb-3 row">
              <div class="col-md-6 offset-md-4">
                <button type="submit" class="btn btn-primary">Update Address</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require "partials/footer.php" ?>
