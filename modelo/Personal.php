<?php

class Personal implements Persistible{

    /**
     * Devuelve una cadena JSON que contiene el resultado de seleccionar todos los personal guardados
     * Se usa PDO. Ver https://diego.com.es/tutorial-de-pdo
     */
    public function seleccionar($param) {
        extract($param);
        $sql = "SELECT * FROM personal ORDER BY nombre DESC";
        // prepara la instrucción SQL para ejecutarla, luego recibir los parámetros de filtrado
        $q = $conexion->pdo->prepare($sql);
        $q->execute();
        $filas = $q->fetchAll(PDO::FETCH_ASSOC); // devuelve un array que contiene todas las filas del conjunto de resultados
        echo json_encode($filas); // las filas resultantes son enviadas en formato JSON al frontend

    }


    /**
     * Inserta un registro de personal en la base de datos
     */
    public function insertar($param) {
        extract($param);

        //Cifrando la contraseña:
        $pass_cifrado= password_hash($data[contrasena],PASSWORD_DEFAULT);

        // error_log(print_r($param, TRUE)); // quitar comentario para ver lo que se recibe del front-end

        $sql = "INSERT INTO personal VALUES (:id_persona, :nombre, :telefono, :direccion, :perfil, :contrasena)";

        // Prepara la instrucción SQL para ejecutarla luego de recibir los parámetros de inserción
        $instruccion = $conexion->pdo->prepare($sql);

        if ($instruccion) {
            //Vincular variables a parametros de la consulta
            $instruccion->bindParam(':id_persona', $data['id_persona']);
            $instruccion->bindParam(':nombre', $data['nombre']);
            $instruccion->bindParam(':telefono', $data['telefono']);
            $instruccion->bindParam(':direccion', $data['direccion']);
            $instruccion->bindParam(':perfil', $data['perfil']);
            $instruccion->bindParam(':contrasena', $pass_cifrado);

            if ($instruccion->execute()) {
                echo $conexion->errorInfo($instruccion);
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló en la instrucción de inserción para personal']);
        }
    }

    /**
     * Actualiza un registro de personal en la base de datos
     */
    public function actualizar($param) {
        extract($param);

        $pass_cifrado= password_hash($data['contrasena'],PASSWORD_DEFAULT);
        // error_log(print_r($param, TRUE)); // quitar comentario para ver lo que se recibe del front-end

        $sql = "UPDATE personal 
                SET id_persona=:id_persona, nombre=:nombre, telefono=:telefono, direccion=:direccion,perfil=:perfil,contrasena=:contrasena
                   WHERE id_persona = :id_actual";

        // Prepara la instrucción SQL para ejecutarla luego de recibir los parámetros de inserción
        $instruccion = $conexion->pdo->prepare($sql);

        if ($instruccion) {
            $instruccion->bindParam(':id_actual', $data['id_actual']);
            $instruccion->bindParam(':id_persona', $data['id_persona']);
            $instruccion->bindParam(':nombre', $data['nombre']);
            $instruccion->bindParam(':direccion', $data['direccion']);
            $instruccion->bindParam(':telefono', $data['telefono']);
            $instruccion->bindParam(':perfil', $data['perfil']);
            $instruccion->bindParam(':contrasena',$pass_cifrado);

            if ($instruccion->execute()) {
                echo $conexion->errorInfo($instruccion);
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló en la instrucción de actualización para personal']);
        }
    }

    /**
     * Elimina un registro con base en su PK
     */
    public function eliminar($param) {
        extract($param);
        // error_log(print_r($param, TRUE)); // quitar comentario para ver lo que se recibe del front-end
        $sql = "DELETE FROM personal WHERE id_persona = :id_persona";
        $instruccion = $conexion->pdo->prepare($sql);

        if ($instruccion) {
            if ($instruccion->execute([":id_persona" => $id_persona])) {
                $estado = $conexion->errorInfo($instruccion);
                echo $conexion->errorInfo($instruccion);
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló en la eliminación de personal']);
        }
    }

    public function listar($param) {
        extract($param);

        $sql = "SELECT * FROM personal ORDER BY nombre";

        // se ejecuta la instrucción SQL, para obtener el conjunto de resultados (si los hay) como un objeto PDOStatement
        if ($stmt = $conexion->pdo->query($sql, PDO::FETCH_OBJ)) {
            // se obtiene el array de objetos con las posibles filas obtenidas
            $lista = $stmt->fetchAll();
            // si la lista tiene elementos, se envía al frontend, si no, se envía un mensaje de error
            if (count($lista)) {
                echo json_encode(['ok' => TRUE, 'lista' => $lista]);
            } else {
                echo json_encode(['ok' => FALSE, 'mensaje' => 'No existe registro de personal']);
            }
        } else {
            // si falla la ejecución se comunica del error al frontend
            $conexion->errorInfo(stmt);
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Imposible consultar el personal']);
        }
    }

    public function autenticar($param) {
        extract($param);

        $sql = "SELECT id_persona, nombre, perfil, contrasena FROM personal
                   WHERE id_persona = :id_persona";
        
        $instruccion = $conexion->pdo->prepare($sql);
        if ($instruccion) {
            $instruccion->bindParam(':id_persona', $idPersona);
            if ($instruccion->execute()) {
                $usuario = $instruccion->fetch(PDO::FETCH_ASSOC);
                if (password_verify($contrasena, $usuario['contrasena'])) {
                    unset($usuario['contrasena']); // no se envía la contraseña al front-end
                    echo json_encode(["ok" => TRUE, "usuario" => $usuario]);
                } else {
                    echo json_encode(["ok" => FALSE, "mensaje" => "Falló la autenticación del usuario"]);
                }
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló el acceso a los datos del usuario']);
        }
    }



}

?>