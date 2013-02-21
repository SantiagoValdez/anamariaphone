

<?php

/**
* Servidor Ana Maria Phone
*
* Servidor rest para conexion entre dispositivo 
* movil y servidor 
*
* @author Santiago Valdez
* @author santiagovaldez@groupweird.com
* 
*/

require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();


/**
* Ruteo de URLs a Funciones
*
* Se utilza post para la actualizacion en cobro 
* porque no sabemos si los navegadores de 
* telefono soportan put 
* 
*/

$app->get('/vendedores', 'getVendedores');
$app->get('/clientes', 'getClientes');
$app->get('/cliente/cobros/:id','getCobrosClientes');
$app->post('/cliente/cobro', 'updateCobroCliente');
//$app->get('/vendedor', 'getVendedorPass');
//$app->post('/cobro/:id','updateCobro');
//$app->put('/oferta/:id','updateOferta');
//$app->delete('/oferta/:id',   'deleteOferta');





 
$app->run();

/**
* Funcion para Loguear en un archivo las acciones del Programa
*
*  Sirve para loguear de manera sencilla lo que va sucendiendo
*  durante la ejecucuin del programa.
* @author SantiagoValdez
* @author santiagovaldez@groupweird.com
* 
*/
function loguear($msg){
    $filename = "log.log";
    // abrimos el archivo
    $fd = fopen($filename, "a");
    // agregamos date/time al mensaje
    $str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg; 
    // escribismo
    fwrite($fd, $str . "\n");
    // cerramos and... PROFIT!
    fclose($fd);

}
 
/**
* Obtiene un puntero a una conexion a BD
*
* Conecta a la BD, devuelve un puntero 
* 
*/

function getConnection() {
    $dbhost="127.0.0.1";
    //$dbuser="root"; 
    $dbuser="groupwe"; 
    //$dbpass="pepe";
    $dbpass="kaiser09";
    //$dbname="anamaria";
    $dbname="groupwe_anamaria";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
} 

/**
* Obtiene una lista de los Vendedores
*
* Esta lista se retorna en formato json, 
* contiene el nombre y apellido del vendedor,
* junto con su id. 
* Caso de error, se retorna un json con la
* descripcion del error
* 
*/

function getVendedores() {
    $sql = "select id,nombre,apellido,password FROM vendedor ORDER BY apellido";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $vendedores = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo '{"Vendedores": ' . json_encode($vendedores) . '}';
        loguear("Se envia vendedores : [" . json_encode($vendedores) . "]" );
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        loguear("ERROR : [". $e->getMessage(). "]");
    }
}

/**
 * Obtener Lista de Clientes Deudores
 *
 * Se obtiene una lista de clientes con 
 * saldo mayor a 0, y se retorna en formato json
 * 
 */
  
function getClientes() {
    $sql = "SELECT id,nombre,apellido,saldo FROM cliente WHERE saldo > 0";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $clientes = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo '{"Clientes": ' . json_encode($clientes) . '}';
        loguear("Se envia clientes : [" . json_encode($clientes) . "]" );
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        loguear("ERROR : [". $e->getMessage(). "]");
    }

}

/**
* Obtiene la lista de Cobros de un Cliente
*
* Retorna en formato json una lista de todos los cobros, mando 
* monto por si se cambia esa parte. Asi no es tan jodido 
*
* 
*/

function getCobrosClientes($id){
    $sql = "SELECT id, monto, fecha FROM  cobro WHERE cliente=:id AND pendiente=1 ORDER BY fecha";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $cobros = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo '{"Cobros": ' . json_encode($cobros) . '}';
        loguear("Se envia cobros : [" . json_encode($cobros) . "]" );
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        loguear("ERROR : [". $e->getMessage(). "]");
    }
}

/**
* Se encarga de persistir un cobro
*
* Persiste el cobro que se realizo al cliente, actualizando tabla cobro 
* y saldo cliente 
* 
*/

 
function updateCobroCliente(){
    $request = \Slim\Slim::getInstance()->request();
    $cobros = json_decode($request->getBody());
    echo $cobros->idCliente;
} 


