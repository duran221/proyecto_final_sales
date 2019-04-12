'use strict';

new class DevolucionCompra {

    constructor() {
        this.tablaDevolucionesCompras;

        let elems = document.querySelectorAll('.datepicker');
        var instances = M.Datepicker.init(elems, {
            format: 'yyyy-mm-dd',
            i18n: util.datePickerES,
            defaultDate: new Date()
        });

        this.url = './controlador/fachada.php'; // la url del controlador de fachada
        this.filasPorPagina = 7;

        $('#devolucion_compra-fecha').value = moment(new Date()).format('YYYY-MM-DD'); // <-- observe uno de los usos que se le puede dar a moment.js
        this.inicializarProveedores();
        
        $('#devolucion_compra-proveedor').addEventListener('change', event => {
            this.crearListaCompras();
        });

        $('#devolucion_compra-compra').addEventListener('change', event => {
            this.crearDetalleCompra(this.calcularLineaCompra);
        });

        $('#devolucion_compra-registrar').addEventListener('click', event => {
            this.registrarDevolucion();
        });

     
        
    }

    /**
     * Intenta recuperar la lista de proveedores y si es posible, continúa intentando recuperar el siguiente
     * número de factura. Si también lo logra ejecuta crearListaProductos, para que continúe el proceso
     * de inicialización de la facturación
     */
    inicializarProveedores() {
        util.cargarLista({ // llenar los elementos de la lista desplegable de proveedores
            clase: 'Proveedor',
            accion: 'listar',
            listaSeleccionable: '#devolucion_compra-proveedor',
            clave: 'id_proveedor',
            valor: 'nombre',
            primerItem: 'Seleccione un proveedor'
        }).then(() => {
            $('#devolucion_compra-proveedor').value = '';
            M.FormSelect.init($('#devolucion_compra-proveedor'));
            let tab='devoluciones_compras';
            let colum='id_devolucion_compra'
            util.siguiente(tab,colum).then(data => {
                if(data.ok){
                    $('#devolucion_compra-id').value =data.colum;
                    M.updateTextFields();

                }else{
                    throw new Error(data.mensaje);

                }
               
            });
        }).catch(error => {
            util.mensaje(error);
        });
    }

    crearListaCompras() {
        let datos = { 
            id_proveedor:$('#devolucion_compra-proveedor').value,
        };
        util.cargarLista({ // llenar los elementos de la lista desplegable de proveedors
            clase: 'Compra',
            accion: 'listar',
            listaSeleccionable: '#devolucion_compra-compra',
            clave: 'id_compra',
            valor: 'fecha_compra',
            primerItem: 'Seleccione una Compra',
            info: datos
        }).then(() => {
            $('#devolucion_compra-compra').value = '';
            M.FormSelect.init($('#devolucion_compra-compra'));

        }).catch(error => {
            util.mensaje(error);
        });



        

    }

    /**
     * Configura una tabla en donde es posible agregar o eliminar líneas o detalles de compra.
     * @param {Object} productos Un objeto que contiene dos listas de productos, una con todos los 
     * datos de cada producto y otra con los datos básicos para visualizar en un campo de autocompletar.
     */
    crearDetalleCompra(_calcularLineaCompra) {
        this.tablaDevoluciones = new Tabulator("#tabla-devoluciones-compras", {
            height: "200px",
            ajaxURL: util.URL,
            ajaxParams: { // parámetros que se envían al servidor para mostrar la tabla
                clase: 'Compra',
                accion: 'listarDetalleCompra',
               compra: $('#devolucion_compra-compra').value
            },
            ajaxConfig: 'POST', // tipo de solicitud HTTP ajax
            ajaxContentType: 'json', // enviar parámetros al servidor como una cadena JSON
            layout: 'fitColumns', // ajustar columnas al ancho de la tabla
            responsiveLayout: 'hide', // ocultar columnas que no caben en el espacio de la trabajo tabla
            tooltips: true, // mostrar mensajes sobre las celdas.
            addRowPos: 'top', // al agregar una nueva fila, agréguela en la parte superior de la tabla
            history: true, // permite deshacer y rehacer acciones sobre la tabla.
            pagination: 'local', // cómo paginar los datos
            paginationSize: 10,
            movableColumns: true, // permitir cambiar el orden de las columnas
            resizableRows: true, // permitir cambiar el orden de las filas
            columns: [{
                    title: "Comprado",
                    field: "cantidad_recibida",
                    width: 150,
                    align: "right"
                },
                {
                    title: "Devuelto",
                    field: "cantidad_devuelta",
                    width: 150,
                    editor: "number",
                    editorParams: {
                        min: 1,
                        max: 1000,
                    },
                    align: "right",
                },
                {title: "Descripción De Productos",field: "nombre"},
                {title: "Id Producto",field: "id_producto"},
                { title: "Vr. Unitario", field: "precio", width: 150, align: "right" },
                { title: "Subtotal", field: "subtotal", width: 150, align: "right"}
            ]
        });

    }

    /**
     * Se envían los datos del front-end al back-end para ser guardados en la base de datos
     */
    registrarDevolucion() {
        // ensayar los tiempos de espera en servidores lentos para ver si esto es necesario:
        //      $('#compra-registrar').disabled = true;
        //      $('#compra-cancelar').disabled = true;

        let errores =this.validarDatos();
        if (errores) { // la compra no se registra si los datos están incompletos
            M.toast({ html: `No se puede registrar la devolución:${errores}` });
            return;
        }

        let devolucion = {
            fecha_devolucion: $('#devolucion_compra-fecha').value,
           compra: $('#devolucion_compra-compra').value,
            detalle: this.tablaDevoluciones.getData()
        };

        util.fetchData(util.URL, {
            'method': 'POST',
            'body': {
                clase: 'DevolucionCompra',
                accion: 'insertar',
                devolucion: devolucion
            }
        }).then(data => {
            // si todo sale bien se retorna el ID de la compra registrada
            if (data.ok) {
                $('#devolucion_compra-id').value = data.id_devolucion_compra + 1;
                M.toast({ html: 'La devolución se registró exitosamente' });
                this.tablaDevoluciones.clearData();
                $('#devolucion_compra-proveedor').value = '';
                $('#devolucion_compra-compra').value = '';
                M.FormSelect.init($('#devolucion_compra-proveedor'));
                M.FormSelect.init($('#devolucion_compra-compra'));

            } else {
                throw new Error(data.mensaje);
            }

        }).catch(error => {
            util.mensaje(error, 'No se pudo determinar el ID de la siguiente devolución');
        });
    }
    /**
     * Si faltan datos en las entradas de la compra, devuelve un string informando de las inconsistencias
     */
    validarDatos() {
        let errores = '';

        if (!moment($('#devolucion_compra-fecha').value).isValid()) {
            errores += '<br>Fecha inválida';
        }



        if (!$('#devolucion_compra-proveedor').value) {
            errores += '<br>Falta seleccionar proveedor';
        }

        if (!$('#devolucion_compra-compra').value) {
            errores += '<br>Falta seleccionar una compra';
        }

        if(this.tablaDevoluciones){
            let lineasDeCompra = this.tablaDevoluciones.getData();
            if (lineasDeCompra.length == 0) {
                errores += '<br>No se hallaron detalles de compra.';
            } else {
                let lineaIncompleta = false;
                lineasDeCompra.forEach((lineaCompra) => {
                    if (!util.esNumero(lineaCompra.subtotal)) {
                        lineaIncompleta = true;
                    }
                });

                if (lineaIncompleta) {
                    errores += '<br>Se encontró al menos un detalle de compra incompleto.';
                }
            }

        }
        

        return errores;
    }

}