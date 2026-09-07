async function confirmarDecisionRH(id, accion) {

    /*
    |--------------------------------------------------------------------------
    | NO APROBAR
    |--------------------------------------------------------------------------
    */

    if (accion === 'rechazar') {

        const resultado = await Swal.fire({

            title: 'No aprobar solicitud',

            width: '600px',

            html: `
                <div style="text-align:left;">

                    <p>
                        Indica el motivo por el cual
                        no se aprueba esta solicitud.
                    </p>

                    <label
                        for="observacionRH"
                        style="font-weight:600;"
                    >
                        Observación
                    </label>

                    <textarea
                        id="observacionRH"
                        class="swal2-textarea"
                        placeholder="Escribe el motivo..."
                        style="
                            width:100%;
                            min-height:120px;
                            margin:10px 0 0 0;
                            resize:vertical;
                        "
                    ></textarea>

                </div>
            `,

            showCancelButton: true,

            confirmButtonText:
                'No aprobar solicitud',

            cancelButtonText:
                'Cancelar',

            confirmButtonColor:
                '#dc3545',

            preConfirm: function () {

                const observacion =
                    document.getElementById(
                        'observacionRH'
                    ).value.trim();

                if (!observacion) {

                    Swal.showValidationMessage(
                        'Debes indicar una observación.'
                    );

                    return false;
                }

                return {
                    observacion: observacion
                };
            }

        });


        if (!resultado.isConfirmed) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | ENVIAR RECHAZO
        |--------------------------------------------------------------------------
        */

        const datos = new FormData();

        datos.append(
            'id',
            id
        );

        datos.append(
            'accion',
            'rechazar'
        );

        datos.append(
            'observacion',
            resultado.value.observacion
        );


        $.ajax({

            url:
                '../solicitudes/ajax/procesar_solicitud_rh.php',

            type:
                'POST',

            data:
                datos,

            processData:
                false,

            contentType:
                false,

            dataType:
                'json',

            success:
                function (respuesta) {

                    if (respuesta.success) {

                        Swal.fire({

                            icon:
                                'success',

                            title:
                                'Solicitud no aprobada',

                            text:
                                respuesta.message,

                            confirmButtonText:
                                'Aceptar'

                        }).then(function () {

                            const filtroActivo =
                                document.querySelector(
                                    '.vacaciones-tab.activo'
                                );

                            if (filtroActivo) {

                                filtroActivo.click();

                            }

                        });

                    } else {

                        Swal.fire({

                            icon:
                                'warning',

                            title:
                                'No se puede realizar la acción',

                            text:
                                respuesta.message ||
                                'No fue posible procesar la solicitud.'

                        });

                    }

                },

            error:
                function (xhr) {

                    console.error(
                        'ERROR AJAX RECHAZO RH:',
                        xhr.status,
                        xhr.responseText
                    );

                    Swal.fire({

                        icon:
                            'error',

                        title:
                            'Error',

                        text:
                            'No fue posible procesar la solicitud.'

                    });

                }

        });

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | APROBAR — FIRMA ELECTRÓNICA DE RH
    |--------------------------------------------------------------------------
    */

    let firma;


    const resultado = await Swal.fire({

        title:
            'Aprobar solicitud',

        width:
            '700px',

        html: `
            <div style="text-align:left;">

                <h4>
                    <strong>Firma de RH</strong>
                </h4>

                <p>
                    Para aprobar esta solicitud debes
                    capturar tu firma.
                </p>

                <canvas
                    id="canvasFirmaRH"
                    style="
                        width:100%;
                        height:250px;
                        border:1px solid #ccc;
                        border-radius:4px;
                        background:#fff;
                        display:block;
                    "
                ></canvas>

            </div>
        `,

        showCancelButton:
            true,

        confirmButtonText:
            'Firmar y aprobar',

        cancelButtonText:
            'Cancelar',

        confirmButtonColor:
            '#198754',


        /*
        |--------------------------------------------------------------------------
        | CREAR FIRMA
        |--------------------------------------------------------------------------
        */

        didOpen:
            function () {

                firma =
                    new FirmaElectronica({

                        canvas:
                            'canvasFirmaRH',

                        modulo:
                            FirmaModulo.VACACIONES,

                        registroId:
                            id,

                        tipo:
                            FirmaTipo.RH,

                        usuarioId:
                            window.usuarioRhId,

                        height:
                            250

                    });

            },


        /*
        |--------------------------------------------------------------------------
        | VALIDAR FIRMA
        |--------------------------------------------------------------------------
        */

        preConfirm:
            function () {

                if (
                    !firma ||
                    !firma.tieneFirma()
                ) {

                    Swal.showValidationMessage(
                        'Debe capturar una firma.'
                    );

                    return false;
                }

                return firma.obtenerDatos();

            }

    });


    if (!resultado.isConfirmed) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | ENVIAR APROBACIÓN
    |--------------------------------------------------------------------------
    */

    const datos = new FormData();

    datos.append(
        'id',
        id
    );

    datos.append(
        'accion',
        'aprobar'
    );

    datos.append(
        'firma',
        resultado.value.firma
    );


    $.ajax({

        url:
            '../solicitudes/ajax/procesar_solicitud_rh.php',

        type:
            'POST',

        data:
            datos,

        processData:
            false,

        contentType:
            false,

        dataType:
            'json',

        success:
            function (respuesta) {

                if (respuesta.success) {

                    Swal.fire({

                        icon:
                            'success',

                        title:
                            'Solicitud aprobada',

                        text:
                            respuesta.message,

                        confirmButtonText:
                            'Aceptar'

                    }).then(function () {

                        const filtroActivo =
                            document.querySelector(
                                '.vacaciones-tab.activo'
                            );

                        if (filtroActivo) {

                            filtroActivo.click();

                        }

                    });

                } else {

                    Swal.fire({

                        icon:
                            'warning',

                        title:
                            'No se puede realizar la acción',

                        text:
                            respuesta.message ||
                            'No fue posible procesar la solicitud.'

                    });

                }

            },

        error:
            function (xhr) {

                console.error(
                    'ERROR AJAX APROBACIÓN RH:',
                    xhr.status,
                    xhr.responseText
                );

                Swal.fire({

                    icon:
                        'error',

                    title:
                        'Error',

                    text:
                        'No fue posible procesar la aprobación.'

                });

            }

    });

}