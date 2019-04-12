<?php


class CategoriaProducto implements Persistible {

    /**
     * Devuelve una cadena JSON que contiene el resultado de seleccionar todas categorias guardadas
     * Se usa PDO. Ver https://diego.com.es/tutorial-de-pdo
     */
    public function seleccionar($param) {
        extract($param);
        $sql = "SELECT * FROM categorias_productos ORDER BY id_categoria_producto";
        // prepara la instrucción SQL para ejecutarla, luego recibir los parámetros de filtrado
        $q = $conexion->pdo->prepare($sql);
        $q->execute();
        $filas = $q->fetchAll(PDO::FETCH_ASSOC); // devuelve un array que contiene todas las filas del conjunto de resultados
        echo json_encode($filas); // las filas resultantes son enviadas en formato JSON al frontend

    }

    //Leer insert returning

    /**
     * Inserta un registro de categorias_productos en la base de datos
     */
    public function insertar($param) {
        extract($param);
        // error_log(print_r($param, TRUE)); // quitar comentario para ver lo que se recibe del front-end

        $sql = "SELECT * FROM insertar_categoria(:descripcion)";
        // Prepara la instrucción SQL para ejecutarla luego de recibir los parámetros de inserción
        $instruccion = $conexion->pdo->prepare($sql);

        if ($instruccion) {
            //Vincular variables a parametros de la consulta
            $instruccion->bindParam(':descripcion', $data['nombre']);


            if ($instruccion->execute()) {
                //si la inserción fue exitosa, recuperar el ID retornado
                $fila= $instruccion->fetch(PDO::FETCH_ASSOC);
                $info=$conexion->errorInfo($instruccion,FALSE);
                // agregar el nuevo ID a la info que se envía al front-end
                $info['id_categoria_producto']=$fila['insertar_categoria'];
                $info['ok']= $fila['insertar_categoria']>0;
                echo json_encode($info);

                
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló en la instrucción de inserción para categorias_productos']);
        }
    }

    /**
     * Inserta un registro de categorias_productos en la base de datos
     */
    public function actualizar($param) {
        extract($param);
        // error_log(print_r($param, TRUE)); // quitar comentario para ver lo que se recibe del front-end

        $sql = "UPDATE categorias_productos SET nombre=:nombre WHERE id_categoria_producto = :id_actual";

        // Prepara la instrucción SQL para ejecutarla luego de recibir los parámetros de inserción
        $instruccion = $conexion->pdo->prepare($sql);

        if ($instruccion) {
            $instruccion->bindParam(':id_actual', $data['id_actual']);
            $instruccion->bindParam(':nombre', $data['nombre']);
           
            if ($instruccion->execute()) {
                echo $conexion->errorInfo($instruccion);
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló en la instrucción de actualización para categorias_productos']);
        }
    }

    /**
     * Elimina un registro con base en su PK
     */
    public function eliminar($param) {
        extract($param);
        // error_log(print_r($param, TRUE)); // quitar comentario para ver lo que se recibe del front-end
        $sql = "DELETE FROM categorias_productos WHERE id_categoria_producto = :id_categoria";
        $instruccion = $conexion->pdo->prepare($sql);

        if ($instruccion) {
            if ($instruccion->execute([":id_categoria" => $id_categoria_producto])) {
                $estado = $conexion->errorInfo($instruccion);
                echo $conexion->errorInfo($instruccion);
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló en la eliminación de categorias']);
        }
    }

    public function listar($param) {
        extract($param);

        $sql = "SELECT * FROM categorias_productos ORDER BY nombre";

        // se ejecuta la instrucción SQL, para obtener el conjunto de resultados (si los hay) como un objeto PDOStatement
        if ($stmt = $conexion->pdo->query($sql, PDO::FETCH_OBJ)) {
            // se obtiene el array de objetos con las posibles filas obtenidas
            $lista = $stmt->fetchAll();
            // si la lista tiene elementos, se envía al frontend, si no, se envía un mensaje de error
            if (count($lista)) {
                echo json_encode(['ok' => TRUE, 'lista' => $lista]);
            } else {
                echo json_encode(['ok' => FALSE, 'mensaje' => 'No existen clientes']);
            }
        } else {
            // si falla la ejecución se comunica del error al frontend
            $conexion->errorInfo(stmt);
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Imposible consultar los clientes']);
        }
    }

}


?>
