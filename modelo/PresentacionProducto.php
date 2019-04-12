<?php

class PresentacionProducto implements Persistible{

    /**
     * Devuelve una cadena JSON que contiene el resultado de seleccionar todos los clientes guardados
     * Se usa PDO. Ver https://diego.com.es/tutorial-de-pdo
     */
    public function seleccionar($param) {
        extract($param);
        $sql = "SELECT * FROM presentaciones_productos ORDER BY descripcion desc";
        // prepara la instrucción SQL para ejecutarla, luego recibir los parámetros de filtrado
        $q = $conexion->pdo->prepare($sql);
        $q->execute();
        $filas = $q->fetchAll(PDO::FETCH_ASSOC); // devuelve un array que contiene todas las filas del conjunto de resultados
        echo json_encode($filas); // las filas resultantes son enviadas en formato JSON al frontend

    }

    //Leer insert returning

    /**
     * Inserta un registro de presentaciones_productos en la base de datos
     */
    public function insertar($param) {
        extract($param);
        // error_log(print_r($param, TRUE)); // quitar comentario para ver lo que se recibe del front-end

        $sql = "SELECT * FROM insertar_presentacion(:descripcion)";

        // Prepara la instrucción SQL para ejecutarla luego de recibir los parámetros de inserción
        $instruccion = $conexion->pdo->prepare($sql);

        if ($instruccion) {

            //Vincular variables a parametros de la consulta
            $instruccion->bindParam(':descripcion', $data['descripcion']);

            if ($instruccion->execute()) {
                //Si la inserción fue exitosa, recuperar el id:
                $fila= $instruccion->fetch(PDO::FETCH_ASSOC);
                $info= $conexion->errorInfo($instruccion,FALSE);
                //rescatando el dato de la fila y asignandolo a el array asociativo $info
                $info['id_presentacion_producto']=$fila['insertar_presentacion'];
                //Preguntando si el id insertado en la columna es mayor a cero:
                $info['ok']=$fila['insertar_presentacion']>0;
                //Enviando los datos al fronted en un json:
                echo json_encode($info);

                
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló en la instrucción de inserción para presentaciones_productos']);
        }
    }

    /**
     * Inserta un registro de presentaciones_productos en la base de datos
     */
    public function actualizar($param) {
        extract($param);
        // error_log(print_r($param, TRUE)); // quitar comentario para ver lo que se recibe del front-end

        $sql = "UPDATE presentaciones_productos SET descripcion=:descripcion WHERE id_presentacion_producto = :id_actual";

        // Prepara la instrucción SQL para ejecutarla luego de recibir los parámetros de inserción
        $instruccion = $conexion->pdo->prepare($sql);

        if ($instruccion) {
            $instruccion->bindParam(':id_actual', $data['id_actual']);
            $instruccion->bindParam(':descripcion', $data['descripcion']);
           
            if ($instruccion->execute()) {
                echo $conexion->errorInfo($instruccion);
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló en la instrucción de actualización para presentaciones_productos']);
        }
    }

    /**
     * Elimina un registro con base en su PK
     */
    public function eliminar($param) {
        extract($param);
        // error_log(print_r($param, TRUE)); // quitar comentario para ver lo que se recibe del front-end
        $sql = "DELETE FROM presentaciones_productos WHERE id_presentacion_producto = :id_presentacion";
        $instruccion = $conexion->pdo->prepare($sql);

        if ($instruccion) {
            if ($instruccion->execute([":id_presentacion" => $id_presentacion_producto])) {
                $estado = $conexion->errorInfo($instruccion);
                echo $conexion->errorInfo($instruccion);
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló en la eliminación de presentaciones']);
        }
    }

    public function listar($param) {
        extract($param);

        $sql = "SELECT * FROM presentaciones_productos ORDER BY descripcion";

        // se ejecuta la instrucción SQL, para obtener el conjunto de resultados (si los hay) como un objeto PDOStatement
        if ($stmt = $conexion->pdo->query($sql, PDO::FETCH_OBJ)) {
            // se obtiene el array de objetos con las posibles filas obtenidas
            $lista = $stmt->fetchAll();
            // si la lista tiene elementos, se envía al frontend, si no, se envía un mensaje de error
            if (count($lista)) {
                echo json_encode(['ok' => TRUE, 'lista' => $lista]);
            } else {
                echo json_encode(['ok' => FALSE, 'mensaje' => 'No existen presentaciones de productos']);
            }
        } else {
            // si falla la ejecución se comunica del error al frontend
            $conexion->errorInfo(stmt);
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Imposible consultar las categorías de productos']);
        }
    }


}

?>