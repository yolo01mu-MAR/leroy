<?php

function badge_estatus($estatus){

    switch($estatus){

        case 'PENDIENTE_JEFE':
            return '<span class="badge bg-warning text-dark">Pendiente Jefe</span>';
        case 'PENDIENTE_RH':
            return '<span class="badge bg-warning text-dark">Pendiente RH</span>';

        case 'APROBADA':
            return '<span class="badge bg-success">Aprobada</span>';

        case 'COMPLETADA':
            return '<span class="badge bg-primary">Completada</span>';

        case 'RECHAZADA_JEFE':
            return '<span class="badge bg-danger">Rechazada Jefe</span>';
        case 'RECHAZADA_RH':
            return '<span class="badge bg-danger">Rechazada RH</span>';

        case 'VENCIDA':
            return '<span class="badge bg-secondary">Vencida</span>';

        default:
            return '<span class="badge bg-light text-dark">'.$estatus.'</span>';
    }

}