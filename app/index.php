<?php
// inicio la sesion
session_start();

require_once __DIR__ . '/vendor/autoload.php';
use App\Crud\DB;

// Variable para mensajes hacia el usuario
$msj = '';

$opcion = $_POST["submit"] ?? '';
switch ($opcion) {
    case 'Login':
        // Verificar SI YA EXISTE el usuario y si la CONTRASEÑA ESTÁ BIEN
        $DB = new DB();

        $name = $_POST["name"];
        $password = $_POST["password"];
        if (empty(trim($name)) || empty(trim($password))) {
            if (empty($name) && !empty($password)) { 
                $msj = 'El campo nombre está sin rellenar';
            }
            if (empty($password) && !empty($name)){ 
                $msj = 'El campo contraseña está sin rellenar';
            }
            if (empty($password) && empty($name)){ 
                $msj = 'Debe rellenar todos los campos';
            }
        } else { // Si los campos han sido rellenados.
            
            $msj = match($DB->validar_usuario($name, $password)){
                true => iniciarSesion($name),
                false => 'Usuario o contraseña incorrectos',
                null => 'El usuario no existe'
            };

        }

        break;
    case 'Register':
        // Registrar nuevo usuario en BD
        $DB = new DB();

        $name = $_POST["name"];
        $password = $_POST["password"];



        // Valido que los campos no estén sin rellenar
        if (empty(trim($name)) || empty(trim($password))) { //como repito este trozo tanto en LOGIN como en REGISTER podría trasladarlo fuera si voy bien de tiempo.
            if (empty($name) && !empty($password)) { 
                $msj = 'El campo nombre está sin rellenar';
            }
            if (empty($password) && !empty($name)){ 
                $msj = 'El campo contraseña está sin rellenar';
            }
            if (empty($password) && empty($name)){ 
                $msj = 'Debe rellenar todos los campos';
            }
        } else {

            
            if($DB->registrar_usuario($name, $password)){ //si todo está bien lo introducimos nos vamos de aquí.
                iniciarSesion($name);
            } else { // si se repite el usuario no entra.
                $msj = 'El usuario ya existe.';
            }
            

        }
            
        
    

        
        break;
    default:
}


// iniciar sesion
function iniciarSesion($name){
    $_SESSION['usuario'] = $name;
    header('Location: sitio.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="contenedor">
    <div class="contenedor login">
        <h1>Bienvenido</h1>
        <h2 class="error"><?= $msj ?? '' ?></h2>
        <form action="index.php" method="post">
            <input type="text" name="name" placeholder="Nombre de usuario" required> 
            <br>
            <input type="password" name="password" placeholder="Contraseña" required>
            <div class="botones">
                <input type="submit" value="Login" name="submit" class="btn">
                <input type="submit" value="Register" name="submit" class="btn">
            </div>
        </form>
    </div>
</body>
</html>
