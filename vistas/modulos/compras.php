<?php

use Controladores\ControladorCompras;
?>
<div class="content-wrapper panel-medio-principal">
    <?php
    if ($_SESSION['perfil'] == 'Vendedor') {
        echo '
        <section class="container-fluid panel-medio">
        <div class="box alert-dangers text-center">
        <div><h3> Área restringida, solo el administrador puede tener acceso</h3></div>
        <div class="img-restringido"></div>
        </div>
        </div>';
    } else {
    ?>
        <div style="padding:5px"></div>
        <section class="container-fluid panel-medio">
            <div class="box rounded">
                <div class="box-header">
                    <i class="fas fa-file-invoice"></i>&nbsp;
                    <h3 class="box-title">Administración de compras</h3>

                    <button class="btn btn-success pull-right btn-radius" onclick="window.location.href='?ruta=nueva-compra'">
                        <i class="fas fa-plus-square"></i> Nueva compra <i class="fa fa-th"></i>
                    </button>
                </div>

                <div class="box-body table-user">
                    <div class="contenedor-busqueda">

                        <div class="input-group-search">
                            <select class="selectpicker show-tick" data-style="btn-select" data-width="70px" id="selectnum" name="selectnum" onchange="loadCompras(1)">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <div class="input-search">
                                <input type="search" class="search" id="searchCompras" name="searchCompras" placeholder="Buscar" onkeyup="loadCompras(1)">
                                <span class="input-group-addo"><i class="fa fa-search"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered dt-responsive tablaVentas tabla-reportes" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:10px;">#</th>
                                    <th>FECHA EMISIÓN</th>
                                    <th>COMPROBANTE</th>
                                    <th>PROVEEDOR</th>
                                    <th>I.G.V.</th>
                                    <th>SUBTOTAL</th>
                                    <th>TOTAL</th>
                                    <th>PDF</th>
                                    <th>ACCIÓN</th>
                                </tr>
                            </thead>
                            <tbody class="body-reporte-compras">
                                <!-- Los datos se cargan vía AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    <?php } ?>
</div>

<!-- Modal IMPRIMIR -->
<div class="modal fade bd-example-modal-lg" id="modalImprimir" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="col-12">
                    <div class="printerhere" width="100%"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="far fa-times-circle fa-lg"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Cargar compras al iniciar la página
        loadCompras(1);
    });

    function loadCompras(page) {
        let search = $("#searchCompras").val();
        let selectnum = $("#selectnum").val();

        let parametros = {
            "action": "ajax",
            "page": page,
            "search": search,
            "selectnum": selectnum,
            "cargarCompras": true
        };

        $.ajax({
            url: 'ajax/compras.ajax.php',
            method: 'POST',
            data: parametros,
            beforeSend: function() {
                $(".body-reporte-compras").html('<tr><td colspan="9" style="text-align:center;"><img src="vistas/img/reload1.svg" width="40px"> Cargando...</td></tr>');
            },
            success: function(data) {
                $(".body-reporte-compras").html(data);
            },
            error: function(xhr, status, error) {
                console.log("Error al cargar compras:", error);
                $(".body-reporte-compras").html('<tr><td colspan="9" style="text-align:center; color:red;">Error al cargar los datos</td></tr>');
            }
        });
    }
</script>