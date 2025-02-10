<?php
namespace App\Crud;

use mysqli;
use mysqli_sql_exception;
use mysqli_stmt;

class DB
{

   private $con;

   public function __construct()  {
      //Tuve que utilizar esto que encontré en stackoverflow.com para que Windows aceptase el fichero .env
      $dbconfig = parse_ini_file(".env");

      $host=$dbconfig['HOST'];
      $user=$dbconfig['DB_USER'];
      $pass=$dbconfig['PASSWORD'];
      $db=$dbconfig['DATABASE'];
      
      try {
         $this->con=new mysqli($host, $user, $pass, $db);
      } catch (mysqli_sql_exception $e) {
         die ("Error accediendo a la base de datos " . $e->getMessage());
      }
   }



   /**
    * @param string $nombre
    * @param string $pass
    * @return bool
    * //Verifica si un usuario existe en la base de datos
    */
   public function validar_usuario(string $nombre, string  $pass):bool | null{
      // Verificar la conexión antes de ejecutar
      if (!$this->con) {
         return false;
      }

      $sentencia = "SELECT password FROM usuarios WHERE nombre = ?";
      $stmt = $this->ejecuta_sentencia($sentencia, [$nombre]);
      $stmt->bind_result($pass_store);
        if (!$stmt->fetch()) {
         return null;
      }
      if(password_verify($pass, $pass_store)) {
         return true;
      }
      return false;

   }
/*
 * Este método tendría que investigar en el diccionario de datos
 * Devolverá qué campos de esta tabla son claves foráneas
 * */

 // Todavía no se me ha ocurrido como usar este método, lo dejo aquí por si el Pablo del futuro da con la tecla.
   public function get_foraneas(string $tabla): array
   {
      return [];
   }


   public function get_campos(string $tabla):array{
      $campos = [];
      if (!$this->con) {
         return false;
      }
         $sentencia = "DESCRIBE $tabla"; // describe -> Field, Type...
         $result = $this->con->query($sentencia);

         // saco uno a uno los campos
         foreach ($result as $row) {
            $campos[] = $row['Field'];
         }

         return $campos;

   }

   // Retorna un array con las filas de una tabla
   //No se me ocurre cómo ni porqué utilizaría una sentencia como parámetro de este método, así que de momento la comento.
   public function get_filas($tabla):array {
      $filas=[];
      if (!$this->con) {
         return false;
      }

      $sentencia = "SELECT * from $tabla";
      $resultado = $this->con->query($sentencia);

      if ($resultado && $resultado->num_rows > 0) { // para poder sacar filas, necesito que HAYA RESULTADO (es decir que no sea false) Y QUE HAYA MÁS DE 0 ROWS dentro
         while($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
         }
      }



      return $filas;
   }

   //Borra una fila de una tabla dada su código
   //Retorna un mensaje diciendo si lo ha podido borrar o no
   public function borrar_fila(int $cod, string $table):string{

      if (!$this->con) {
         return "Error en la conexión";
      }

      $sentencia = "DELETE FROM $table WHERE cod =?";
      
      $datos = [];
      $datos[] = $cod;
      try {
      $stmt = $this->ejecuta_sentencia($sentencia, $datos);
      } catch (mysqli_sql_exception $e) {
         return "La fila que intenta eliminar está atada a otras tablas, empiece eliminando por ahí.";
      }


      if ($stmt) {
         return "La fila $cod ha sido borrada correctamente";
      } else {
         return "Error al borrar la fila $cod";
      }
   }
   public function close(){
      $this->con->close();
   }

   // Añade una fila cuyos valores se pasan en un array.
   //Tengo el nombre de la tabla y el array ["nombre_Campo"=>"valor"]
   public function add_fila(string $tabla,array $campos){
      if (!$this->con) {
         return false;

      }

      $datos = [];
      $camposInsert = [];
      foreach ($campos as $nombre => $valor) {
         $datos[] = $valor;
         $camposInsert[] = $nombre;
      }

      $camposInsert = implode(",",$camposInsert); // proceso inverso del explode (array a string)

      $numValores = count($campos);
      $interrogantes = array_fill(0, $numValores, '?'); // relleno un array desde el inddice 0 hasta el num de valores que tengo con "?"
      $interrogantes = implode(',',$interrogantes); 
      
      // en el primer parentesis irán los campos, y en VALUES, los valores que le meto
      $sentencia = "INSERT INTO $tabla ($camposInsert) VALUES ($interrogantes)";
      $this->ejecuta_sentencia($sentencia, $datos);

      $this->con->commit(); // guardamos los cambios
      $this->con->close();

   }

   //Registra un usuario en la tabla usuarios y me pasan el nombre y el pass
   //El pass tiene que estar cifrado antes de insertar
   //Retorna un bool = true si ha ido bien o un mensaje si ha ocurrdio algún problema, como que el usuario ya existiese
   public function registrar_usuario($nombre, $pass): bool|string {
      // Salgo si no hay conexión con BD o el usuario ya existe.
      if (!$this->con) {
         return false;
      }
      if ($this->existe_usuario($nombre)) {
         return false; // si me acuerdo antes de acabar, me gustaría reemplazar estos falses por msj con la info del error concreto que ocurre.
      }
      $pass = password_hash($pass, PASSWORD_DEFAULT); // proteger la contraseña

      $sentencia = "INSERT INTO usuarios (nombre, password) VALUES (?,?)";
      $this->ejecuta_sentencia($sentencia, [$nombre, $pass]);

      $this->con->commit(); // guardamos los cambios
      $this->con->close();
      return true;
   }

   //Verifica si un usuario existe o no
   private function existe_usuario(string $nombre):bool {
      
      $sentencia = "SELECT 1 FROM usuarios WHERE nombre = ?";
      $stmt = $this->ejecuta_sentencia($sentencia, [$nombre]);

      // Comprueba si hay resultados
      $stmt->bind_result($resultado);
      if ($stmt->fetch()) {
         return true;
      }
      return false;
   }

   //Ejecuta una sentencia y retorna un mysql_stmt
   //La sentencia hay que paraemtrizarla
   //Recibo la sentencia con parámetros y un array indexado con los valores

   // CONTRA TODO PRONOSTICO FUNCIONÓ A LA PRIMERA [NO TOCAR BAJO NINGÚN CONCEPTO PABLO DEL FUTURO]
   private function ejecuta_sentencia(string $sentencia, array $datos): mysqli_stmt {
      $stmt=$this->con->stmt_init();
      $stmt->prepare($sentencia);

      // Clasifico los tipos como se explicó en clase.
      $tipos = '';
      foreach($datos as $dato) {
         if(is_string($dato)){
            $tipos.='s';
         } 
         if(is_int($dato)){
            $tipos.='i';
         }
         if(is_double($dato)){
            $tipos.='d';
         }

      }

      $stmt->bind_param($tipos,...$datos);
      $stmt->execute();
      $stmt->store_result();

      return $stmt;
   }

}

?>
