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
        $opcion = 0;
        extract($param);
        $this->conexion = $conexion;
        // error_log(print_r($param, TRUE)); // quitar comentario para ver lo que se recibe del front-end

        if ($opcion == 1) {
            $this->listarVentas1($cliente);
        } else if ($opcion == 2) {
            $this->listarVentas2();
        }
    }
    
    private function listarVentas1($cliente) {
        $sql = "SELECT id_venta, format('%s - %s', to_char(id_venta, '00000'), fecha_venta) datos_venta
                FROM ventas
                WHERE id_cliente = :cliente";

        $instruccion = $this->conexion->pdo->prepare($sql);

        if ($instruccion) {
            if ($instruccion->execute([':cliente' => $cliente])) {
                $lista = $instruccion->fetchAll(PDO::FETCH_ASSOC); // devuelve un array que contiene todas las filas del conjunto de resultados
                echo json_encode(['ok' => TRUE, 'lista' => $lista]);
            } else {
                echo $this->conexion->errorInfo($instruccion);
            }
        } else {
            // si falla la ejecución se comunica del error al frontend
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Imposible consultar el listado de ventas']);
        }
    }

    private function listarVentas2() {

        $sql = "SELECT CASE WHEN ventas.total_credito  < 30000 THEN 'green'
                            WHEN ventas.total_credito  < 100000 THEN 'amber'
                            ELSE 'red'
                       END estado,
                       ventas.*,
                       ventas.total_credito + ventas.total_contado total,
                       clientes.nombre cliente, clientes.telefonos,
                       personal.nombre vendedor
                FROM ventas
                    JOIN clientes ON ventas.id_cliente = clientes.id_cliente
                    JOIN personal ON ventas.id_vendedor = personal.id_persona
                ORDER BY clientes.nombre, fecha_venta";

        $instruccion = $this->conexion->pdo->prepare($sql);

        if ($instruccion) {
            if ($instruccion->execute()) {
                $lista = $instruccion->fetchAll(PDO::FETCH_ASSOC); // devuelve un array que contiene todas las filas del conjunto de resultados
                echo json_encode($lista);
            }
            // else {
            //     echo $this->conexion->errorInfo($instruccion);
            // }
        } else {
            // si falla la ejecución se comunica del error al frontend
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Imposible consultar el listado de ventas']);
        }
    }

    public function listarDetalleVenta($param) {
        extract($param);
        // error_log(print_r($param, TRUE)); // quitar comentario para ver lo que se recibe del front-end

        // Para los casos en que no haya devoluciones previas, el LEFT JOIN arrojará nulos a la derecha, por lo tanto
        // es necesario tratar lo devuelto con COALESCE que retorna el primero de sus argumentos que no es NULL
        $sql = 'SELECT ldva.*,
                    ldev.total_devuelto,
                    (ldva.vendido - COALESCE(ldev.total_devuelto, 0)) "vendido-devuelto"
                FROM lista_detalles_ventas_agrupadas ldva
                    LEFT JOIN lista_devoluciones_ventas_agrupadas ldev
                        ON ldva.id_venta = ldev.id_venta AND ldva.producto = ldev.id_producto
                WHERE ldva.id_venta = :idVenta';

        $instruccion = $conexion->pdo->prepare($sql);

        if ($instruccion) {
            if ($instruccion->execute([':idVenta' => $idVenta])) {
                $lista = $instruccion->fetchAll(PDO::FETCH_ASSOC); // devuelve un array que contiene todas las filas del conjunto de resultados
                echo json_encode($lista);
            } else {
                echo $conexion->errorInfo($instruccion);
            }
        } else {
            // si falla la ejecución se comunica del error al frontend
            echo json_encode(['ok' => FALSE, 'mensaje' => 'Imposible consultar el listado de detalles de la venta']);
        }
    }





        /**
     * Devuelve una cadena JSON que contiene el resultado de listar todos los detalles de venta
     * Se usa PDO. Ver https://diego.com.es/tutorial-de-pdo
     */
    public function listarDetalleVenta2($param) {
        extract($param);
        
        $sql = "SELECT * FROM lista_detalle_venta WHERE ID_VENTA=:venta";

        $instruccion = $conexion->pdo->prepare($sql);
        if ($instruccion) {
            if ($instruccion->execute([':venta' => $idVenta])) {
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