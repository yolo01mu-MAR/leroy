<?php
    $menu = obtener_menu_usuario();
?>
<ul>
    <?php foreach ($menu as $nombreModulo => $modulo): ?>
        <?php
            $items = $modulo['items'];
            $icono = $modulo['icono'];
        ?>
        <?php if (count($items) === 1): ?>
            <?php $item = $items[0]; ?>
            <li>
                <a href="<?= BASE_URL ?>/<?= htmlspecialchars($item['ruta']) ?>">
                    <i class="sidebar-icon <?= htmlspecialchars($icono) ?>"></i>
                    <span>
                        <?= htmlspecialchars($item['nombre']) ?>
                    </span>
                </a>
            </li>
        <?php else: ?>
            <li>
                <a href="#" class="submenu-toggle">
                    <i class="sidebar-icon <?= htmlspecialchars($icono) ?>"></i>
                    <span>
                        <?= htmlspecialchars($nombreModulo) ?>
                    </span>
                </a>
                <ul class="nav submenu">
                    <?php foreach ($items as $item): ?>
                        <li>
                            <a href="<?= BASE_URL ?>/<?= htmlspecialchars($item['ruta']) ?>">
                                <?= htmlspecialchars($item['nombre']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>