<?php

class DevolucionCompra implements Persistible{

    public function seleccionar($param){
        
    }

    public function insertar($param) {
        //Convierte variables locales un array asociativo:
        extract($param);
        error_log(print_r($devolucion, 1));
        
        $sql = "SELECT * FROM insertar_devolucion_compra(:datos_devolucion)";
        $instruccion = $conexion->pdo->prepare($sql);

        if ($instruccion) {
            $datosDevolucion = json_encode($devolucion);
            error_log($datosDevolucion);

            $instruccion->bindParam(':datos_devolucion', $datosDevolucion);

            if ($instruccion->execute()) {
                $fila = $instruccion->fetch(PDO::FETCH_ASSOC); // si la inserción fue exitosa, recuperar el ID retornado
                $info = $conexion->errorInfo($instruccion, FALSE);
                $info['id_devolucion_compra'] = $fila['insertar_devolucion_compra']; // agregar el nuevo ID a la info que se envía al front-end
                //Si el que retorna 'insertar_compra' es mayor a cero, lo asigna
                $info['ok'] = $fila['insertar_devolucion_compra'] > 0;
                //Envia los datos a fronted
                echo json_encode($info);
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló en el registro de la nueva compra']);
        }
    }

    /**
     * Inserta un registro de productos en la base de datos
     */
    public function actualizar($param) {
       
    }

    /**
     * Elimina un registro con base en su PK
     */
    public function eliminar($param) {
       
    }

    /**
     * Devuelve una lista de productos para ser usada en listas seleccionables
     */
    public function listar($param) {
    }

}