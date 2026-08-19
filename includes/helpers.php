<?php
/**
 * Formatea una fecha en español (México)
 * @param string $fecha (Y-m-d)
 * @param string $formato (ej: 'dd MMMM yyyy')
 * @return string
 */
    setlocale(LC_TIME, 'es_MX.UTF-8', 'es_ES.UTF-8', 'spanish');

    function obtenerSemanaISO($anio = null, $semana = null) {

        if ($anio === null || $semana === null) {
            $dto = new DateTime();
            $semana = $dto->format('W');
            $anio   = $dto->format('o');
        }

        $dto = new DateTime();
        $dto->setISODate($anio, $semana);

        $lunes = $dto->format('Y-m-d');
        $domingo = date('Y-m-d', strtotime("$lunes +6 days"));

        return [
            'semana'   => $semana,
            'anio'     => $anio,
            'lunes'    => $lunes,
            'domingo'  => $domingo
        ];
    }
    function resumen_filtros_asistencia() {
        
        $filtros = [];

        if (!empty($_GET['grupo'])) {
            $grupo = find_by_id('grupos', (int)$_GET['grupo']);
            $filtros[] = 'Grupo: <strong>' . remove_junk($grupo['nombre']) . '</strong>';
        }

        if (!empty($_GET['nave'])) {
            $nave = find_by_id('naves', (int)$_GET['nave']);
            $filtros[] = 'Nave: <strong>' . remove_junk($nave['nombre']) . '</strong>';
        }

        if (!empty($_GET['departamento'])) {
            $departamento = find_by_id('departamento', (int)$_GET['departamento']);
            $filtros[] = 'Departamento: <strong>' . remove_junk($departamento['zona']) . '</strong>';
        }

        if (!empty($_GET['depPlantilla'])) {
            $depPlantilla = find_by_id('departamento_plantilla', (int)$_GET['depPlantilla']);
            $filtros[] = 'Zona: <strong>' . remove_junk($depPlantilla['nombre']) . '</strong>';
        }

        if (!empty($_GET['jefe'])) {
            $jefe = find_by_id('users', (int)$_GET['jefe']);
            $filtros[] = 'Jefe de cuadrilla - <strong>' . remove_junk($jefe['name']) . '</strong>';
        }

        if (empty($filtros)) {
            return '';
        }

        return implode(' · ', $filtros);
    }
    function fecha_es($fecha, $formato = 'dd MMMM yyyy') {
        if (empty($fecha)) {
            return '';
        }

        $date = new DateTime($fecha);

        $formatter = new IntlDateFormatter(
            'es_MX',
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            null,
            null,
            $formato
        );

        return $formatter->format($date);
    }

?>