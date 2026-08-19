<ul>
  <li>
    <a href="#" class="submenu-toggle">
      <i class="sidebar-icon bi bi-people"></i>
      <span>Personal</span>
    </a>
    <ul class="nav submenu">
      <li><a href="allUsers.php">Empleados</a></li>
    </ul>
  </li>
  <li>
    <a href="#" class="submenu-toggle">
      <i class="sidebar-icon bi bi-laptop"></i>
      <span>Equipos</span>
    </a>
    <ul class="nav submenu">
      <li><a href="<?= BASE_URL ?>/modules/equipos/equipos_inventario.php">Inventario Gral</a></li>
      <li><a href="<?= BASE_URL ?>/modules/equipos/add_formulario_equipo.php">Agregar un equipo</a></li>
      <li><a href="<?= BASE_URL ?>/modules/equipos/equipos_asignados.php">Equipos Asignados</a></li>
      <li><a href="">Asignar un equipo</a></li>
    </ul>
  </li>
  <li>
    <a href="#" class="submenu-toggle">
      <i class="sidebar-icon bi bi-tools"></i>
      <span>Soporte</span>
    </a>
    <ul class="nav submenu">
      <li><a href="<?= BASE_URL ?>/modules/tickets/ticket_histo_admin.php">Mis tickets</a></li>
      <li><a href="<?= BASE_URL ?>/modules/tickets/ticket_historial.php">Historial tickets</a></li>
      <li><a href="<?= BASE_URL ?>/modules/tickets/ticket_equipo_historial.php">Historial Reparacion <br>Equipos</a></li>
      <li><a href="<?= BASE_URL ?>/modules/tickets/ticket_reportes.php">Reportes</a></li>
    </ul>
  </li>
  <!-- DIVISOR -->
  <li class="menu-divider">
    <span>Accesos Externos</span>
  </li>
  <li>
    <a href="#" class="submenu-toggle">
      <i class="sidebar-icon bi bi-microsoft"></i>
      <span>Microsoft Account</span>
    </a>
    <ul class="nav submenu">
      <li><a href="https://admin.cloud.microsoft/#/homepage" target="_blank">Admin Cloud</a></li>
      <li><a href="https://entra.microsoft.com/#home" target="_blank">Entra</a></li> 
    </ul>
  </li>
  <li>
    <a href="https://admin.flexcontrol.app/" target="_blank">
      <i class="sidebar-icon bi bi-phone"></i>
      <span>FlexControl</span>
    </a>
  </li>
  <li>
    <a href="https://cloud-la.ruijienetworks.com/sso/login?service=https%3A%2F%2Fcloud-la.ruijienetworks.com%2Fwebproxy%2Fsso%2Fback%3Frewrite%3Doff%26back%3Dhttps%253A%252F%252Fcloud-la.ruijienetworks.com%252Fmacc5%252FadminIntl%252F%2523%252F" target="_blank">
      <i class="sidebar-icon bi bi-router"></i>
      <span>Ruijie Cloud</span>
    </a>
  </li>
</ul>