        </div>
        <!-- PIE DE PAGINA -->
        <footer class="py-4">
            <div class="container text-center">
                <b>LE ROY</b><br>
                Portal de Recursos Humanos
            </div>
        </footer>
        <div id="relojSesion">
            <i class="bi bi-clock-history"></i>
            <span id="tiempoSesion">05:00</span>
        </div>
        <div class="modal fade"
            id="modalSesion"
            data-bs-backdrop="static"
            data-bs-keyboard="false"
            tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            Sesión por finalizar
                        </h5>
                    </div>
                    <div class="modal-body text-center">
                        <h2 id="contadorSesion">60</h2>
                        <p>Tu sesión finalizará por inactividad.</p>
                        <p>¿Deseas continuar?</p>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-success"
                            onclick="Kiosco.renovarSesion()">
                            Continuar sesión
                        </button>
                        <button
                            type="button"
                            class="btn btn-danger"
                            onclick="Kiosco.cerrarSesion()">
                            Cerrar sesión
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <!-- MODAL VALIDAR CONTRASEÑA -->
        <div class="modal fade" id="modalPassword" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Confirmar identidad
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">
                            Para realizar una solicitud de vacaciones o de Tiempo por tiempo,
                            confirma tu identidad.
                        </p>
                        <label class="form-label">
                            Contraseña
                        </label>
                        <input
                            type="password"
                            id="passwordKiosco"
                            class="form-control form-control-lg"
                            autocomplete="off"
                            placeholder="Ingresa tu contraseña">
                        <div
                            id="errorPassword"
                            class="alert alert-danger mt-3 d-none">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Olvide mi contraseña
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            id="btnValidarPassword"
                            onclick="Kiosco.validarPassword()">
                            Continuar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- LIBRERÍAS GENERALES -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="libs/js/kiosco.js"></script>
        
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                Kiosco.iniciar();
            });
        </script>
        
        <!-- SCRIPTS ESPECÍFICOS DE LA PÁGINA -->
        <?php if (!empty($scripts_kiosco)): ?>
            <?php foreach ($scripts_kiosco as $script): ?>
                <script src="<?= $script ?>"></script>
            <?php endforeach; ?>
        <?php endif; ?>
    </body>
</html>