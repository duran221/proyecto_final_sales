<?php

class Venta implements Persistible {

    /**
     * Devuelve una cadena JSON que contiene el resultado de seleccionar la información básica de ventas
     * Se usa PDO. Ver https://diego.com.es/tutorial-de-pdo
     */
    public function seleccionar($param) {
        extract($param);
        // error_log(print_r($param, TRUE)); // quitar comentario para ver lo que se recibe del front-end

        $sql = "SELECT id_venta, fecha_venta, total_credito, total_contado, id_cliente, id_vendedor
                   FROM ventas
                   WHERE id_cliente = :cliente
                ORDER BY fecha_venta";
        // prepara la instrucción SQL para ejecutarla, luego recibir los parámetros de filtrado
        $q = $conexion->pdo->prepare($sql);
        $q->execute([':cliente' => $cliente]);
        $filas = $q->fetchAll(PDO::FETCH_ASSOC); // devuelve un array que contiene todas las filas del conjunto de resultados
        echo json_encode($filas); // las filas resultantes son enviadas en formato JSON al frontend
    }

    public function insertar($param) {
        //Convierte variables locales un array asociativo:
        extract($param);
        error_log(print_r($venta, 1));
        
        $sql = "SELECT * FROM insertar_venta(:datos_venta)";
        $instruccion = $conexion->pdo->prepare($sql);

        if ($instruccion) {
            $datosVenta = json_encode($venta);
            $instruccion->bindParam(':datos_venta', $datosVenta);

            if ($instruccion->execute()) {
                $fila = $instruccion->fetch(PDO::FETCH_ASSOC); // si la inserción fue exitosa, recuperar el ID retornado
                $info = $conexion->errorInfo($instruccion, FALSE);
                $info['id_venta'] = $fila['insertar_venta']; // agregar el nuevo ID a la info que se envía al front-end
                //Si el que retorna 'insertar_venta' es mayor a cero, lo asigna
                $info['ok'] = $fila['insertar_venta'] > 0;
                //Envia los datos a fronted
                echo json_encode($info);
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Falló en el registro de la nueva venta']);
        }
    }

    public function actualizar($param) {
        throw new Exception("Sin implementar 'actualizar'");
    }

    public function eliminar($param) {
        throw new Exception("Sin implementar 'eliminar'");
    }

    public function listar($param) {
        extract($param);

        $sql = "SELECT id_venta,fecha_venta
                    FROM ventas WHERE id_cliente=:cliente_id
                    ORDER BY fecha_venta";

        // prepara la instrucción SQL para ejecutarla, luego recibir los parámetros de filtrado
        $instruccion = $conexion->pdo->prepare($sql);
        if ($instruccion) {
            $instruccion->bindParam(':cliente_id', $data['id_cliente']);
            
            if ($instruccion->execute()) {
                $info = $conexion->errorInfo($instruccion, FALSE);
                $filas['lista'] = $instruccion->fetchAll(PDO::FETCH_ASSOC); // devuelve un array que contiene todas las filas del conjunto de resultados
                $filas['ok'] =TRUE;
                echo json_encode($filas); // las filas resultantes son enviadas en formato JSON al frontend
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Fallo al determinar el ID del siguiente pago de clientes']);
        }
    }

        /**
     * Devuelve una cadena JSON que contiene el resultado de listar todos los detalles de venta
     * Se usa PDO. Ver https://diego.com.es/tutorial-de-pdo
     */
    public function listarDetalleVenta($param) {
        extract($param);
        
        $sql = "SELECT * FROM lista_detalle_venta WHERE ID_VENTA=:venta";

        $instruccion = $conexion->pdo->prepare($sql);
        if ($instruccion) {
            if ($instruccion->execute([':venta' => $venta])) {
                $info = $conexion->errorInfo($instruccion, FALSE);
                $filas = $instruccion->fetchAll(PDO::FETCH_ASSOC); // devuelve un array que contiene todas las filas del conjunto de resultados
                for ($i = 0; $i < count($filas); $i++) {
                    $filas[$i]['nombre']=$filas[$i]['nombre'] . "-" . $filas[$i]['descripcion'];
                }
                echo json_encode($filas); // las filas resultantes son enviadas en formato JSON al frontend
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Fallo al determinar el ID del siguiente pago de clientes']);
        }
    }



}