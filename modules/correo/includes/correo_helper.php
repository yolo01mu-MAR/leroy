<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../../vendor/autoload.php';


/**
 * Envía un correo mediante Mailjet SMTP.
 *
 * @param string $destinatario
 * @param string $nombre
 * @param string $asunto
 * @param string $contenido
 *
 * @return array
 */
function enviar_correo($destinatario, $nombre, $asunto, $contenido) {

    $mail = new PHPMailer(true);

    try {

        /*
        |--------------------------------------------------------------------------
        | SMTP MAILJET
        |--------------------------------------------------------------------------
        */

        $mail->isSMTP();
        $mail->Host = 'in-v3.mailjet.com';
        $mail->SMTPAuth = true;
        $mail->Username = MAILJET_API_KEY;
        $mail->Password = MAILJET_SECRET_KEY;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        /*
        |--------------------------------------------------------------------------
        | REMITENTE
        |--------------------------------------------------------------------------
        */

        $mail->setFrom(
            MAILJET_FROM_EMAIL,
            MAILJET_FROM_NAME
        );


        /*
        |--------------------------------------------------------------------------
        | DESTINATARIO
        |--------------------------------------------------------------------------
        */

        $mail->addAddress(
            $destinatario,
            $nombre
        );


        /*
        |--------------------------------------------------------------------------
        | CONTENIDO
        |--------------------------------------------------------------------------
        */

        $mail->isHTML(true);

        $mail->CharSet = 'UTF-8';

        $mail->Subject = $asunto;

        $mail->Body = $contenido;

        $mail->AltBody =
            strip_tags($contenido);


        /*
        |--------------------------------------------------------------------------
        | ENVIAR
        |--------------------------------------------------------------------------
        */

        $mail->send();


        return [
            'ok' => true,
            'mensaje' => 'Correo enviado correctamente.'
        ];


    } catch (Exception $e) {

        return [
            'ok' => false,
            'mensaje' =>
                'No fue posible enviar el correo.',
            'error' =>
                $mail->ErrorInfo
        ];

    }

}
function enviar_correo_a_usuarios(
    $usuarios,
    $asunto,
    $contenido
){

    $resultados = [];

    foreach($usuarios as $usuario){

        $email = trim(
            $usuario['email'] ?? ''
        );

        /*
        |--------------------------------------------------------------------------
        | SIN CORREO
        |--------------------------------------------------------------------------
        */

        if(empty($email)){

            $resultados[] = [
                'usuario_id' => $usuario['id'],
                'ok' => false,
                'mensaje' => 'El usuario no tiene correo registrado.'
            ];

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | ENVIAR CORREO
        |--------------------------------------------------------------------------
        */

        try {

            $resultado = enviar_correo(
                $email,
                $usuario['nombre'],
                $asunto,
                $contenido
            );

            $resultados[] = [
                'usuario_id' => $usuario['id'],
                'ok' => $resultado['ok'] ?? false,
                'mensaje' => $resultado['mensaje'] ?? ''
            ];

        } catch(Throwable $e){

            $resultados[] = [
                'usuario_id' => $usuario['id'],
                'ok' => false,
                'mensaje' => $e->getMessage()
            ];

            error_log(
                'Error enviando correo al usuario ' .
                $usuario['id'] .
                ': ' .
                $e->getMessage()
            );

        }

    }

    return $resultados;
}
/**
 * Genera el contenido HTML para correos de vacaciones.
 *
 * @param array $datos
 * @return string
 * @throws Exception
 */
function generar_correo_vacaciones(array $datos){
    $template =
        __DIR__ .
        '/../templates/vacaciones.php';

    if (!file_exists($template)) {

        throw new Exception(
            'No existe la plantilla de correo de vacaciones.'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | DATOS
    |--------------------------------------------------------------------------
    */

    $titulo =
        $datos['titulo']
        ?? 'Solicitud de vacaciones';

    $nombre_colaborador =
        $datos['nombre_colaborador']
        ?? '';

    $mensaje =
        $datos['mensaje']
        ?? '';

    $fecha_inicio =
        $datos['fecha_inicio']
        ?? '';

    $fecha_fin =
        $datos['fecha_fin']
        ?? '';

    $dias =
        (int)($datos['dias'] ?? 0);

    $estado =
        $datos['estado']
        ?? 'Pendiente';

    $estado_color =
        $datos['estado_color']
        ?? 'azul';

    $url =
        $datos['url']
        ?? '#';

    $texto_boton =
        $datos['texto_boton']
        ?? 'Ver solicitud';


    /*
    |--------------------------------------------------------------------------
    | GENERAR HTML
    |--------------------------------------------------------------------------
    */

    ob_start();

    require $template;

    return ob_get_clean();
}