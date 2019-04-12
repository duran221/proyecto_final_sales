'use strict';

import * as util from './utilidades.js';

window.util = util;

((doc, win) => {

    let menuPrincipal;

    // --- la siguiente asignación de usuario se hará más adelante por autenticación----
    // --- por ahora, debe asignarse uno arbitrariamente, pero existente en la bd.
    util.usuario.id = '001';
    util.usuario.nombre = 'Jorge Pérez';
    util.usuario.perfil = 'Administrador';

    const gestionarClientes = () => {
        // se envía un solicitud de datos al back-end
        util.fetchData('./controlador/fachada.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: {
                clase: 'Cliente', // Nombre de la clase PHP
                accion: 'seleccionar', // Método de la clase
                id_cliente: '', // este y los siguientes datos son simples ejemplos de argumentos adicionales que se envían al back-end
                nombre: '',
                telefonos: '',
                direccion: ''
            }
        }).then(data => {
            // tratamiento dado a la respuesta que llega del back-end
            console.log(data);
            // hacer algo con la información que llega
            // ...
        }).catch(error => {
            util.mensaje(error, 'No se pudo acceder a clientes');
        });
    }

    let opciones = {
        'menu-ir-a-inicio': () => window.location.href = 'index.html',
        'menu-clientes': './vista/html/clientes.html',
        'menu-personal': './vista/html/personal.html',
        'menu-proveedores': './vista/html/proveedores.html',
        'menu-categorias': './vista/html/categorias-productos.html',
        'menu-presentaciones': './vista/html/presentaciones-productos.html',
        'menu-productos': './vista/html/productos.html',
        'menu-bajas': './vista/html/bajas-productos.html',
        'menu-ventas': './vista/html/ventas.html',
        'menu-compras': './vista/html/compras.html',
        'menu-pago-clientes': './vista/html/pagos-clientes.html',
        'menu-devoluciones-compras': './vista/html/devoluciones-compras.html',
        'menu-devoluciones-ventas': './vista/html/devoluciones-ventas.html',
        'menu-btnacercade': './vista/html/acercade.html'
    }
    doc.addEventListener('DOMContentLoaded', event => {

        $('#index-menu').cargar('./vista/html/menu.html', (contenedor) => {
            let elementosMenu = $('#menu-principal');
            menuPrincipal = M.Sidenav.init(elementosMenu);

            elementosMenu.addEventListener('click', (e) => {
                // obtener el enlace y el ID del enlace
                let enlace = e.target;
                let idEnlace = enlace.getAttribute('id');

                // si efectivamente es un enlace y tiene nombre ejecutar algo...
                if (enlace.nodeName.toLowerCase() === 'a' && idEnlace) {
                    util.gestionarOpciones(opciones, idEnlace);
                }
                e.preventDefault();
            }, false);
        });

    });

})(document, window);