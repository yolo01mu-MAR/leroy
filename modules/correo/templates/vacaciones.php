<?php

/*
|--------------------------------------------------------------------------
| DATOS ESPERADOS
|--------------------------------------------------------------------------
|
| $titulo               -> Ej: "Solicitud de vacaciones"
| $nombre_colaborador   -> Ej: "Juan Pérez"
| $mensaje              -> Ej: "Se ha generado una solicitud..."
| $fecha_inicio
| $fecha_fin
| $dias
| $estado               -> Ej: "Pendiente de revisión"
| $estado_color         -> (opcional) 'azul' | 'verde' | 'rojo' | 'amarillo'
| $url
| $texto_boton          -> (opcional) Ej: "Ver solicitud"
|
|--------------------------------------------------------------------------
*/

// Valores por defecto para no romper si algo no llega

$texto_boton = $texto_boton ?? 'Ver solicitud';

$estado_color = $estado_color ?? 'azul';


// Paleta según el estado (badge + botón)

$paletas = [

    'azul' => [
        'bg'     => '#eff6ff',
        'texto'  => '#2563eb',
        'boton'  => '#2563eb',
    ],

    'verde' => [
        'bg'     => '#ecfdf5',
        'texto'  => '#059669',
        'boton'  => '#059669',
    ],

    'rojo' => [
        'bg'     => '#fef2f2',
        'texto'  => '#dc2626',
        'boton'  => '#dc2626',
    ],

    'amarillo' => [
        'bg'     => '#fffbeb',
        'texto'  => '#d97706',
        'boton'  => '#d97706',
    ],

];

$paleta = $paletas[$estado_color]
    ?? $paletas['azul'];

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars(
            $titulo,
            ENT_QUOTES,
            'UTF-8'
        ); ?>
    </title>

</head>

<body
    style="
        margin:0;
        padding:0;
        background:#eef1f5;
        font-family:Arial, Helvetica, sans-serif;
    "
