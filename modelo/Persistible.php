<?php

interface Persistible {

    public function insertar($param);

    public function actualizar($param);

    public function eliminar($param);

    public function seleccionar($param);

    public function listar($param);

}

?>