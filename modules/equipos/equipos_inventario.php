<?php
  $page_title = 'Inventario equipos';

  $scripts = [
      'equipo_inventario'
  ];

  require_once __DIR__ . '/../../app/bootstrap.php';

  page_require_level(1);

  /*
   * CONFIGURACIÓN
   */
  $limite = 10;

  /*
   * OBTENER TODOS LOS EQUIPOS
   *
   * Por ahora no paginamos aquí.
   * Primero necesitamos agrupar los equipos por tipo.
   */
  $equipos = find_view_equipos();

  /*
   * AGRUPAR POR TIPO
   */
  $estructura = [];

  foreach($equipos as $equipo){

    $tipo = $equipo['tipo'];

    $estructura[$tipo][] = $equipo;
  }

  /*
   * TOTAL GENERAL
   */
  $total_registros = count($equipos);
?>

<?php include_once BASE_PATH . '/layouts/header.php'; ?>
<div class="row">

  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>

</div>

<div class="row">

  <div class="col-md-12">

    <div class="panel panel-default">

      <!-- HEADER -->
      <div class="panel-heading clearfix">

        <!-- IZQUIERDA -->
        <div class="pull-left">

          <div style="
            font-size:18px;
            font-weight:600;
            color:#333;
            margin-bottom:6px;
          ">

            <span class="glyphicon glyphicon-hdd"></span>

            Inventario de Equipos

          </div>

          <div>

            <span class="label label-primary">
              <?php echo $total_registros; ?> equipos
            </span>

            <?php foreach($estructura as $tipo => $lista): ?>

              <span class="label label-default">

                <?php echo remove_junk($tipo); ?>

                <span class="badge">
                  <?php echo count($lista); ?>
                </span>

              </span>

            <?php endforeach; ?>

          </div>

        </div>

        <!-- DERECHA -->
        <div class="pull-right" style="width:320px; margin-top:10px;">

          <div class="input-group input-group-sm">

            <span class="input-group-addon">
              <span class="glyphicon glyphicon-search"></span>
            </span>

            <input type="text"
                  id="buscador"
                  class="form-control"
                  placeholder="Buscar código, marca o modelo">

            <span class="input-group-btn">

              <a href="add_formulario_equipo.php"
                class="btn btn-roy">

                Agregar

              </a>

            </span>

          </div>

        </div>

      </div>

      <!-- BODY -->
      <div class="panel-body">

        <!-- TABS -->
        <ul class="nav nav-tabs" role="tablist">

          <?php
            $i = 0;

            foreach($estructura as $tipo => $lista):
          ?>

            <li role="presentation"
                class="<?php echo ($i == 0) ? 'active' : ''; ?>">

              <a href="#tab_<?php echo $i; ?>"
                aria-controls="tab_<?php echo $i; ?>"
                role="tab"
                data-toggle="tab">

                <?php echo remove_junk($tipo); ?>

                <span class="badge">
                  <?php echo count($lista); ?>
                </span>

              </a>

            </li>

          <?php
            $i++;
            endforeach;
          ?>

        </ul>


        <!-- CONTENIDO -->
        <div class="tab-content" style="margin-top:20px;">

          <?php
            $i = 0;

            foreach($estructura as $tipo => $lista):

              /*
               * TOTAL DE EQUIPOS DE ESTE TIPO
               */
              $total_tipo = count($lista);

              /*
               * PÁGINA INDEPENDIENTE PARA CADA PESTAÑA
               *
               * Ejemplo:
               * ?page_0=2
               * ?page_1=3
               * ?page_2=1
               */
              $pagina_tipo = isset($_GET['page_'.$i])
                  ? (int)$_GET['page_'.$i]
                  : 1;

              if($pagina_tipo < 1){
                $pagina_tipo = 1;
              }

              /*
               * TOTAL DE PÁGINAS DE ESTE TIPO
               */
              $total_paginas_tipo = ceil($total_tipo / $limite);

              /*
               * EVITAR PÁGINAS QUE NO EXISTEN
               */
              if(
                $pagina_tipo > $total_paginas_tipo
                && $total_paginas_tipo > 0
              ){
                $pagina_tipo = $total_paginas_tipo;
              }

              /*
               * OFFSET DE ESTA PESTAÑA
               */
              $offset_tipo = ($pagina_tipo - 1) * $limite;

              /*
               * OBTENER SOLAMENTE LOS 10 REGISTROS
               * CORRESPONDIENTES A ESTA PÁGINA
               */
              $lista_paginada = array_slice(
                $lista,
                $offset_tipo,
                $limite
              );
          ?>

            <div role="tabpanel"
                class="tab-pane fade <?php echo ($i == 0) ? 'in active' : ''; ?>"
                id="tab_<?php echo $i; ?>">

              <div class="table-responsive">

                <table class="table table-bordered table-striped table-hover">

                  <thead>

                    <tr>

                      <th class="text-center">#</th>

                      <th class="text-center">
                        Codigo
                      </th>

                      <th class="text-center">
                        Marca
                      </th>

                      <th class="text-center">
                        Modelo
                      </th>

                      <th class="text-center">
                        Tipo
                      </th>

                      <th class="text-center">
                        Detalles
                      </th>

                      <th class="text-center">
                        Acciones
                      </th>

                    </tr>

                  </thead>

                  <tbody>

                    <?php if(empty($lista_paginada)): ?>

                      <tr>

                        <td colspan="7"
                            class="text-center text-muted">

                          No hay equipos registrados.

                        </td>

                      </tr>

                    <?php else: ?>

                      <?php foreach($lista_paginada as $e): ?>

                        <tr>

                          <td class="text-center">

                            <?php
                              echo remove_junk($e['id']);
                            ?>

                          </td>


                          <td class="text-center">

                            <?php
                              echo remove_junk(
                                $e['codigo_equipo']
                              );
                            ?>

                          </td>


                          <td class="text-center">

                            <?php
                              echo remove_junk(
                                $e['marca']
                              );
                            ?>

                          </td>


                          <td class="text-center">

                            <?php
                              echo remove_junk(
                                $e['modelo']
                              );
                            ?>

                          </td>


                          <td class="text-center">

                            <?php
                              echo remove_junk(
                                $e['tipo']
                              );
                            ?>

                          </td>


                          <td class="text-center">

                            <a href="#"
                              class="ver-detalles"
                              data-id="<?php echo (int)$e['id']; ?>">

                              Ver

                            </a>

                          </td>


                          <td class="text-center">

                            <div class="btn-group">

                              <a
                                href="edit_user.php?id=<?php echo (int)$e['id']; ?>&return=adminUsers.php"
                                class="btn btn-xs btn-warning"
                                data-toggle="tooltip"
                                title="Editar">

                                <i class="glyphicon glyphicon-pencil"></i>

                              </a>


                              <a
                                href="delete_user.php?id=<?php echo (int)$e['id']; ?>"
                                class="btn btn-xs btn-danger"
                                data-toggle="tooltip"
                                title="Eliminar">

                                <i class="glyphicon glyphicon-remove"></i>

                              </a>

                            </div>

                          </td>

                        </tr>

                      <?php endforeach; ?>

                    <?php endif; ?>

                  </tbody>

                </table>

              </div>


              <!-- INFORMACIÓN Y PAGINACIÓN DE ESTA PESTAÑA -->

              <div class="table-footer clearfix">

                <div class="pull-left text-muted"
                    style="font-size:12px; line-height:30px;">

                  Mostrando
                  <?php echo count($lista_paginada); ?>

                  de
                  <?php echo $total_tipo; ?>

                  registros

                </div>


                <?php if($total_paginas_tipo > 1): ?>

                  <div class="pull-right">

                    <ul class="pagination pagination-sm no-margin">

                      <!-- ANTERIOR -->
                      <li class="<?php
                        echo ($pagina_tipo <= 1)
                          ? 'disabled'
                          : '';
                      ?>">

                        <?php if($pagina_tipo > 1): ?>

                          <a href="?page_<?php echo $i; ?>=<?php echo $pagina_tipo - 1; ?>#tab_<?php echo $i; ?>">

                            &laquo;

                          </a>

                        <?php else: ?>

                          <span>&laquo;</span>

                        <?php endif; ?>

                      </li>


                      <!-- NÚMEROS -->
                      <?php for(
                        $p = 1;
                        $p <= $total_paginas_tipo;
                        $p++
                      ): ?>

                        <li class="<?php
                          echo ($p == $pagina_tipo)
                            ? 'active'
                            : '';
                        ?>">

                          <a href="?page_<?php echo $i; ?>=<?php echo $p; ?>#tab_<?php echo $i; ?>">

                            <?php echo $p; ?>

                          </a>

                        </li>

                      <?php endfor; ?>


                      <!-- SIGUIENTE -->
                      <li class="<?php
                        echo ($pagina_tipo >= $total_paginas_tipo)
                          ? 'disabled'
                          : '';
                      ?>">

                        <?php if(
                          $pagina_tipo < $total_paginas_tipo
                        ): ?>

                          <a href="?page_<?php echo $i; ?>=<?php echo $pagina_tipo + 1; ?>#tab_<?php echo $i; ?>">

                            &raquo;

                          </a>

                        <?php else: ?>

                          <span>&raquo;</span>

                        <?php endif; ?>

                      </li>

                    </ul>

                  </div>

                <?php endif; ?>

              </div>

            </div>

          <?php
            $i++;
            endforeach;
          ?>

        </div>

      </div>

    </div>

  </div>

</div>


<!-- MODAL -->
<div id="modalDetalles" class="modal fade">

  <div class="modal-dialog modal-xl"
      style="width:95%; max-width:1400px;">

    <div class="modal-content">

      <div class="modal-header">

        <button type="button"
                class="close"
                data-dismiss="modal">

          &times;

        </button>

        <h4 class="modal-title">

          Detalles del equipo

        </h4>

      </div>


      <div class="modal-body"
          style="max-height:80vh; overflow-y:auto;">

        <div id="contenido-detalles"></div>

      </div>

    </div>

  </div>

</div>


<?php include_once BASE_PATH . '/layouts/footer.php'; ?>