/*************************************************************************
**************************************************************************
**************************************************************************
**************************************************************************/
function addOferta() {
    $request = \Slim\Slim::getInstance()->request();
    $Oferta = json_decode($request->getBody());
    $sql = "INSERT INTO Oferta (nombre, descripcion) VALUES (:nombre, :descripcion)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("nombre", $Oferta->nombre);
        $stmt->bindParam("descripcion", $Oferta->descripcion);
        $stmt->execute();
        $Oferta->id = $db->lastInsertId();
        $db = null;
        echo json_encode($Oferta);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
 
function updateOferta($id) {
    $request = \Slim\Slim::getInstance()->request();
    $body = $request->getBody();
    $Oferta = json_decode($body);
    $sql = "UPDATE Oferta SET nombre=:nombre, descripcion=:descripcion WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("nombre", $Oferta->nombre);
        $stmt->bindParam("descripcion", $Oferta->descripcion);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo json_encode($Oferta);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
 
function deleteOferta($id) {
    $sql = "DELETE FROM Oferta WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
 
function findByName($query) {
    $sql = "SELECT * FROM Oferta WHERE UPPER(nombre) LIKE :query ORDER BY nombre";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $query = "%".$query."%";
        $stmt->bindParam("query", $query);
        $stmt->execute();
        $oferta = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo '{"Oferta": ' . json_encode($oferta) . '}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}
 

/*
*   Grupo de funciones para el manejo
*   de :        OFERTAS
*/

    //Todas las ofertas
    function getOffert() {
        $sql = "select * FROM offert ORDER BY date";
        try {
            $db = getConnection();
            $stmt = $db->query($sql);
            $oferta = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            echo '{"Offert": ' . json_encode($oferta) . '}';
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    //Una oferta especifica
    function getOffertById($id) {
        $sql = "SELECT * FROM offert WHERE idOffert=:id";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindParam("id", $id);
            $stmt->execute();
            $Offert = $stmt->fetchObject();
            $db = null;
            echo json_encode($Offert);
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    //Las ofertas de un usuario
    function getOffertByUser($id){
        $sql = "SELECT * FROM offert WHERE userId=:id LIMIT 0, 30";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindParam("id", $id);
            $stmt->execute();
            $oferta = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            echo '{"Offert": ' . json_encode($oferta) . '}';
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    //Las ofertas de un negocio
    function getOffertBySeller($id){
        $sql = "SELECT * FROM offert WHERE sellerId=:id LIMIT 0, 30";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindParam("id", $id);
            $stmt->execute();
            $oferta = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            echo '{"Offert": ' . json_encode($oferta) . '}';
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    //Las ofertas de una categoria
    function getOffertByCategory($id){
        $sql = "SELECT * FROM offert WHERE categoryId=:id LIMIT 0, 30";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindParam("id", $id);
            $stmt->execute();
            $oferta = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            echo '{"Offert": ' . json_encode($oferta) . '}';
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    // Agregar Oferta
    function addOffert() {
        $request = \Slim\Slim::getInstance()->request();
        $Oferta = json_decode($request->getBody());

        $sql = "INSERT INTO offert (offertName, offertDescription, date, rating, ratingsCount, photo, price, currency, sellerId, categoryId, userId) VALUES ( :offertName, :offertDescription, :date, :rating, :ratingsCount, :photo, :price, :currency, :sellerId, :categoryId, :userId);";

        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            
            $stmt->bindParam("offertName", $Oferta->offertName);
            $stmt->bindParam("offertDescription", $Oferta->offertDescription);
            $stmt->bindParam("date",$Oferta->date);
            $stmt->bindParam("rating",$Oferta->rating);
            $stmt->bindParam("ratingsCount",$Oferta->ratingsCount);
            $stmt->bindParam("photo",$Oferta->photo);
            $stmt->bindParam("price",$Oferta->price);
            $stmt->bindParam("currency",$Oferta->currency);
            $stmt->bindParam("sellerId",$Oferta->sellerId);
            $stmt->bindParam("categoryId",$Oferta->categoryId);
            $stmt->bindParam("userId",$Oferta->userId);
            
            $stmt->execute();
            $Oferta->id = $db->lastInsertId();
            $db = null;
            echo json_encode($Oferta);
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    // Actualiza Oferta
    function updateOffert($id) {
        $request = \Slim\Slim::getInstance()->request();
        $body = $request->getBody();
        $Oferta = json_decode($body);
        $sql = "UPDATE offert SET offertName=:offertName, offertDescription=:offertDescription, date=:date, rating=:rating, ratingsCount=:ratingsCount, photo=:photo, price=:price, currency=:currency, sellerId=:sellerId, categoryId=:categoryId, userId=:userId WHERE idOffert=:id";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            
            $stmt->bindParam("offertName", $Oferta->offertName);
            $stmt->bindParam("offertDescription", $Oferta->offertDescription);
            $stmt->bindParam("id", $id);
            
            $stmt->execute();
            $db = null;
            echo json_encode($Oferta);
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    // Borrar Oferta
    function deleteOffert($id) {
        $sql = "DELETE FROM offert WHERE idOffert=:id";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindParam("id", $id);
            $stmt->execute();
            $db = null;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    // >>>>>>>> FIN OFERTA <<<<<<<<<

/*
*   Grupo de funciones para el manejo
*   de :        NEGOCIO
*/

    function getSeller() {
        $sql = "select * FROM seller ORDER BY sellerName";
        try {
            $db = getConnection();
            $stmt = $db->query($sql);
            $seller = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            echo '{"Seller": ' . json_encode($seller) . '}';
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }
     
    function getSellerById($id) {
        $sql = "SELECT * FROM seller WHERE idSeller=:id";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindParam("id", $id);
            $stmt->execute();
            $Seller = $stmt->fetchObject();
            $db = null;
            echo json_encode($Seller);
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }
     
    function addSeller() {
        $request = \Slim\Slim::getInstance()->request();
        $Seller = json_decode($request->getBody());
        $sql = "INSERT INTO seller (sellerName, address, latitude, longitude, photo) VALUES (:sellerName, :address, :latitude, :longitude, :photo)";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindParam("sellerName", $Seller->sellerName);
            $stmt->bindParam("address", $Seller->address);
            $stmt->bindParam("latitude", $Seller->latitude);
            $stmt->bindParam("longitude", $Seller->longitude);
            $stmt->bindParam("photo", $Seller->photo);
            $stmt->execute();
            $Seller->id = $db->lastInsertId();
            $db = null;
            echo json_encode($Seller);
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }
     
    function updateSeller($id) {
        $request = \Slim\Slim::getInstance()->request();
        $body = $request->getBody();
        $Seller = json_decode($body);
        $sql = "UPDATE seller SET sellerName, address, latitude, longitude, photo WHERE idSeller=:id";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindParam("sellerName", $Seller->sellerName);
            $stmt->bindParam("address", $Seller->address);
            $stmt->bindParam("latitude", $Seller->latitude);
            $stmt->bindParam("longitude",$Seller->longitude);
            $stmt->bindParam("photo",$Seller->photo);
            $stmt->bindParam("id", $id);
            $stmt->execute();
            $db = null;
            echo json_encode($Seller);
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }
     
    function deleteSeller($id) {
        $sql = "DELETE FROM seller WHERE idSeller=:id";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindParam("id", $id);
            $stmt->execute();
            $db = null;
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    function getSellerByLatLon($lat,$lon){
        $sql = "select *, ( 6371 * acos( cos( radians( :latitude ) ) * cos( radians( latitude ) ) * cos( radians( longitude )- radians( :longitude ) ) + sin( radians( :latitude ) ) * sin( radians( latitude ) ) ) ) AS distance FROM seller HAVING distance <= 1 ORDER BY distance ASC";

        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindParam("latitude", $lat);
            $stmt->bindParam("longitude", $lon);
            $stmt->execute();
            $seller = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;
            echo '{"Seller": ' . json_encode($seller) . '}';
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }
 


?>