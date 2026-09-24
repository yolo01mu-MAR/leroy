<?php

// =====================================================
// BOOTSTRAP PRINCIPAL DE ASISTENCIA LE ROY
// =====================================================

defined('BASE_PATH') || define(
    'BASE_PATH',
    dirname(__DIR__)
);

defined('BASE_URL') || define(
    'BASE_URL',
    '/asistenciaLEROY_NUEVO'
);

// =====================================================
// COMPOSER
// =====================================================

require_once BASE_PATH . '/vendor/autoload.php';

// =====================================================
// CARGAR NÚCLEO ACTUAL
// =====================================================

require_once BASE_PATH . '/includes/load.php';