<?php
  require_once('includes/load.php');
  $reportes_sidebar = obtener_reportes_activos();
?>
<ul>
  <li>
    <a href="#" class="submenu-toggle">
      <i class="sidebar-icon bi bi-people"></i>
       <span>Personal</span>
      </a>
      <ul class="nav submenu">
        <li><a href="usersTeams_asistencia.php">Asistencia</a></li>
        <li><a href="home.php">Personal</a></li>
        <li><a href="userTeam_vacaciones.php">Solicitudes Vacaciones</a></li>
      </ul>
  </li>
  <li>
    <a href="historial_reportes.php" >
      <i class="sidebar-icon bi bi-file-earmark-bar-graph"></i>
      <span>Reportes de Produccion</span>
    </a>
  </li>
  <li>
    <a href="registros_produccion.php">
      <i class="sidebar-icon bi bi-clipboard-data"></i>
      <span>Vista de Produccion</span>
    </a>
  </li>
  <li>
    <a href="registros_paros.php" >
      <i class="sidebar-icon bi bi-exclamation-triangle"></i>
      <span>Vistas Fallas</span>
    </a>
  </li>
  <li>
    <a href="lista_paros.php" >
      <i class="sidebar-icon bi bi-tools"></i>
      <span>Paros</span>
    </a>
  </li>
  <!-- DIVISOR -->
  <li class="menu-divider">
    <span>Reportes Activos</span>
  </li>
  <?php while($rep = $reportes_sidebar->fetch_assoc()): ?>
    <li>
      <a href="edit_reporte_produccion.php?id=<?= $rep['id']; ?>" class="reporte-item">
        <i class="sidebar-icon bi bi-file-earmark-text"></i>
        <?= htmlspecialchars($rep['maquina'] . ' - ' . $rep['fecha']); ?>
      </a>
    </li>
  <?php endwhile; ?>
</ul>
<div class="menu-footer">
  <button class="btn-generar" data-toggle="modal" data-target="#modalReporte">
    + Generar Reporte
  </button>
</div>
