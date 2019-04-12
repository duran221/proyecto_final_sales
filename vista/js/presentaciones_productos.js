'use strict';

// se crea un nuevo objeto anónimo a partir de una clase anónima
// dicho objeto define la gestión de presentaciones, utilizando el componente 'Tabulator' (http://tabulator.info/)

new class PresentacionProducto {

    constructor() {

        this.contenedor = '#tabla-presentaciones'; // el div que contendrá la tabla de datos de presentaciones
        this.url = './controlador/fachada.php'; // la url del controlador de fachada
        this.filasPorPagina = 7;

        this.parametros = { // parámetros que se envían al servidor para mostrar la tabla
            clase: 'PresentacionProducto',
            accion: 'seleccionar'
        };

        this.columnas = [ // este array de objetos define las columnas de la tabla
            { // la primera columna incluye los botones para actualizar y eliminar
                title: 'Control',
                headerSort: false,
                width: 150,
                align: "center",
                formatter: (cell, formatterParams) => {
                    // en cada fila, en la primera columna, se asignan los botones de editar y actualizar 
                    return `<i id="tabulator-btnactualizar" class="material-icons teal-text">edit</i>
                            <i id="tabulator-btneliminar" class="material-icons deep-orange-text">delete</i>`;
                },
                cellClick: (e, cell) => {
                    // define qué hacer si se pulsan los botones de actualizar o eliminar
                    this.operacion = e.target.id === 'tabulator-btnactualizar' ? 'actualizar' : 'eliminar';
                    this.filaActual = cell.getRow();
                    if (this.operacion === 'actualizar') {
                        this.editarRegistro();
                    } else if (this.operacion === 'eliminar') {
                        this.eliminarRegistro();
                    }
                }
            },
            { title: 'ID Presentación Producto', field: 'id_presentacion_producto',visible:false},
            { title: 'Descripción', field: 'descripcion', editor: 'input',align:'center' },
        
        ];

        this.ordenInicial = [ // establece el orden inicial de los datos
            { column: 'descripcion', dir: 'desc' }
        ]

        this.indice = 'id_presentacion_producto'; // estable la PK como índice único para cada fila de la tabla visualizada
        this.tabla = this.generarTabla();
        this.filaActual; // guarda el objeto "fila actual" cuando se elige actualizar o eliminar sobre una fila
        this.operacion; // insertar | actualizar | eliminar

        this.frmEdicionPresentacionProducto = M.Modal.init($('#presentaciones-frmedicion'), {
            dismissible: true, // impedir el acceso a la aplicación durante la edición
            onOpenStart: () => {
                // luego miraremos para que sirve esta belleza
            }
        });

        this.gestionarEventos();
    }

    generarTabla() {
        console.log(this.indice);
        return new Tabulator(this.contenedor, {
            ajaxURL: this.url,
            ajaxParams: this.parametros,
            ajaxConfig: 'POST', // tipo de solicitud HTTP ajax
            ajaxContentType: 'json', // enviar parámetros al servidor como una cadena JSON
            layout: 'fitColumns', // ajustar columnas al ancho de la tabla
            responsiveLayout: 'hide', // ocultar columnas que no caben en el espacio de trabajola tabla
            tooltips: true, // mostrar mensajes sobre las celdas.
            addRowPos: 'top', // al agregar una nueva fila, agréguela en la parte superior de la tabla
            history: true, // permite deshacer y rehacer acciones sobre la tabla.
            pagination: 'local', // cómo paginar los datos
            paginationSize: this.filasPorPagina,
            movableColumns: true, // permitir cambiar el orden de las columnas
            resizableRows: true, // permitir cambiar el orden de las filas
            initialSort: this.ordenInicial,
            columns: this.columnas,
            // addRowPos: 'top', // no se usa aquí. Aquí se usa un formulario de edición personalizado
            index: this.indice, // indice único de cada fila
            // locale: true, // se supone que debería utilizar el idioma local
            rowAdded: (row) => this.filaActual = row
        });
    }

    /**
     * Conmuta de verdadero a falso o viceversa, cuando se pulsa clic en una celda que almacena un boolean.
     * Importante: ** no actualiza los cambios en la base de datos **
     * Ver columna 'crédito'
     * @param {*} evento 
     * @param {*} celda 
     */
    conmutar(evento, celda) {
        let valor = !celda.getValue();
        celda.setValue(valor, true);
    }

    /**
     * Se asignan los eventos a los botones principales para la gestión de presentaciones
     */
    gestionarEventos() {
        $('#presentaciones-btnagregar').addEventListener('click', event => {
            this.operacion = 'insertar';
            // despliega el formulario para editar presentaciones. Ir a la definición del boton 
            // 'presentaciones-btnagregar' en presentaciones.html para ver cómo se dispara este evento
        });

        $('#presentaciones-btnaceptar').addEventListener('click', event => {
            // dependiendo de la operación elegida cuando se abre el formulario de
            // edición y luego se pulsa en 'Aceptar', se inserta o actualiza un registro.
            if (this.operacion == 'insertar') {
                this.insertarRegistro();
            } else if (this.operacion == 'actualizar') {
                this.actualizarRegistro();
            }
            this.frmEdicionPresentacionProducto.close();
        });

        $('#presentaciones-btncancelar').addEventListener('click', event => {
            this.frmEdicionPresentacionProducto.close();
        });
    }

    /**
     * Envía un nuevo registro al back-end para ser insertado en la tabla presentaciones
     */
    insertarRegistro() {
        // se creas un objeto con los datos del formulario
        let nuevoPresentacionProducto = {
            descripcion: $('#presentaciones-txtdescripcion').value
        };

        // se envían los datos del nuevo presentaciones al back-end y se nuestra la nueva fila en la tabla
        util.fetchData('./controlador/fachada.php', {
            'method': 'POST',
            'body': {
                clase: 'PresentacionProducto',
                accion: 'insertar',
                data: nuevoPresentacionProducto
            }
        }).then(data => {
            if (data.ok) {
                util.mensaje('', '<i class="material-icons">done</i>', 'teal darken');
                nuevoPresentacionProducto['id_presentacion_producto']=data.id_presentacion_producto;
                this.tabla.addData([nuevoPresentacionProducto]);
                $('#presentaciones-txtid').value = '';
                $('#presentaciones-txtdescripcion').value = '';
            } else {
                throw new Error(data.mensaje);
            }
        }).catch(error => {
            util.mensaje(error, 'No se pudo insertar la presentacion');
        });
    }

    /**
     * despliega el formulario de edición para actualizar el registro de la fila sobre la 
     * que se pulsó el botón actualizar.
     * @param {Row} filaActual Una fila Tabulator con los datos de la fila actual
     */
    editarRegistro() {
        //Abriendo el formulario de materialize
        this.frmEdicionPresentacionProducto.open();
        // se muestran en el formulario los datos de la fila a editar
        let filaActual = this.filaActual.getData();
        $('#presentaciones-txtid').value = filaActual.id_presentacion_producto;
        $('#presentaciones-txtdescripcion').value = filaActual.descripcion;
        M.updateTextFields();
    }

    /**
     * Envía los datos que se han actualizado de una fila actual, al back-end para ser
     * también actualizados en la base de datos.
     */
    actualizarRegistro() {
        // se crea un objeto con los nuevos datos de la fila modificada
        let idPresentacionProductoActual = this.filaActual.getData().id_presentacion_producto;
        let nuevosDatosPresentacionProducto = {
            id_actual: idPresentacionProductoActual,
            id_presentacion_producto: $('#presentaciones-txtid').value, // el posible nuevo ID
            descripcion: $('#presentaciones-txtdescripcion').value,
        };

        // se envían los datos del nuevo presentaciones al back-end y se nuestra la nueva fila en la tabla
        util.fetchData('./controlador/fachada.php', {
            'method': 'POST',
            'body': {
                clase: 'PresentacionProducto',
                accion: 'actualizar',
                data: nuevosDatosPresentacionProducto
            }
        }).then(data => {
            if (data.ok) {
                util.mensaje('', '<i class="material-icons">done</i>', 'teal darken');
                delete nuevosDatosPresentacionProducto.id_actual; // elimina esta propiedad del objeto, ya no se requiere
                this.tabla.updateRow(idPresentacionProductoActual, nuevosDatosPresentacionProducto);
            } else {
                throw new Error(data.mensaje);
            }
        }).catch(error => {
            util.mensaje(error, 'No se pudo insertar la presentacion');
        });

    }

    /**
     * Elimina el registro sobre el cual se pulsa el botón respectivo
     * @param {Row} filaActual Una fila Tabulator con los datos de la fila actual
     */
    eliminarRegistro() {
        let idFila = this.filaActual.getData().id_presentacion_producto;

        MaterialDialog.dialog(
            "Cuidado, Va a eliminar un elemento, Por favor confirme la accion",
            {
                title: "Cuidado",
                buttons:{
                    close:{
                        className:"red",
                        text:"Cancelar",

                    },
                    confirm:{
                        className:"blue",
                        text:"Confirmar",
                        callback: () =>{

                            // se envía el ID del presentaciones al back-end para el eliminado y se actualiza la tabla
                            util.fetchData('./controlador/fachada.php', {
                                'method': 'POST',
                                'body': {
                                    clase: 'PresentacionProducto',
                                    accion: 'eliminar',
                                    id_presentacion_producto: idFila
                                }
                            }).then(data => {
                                if (data.ok) {
                                    this.filaActual.delete();
                                    util.mensaje('', '<i class="material-icons">done</i>', 'teal darken');
                                } else {
                                    throw new Error(data.mensaje);
                                }
                            }).catch(error => {
                                util.mensaje(error, `No se pudo eliminar la presentacion con ID ${idFila}`);
                            });
                            
                        }
                    }
                }
            }

        );

        
    }

}