>

    <table
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="background:#eef1f5;"
    >

        <tr>

            <td
                align="center"
                style="padding:40px 15px;"
            >

                <table
                    width="600"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="
                        max-width:600px;
                        width:100%;
                        background:#ffffff;
                        border-radius:12px;
                        overflow:hidden;
                        box-shadow:0 1px 3px rgba(0,0,0,0.06);
                    "
                >

                    <!-- ACENTO SUPERIOR -->

                    <tr>

                        <td
                            style="
                                height:5px;
                                background:<?= $paleta['boton']; ?>;
                                font-size:0;
                                line-height:0;
                            "
                        >
                            &nbsp;
                        </td>

                    </tr>


                    <!-- ENCABEZADO -->

                    <tr>

                        <td
                            style="
                                padding:28px 32px 20px;
                                text-align:center;
                            "
                        >

                            <div
                                style="
                                    font-size:22px;
                                    font-weight:bold;
                                    color:#111827;
                                    letter-spacing:2px;
                                "
                            >
                                LE ROY
                            </div>

                            <div
                                style="
                                    margin-top:4px;
                                    font-size:12px;
                                    color:#9ca3af;
                                    letter-spacing:0.5px;
                                    text-transform:uppercase;
                                "
                            >
                                Portal LE ROY
                            </div>

                        </td>

                    </tr>


                    <!-- SEPARADOR -->

                    <tr>

                        <td style="padding:0 32px;">

                            <div
                                style="
                                    border-top:1px solid #eef1f5;
                                "
                            >
                                &nbsp;
                            </div>

                        </td>

                    </tr>


                    <!-- CONTENIDO -->

                    <tr>

                        <td
                            style="
                                padding:28px 32px 8px;
                                color:#374151;
                            "
                        >

                            <!-- BADGE DE ESTADO -->

                            <table
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                            >

                                <tr>

                                    <td
                                        style="
                                            background:<?= $paleta['bg']; ?>;
                                            border-radius:20px;
                                            padding:6px 14px;
                                            font-size:12px;
                                            font-weight:bold;
                                            color:<?= $paleta['texto']; ?>;
                                            text-transform:uppercase;
                                        "
                                    >
                                        <?= htmlspecialchars(
                                            $estado,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                </tr>

                            </table>


                            <h1
                                style="
                                    margin:16px 0 12px;
                                    font-size:21px;
                                    line-height:1.3;
                                    color:#111827;
                                "
                            >
                                <?= htmlspecialchars(
                                    $titulo,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </h1>


                            <p
                                style="
                                    margin:0 0 24px;
                                    font-size:15px;
                                    line-height:1.6;
                                    color:#4b5563;
                                "
                            >
                                <?= htmlspecialchars(
                                    $mensaje,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </p>


                            <!-- INFORMACIÓN -->

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    background:#f9fafb;
                                    border-radius:10px;
                                    margin-bottom:28px;
                                "
                            >

                                <tr>

                                    <td
                                        style="
                                            padding:16px 18px;
                                            font-size:13px;
                                            color:#9ca3af;
                                        "
                                    >
                                        Colaborador
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding:16px 18px;
                                            font-size:14px;
                                            color:#111827;
                                            font-weight:bold;
                                        "
                                    >
                                        <?= htmlspecialchars(
                                            $nombre_colaborador,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                </tr>


                                <tr>

                                    <td
                                        style="
                                            padding:16px 18px;
                                            font-size:13px;
                                            color:#9ca3af;
                                            border-top:1px solid #eef1f5;
                                        "
                                    >
                                        Periodo
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding:16px 18px;
                                            font-size:14px;
                                            color:#111827;
                                            font-weight:bold;
                                            border-top:1px solid #eef1f5;
                                        "
                                    >
                                        <?= htmlspecialchars(
                                            $fecha_inicio,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                        &nbsp;–&nbsp;
                                        <?= htmlspecialchars(
                                            $fecha_fin,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </td>

                                </tr>


                                <tr>

                                    <td
                                        style="
                                            padding:16px 18px;
                                            font-size:13px;
                                            color:#9ca3af;
                                            border-top:1px solid #eef1f5;
                                        "
                                    >
                                        Días solicitados
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding:16px 18px;
                                            font-size:14px;
                                            color:#111827;
                                            font-weight:bold;
                                            border-top:1px solid #eef1f5;
                                        "
                                    >
                                        <?= (int)$dias; ?>
                                        <?= (int)$dias === 1 ? 'día' : 'días'; ?>
                                    </td>

                                </tr>

                            </table>


                            <!-- BOTÓN -->

                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                            >

                                <tr>

                                    <td align="center" style="padding-bottom:8px;">

                                        <a
                                            href="<?= htmlspecialchars(
                                                $url,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>"
                                            style="
                                                display:inline-block;
                                                padding:14px 32px;
                                                background:<?= $paleta['boton']; ?>;
                                                color:#ffffff;
                                                text-decoration:none;
                                                font-size:14px;
                                                font-weight:bold;
                                                border-radius:8px;
                                            "
                                        >
                                            <?= htmlspecialchars(
                                                $texto_boton,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>
                                        </a>

                                    </td>

                                </tr>

                            </table>


                            <p
                                style="
                                    margin:20px 0 0;
                                    font-size:12px;
                                    line-height:1.5;
                                    color:#9ca3af;
                                    text-align:center;
                                "
                            >
                                Ingresa al Portal LE ROY para
                                dar seguimiento a esta solicitud.
                            </p>

                        </td>

                    </tr>


                    <!-- PIE -->

                    <tr>

                        <td
                            style="
                                padding:20px 32px;
                                background:#f9fafb;
                                text-align:center;
                            "
                        >

                            <span
                                style="
                                    font-size:12px;
                                    color:#9ca3af;
                                "
                            >
                                Este correo fue generado
                                automáticamente por
                                Portal LE ROY.
                            </span>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

</body>

</html>