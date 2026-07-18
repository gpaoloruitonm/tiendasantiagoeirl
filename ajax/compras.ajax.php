<?php
session_start();

require_once "../vendor/autoload.php";
require_once __DIR__ . "../Conect/Conexion.php";

use Controladores\ControladorProveedores;
use Controladores\ControladorProductos;
use Controladores\ControladorCompras;
use Controladores\ControladorSunat;
use Conect\Conexion;

class AjaxCompras
{

    public function ajaxCargarCompras()
    {
        $search = $_POST['search'] ?? '';
        $perPage = (int)($_POST['selectnum'] ?? 10);
        $page = (int)($_POST['page'] ?? 1);
        $offset = ($page - 1) * $perPage;

        // Conectar a la base de datos usando la clase Conexion si existe
        if (class_exists('Conexion')) {
            $pdo = Conexion::conectar();
        } else {
            throw new \Exception('Clase Conexion no encontrada. Asegure que el archivo de conexión está incluido.');
        }

        // Construir WHERE para búsqueda
        $where = "";
        if (!empty($search)) {
            $where = "WHERE t2.nombre LIKE '%$search%' OR t1.serie_correlativo LIKE '%$search%' OR t1.serie LIKE '%$search%' OR t1.correlativo LIKE '%$search%'";
        }

        // Contar total de registros
        $queryCount = "SELECT COUNT(*) AS numrows FROM compra t1 INNER JOIN proveedores t2 ON t1.codproveedor=t2.id $where";
        $totalRegistros = $pdo->query($queryCount);
        $totalRegistros = $totalRegistros ? $totalRegistros->fetch()['numrows'] : 0;
        $totalPages = ($perPage > 0) ? ceil($totalRegistros / $perPage) : 0;

        // Consulta principal
        $query = "SELECT t1.*, t2.nombre as proveedor_nombre, t2.ruc, t2.documento, t2.razon_social 
                  FROM compra t1 
                  INNER JOIN proveedores t2 ON t1.codproveedor=t2.id 
                  $where 
                  ORDER BY t1.id DESC 
                  LIMIT $offset, $perPage";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $registros = $stmt->fetchAll();

        if ($totalRegistros > 0) {
            foreach ($registros as $key => $value) {
                $verproveedor = ($value['tipodoc'] == 6)
                    ? ($value['razon_social'] . ' - ' . $value['ruc'])
                    : ($value['proveedor_nombre'] . ' - ' . $value['documento']);

                $serieCorrelativo = $value['serie_correlativo'];

                echo "<tr>
                    <td>" . ++$key . "</td>
                    <td>" . date_format(date_create($value['fecha_emision']), 'd/m/Y') . "</td>
                    <td>" . htmlspecialchars($serieCorrelativo) . "</td>
                    <td>" . htmlspecialchars($verproveedor) . "</td>
                    <td>" . number_format($value['igv'], 2) . "</td>
                    <td>" . number_format($value['subtotal'], 2) . "</td>
                    <td>" . number_format($value['total'], 2) . "</td>
                    <td style='text-align:center;'>
                        <button class='btn btn-print-compra' idCompra='{$value['id']}' data-toggle='modal' data-target='#modalImprimir'>+</button>
                    </td>
                    <td style='text-align:center;'>
                        <div class='dropdown'>
                            <button class='btn btn-danger btn-xs dropdown-toggle' type='button' data-toggle='dropdown' aria-expanded='true'>
                                <i class='fa fa-cog fa-lg'></i>
                                <span class='caret'></span>
                            </button>
                            <ul class='dropdown-menu dropdown-menu-right' role='menu' style='font-size:17px'>";

                if (($_SESSION['perfil'] ?? '') == 'Administrador') {
                    echo "<li role='presentation'><a role='menuitem' tabindex='-1' href='#' idCompra='{$value['id']}' class='btn-anular-compra'><i class='fas fa-ban' style='color:red;'></i> Anular compra</a></li>";
                }
                echo "        <li role='presentation'><a role='menuitem' tabindex='-1' href='nueva-compra'><i class='fa fa-plus'></i> Nueva compra</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='9' style='text-align:center;'>No hay compras registradas</td></tr>";
        }
    }

    public static function ajaxLlenarCarrito()
    {

        $datosCarrito = $_POST;

        $respuesta = ControladorCompras::ctrLlenarCarrito($datosCarrito);
    }

    public $idEliminarCarroC;
    public function ajaxEliminarItemCarritoC()
    {
        $idEliminar = $this->idEliminarCarroC;

        //Como antes, usamos extract() por comodidad, pero podemos no hacerlo tranquilamente
        $carritoC = isset($_SESSION['carritoC']) ? $_SESSION['carritoC'] : [];
        //Asignamos a la variable $carro los valores guardados en la sessión
        if (isset($carritoC[$idEliminar])) {
            unset($carritoC[$idEliminar]);
            $carritoC = array_values($carritoC);
        }
        $_SESSION['carritoC'] = $carritoC;

        $respuesta = ControladorCompras::ctrLoadCarro(null);
    }
    public $descuentoGlobalC;
    public function ajaxDescuentoGlobal()
    {
        $descuentoGlobalC = $this->descuentoGlobalC;
        $respuesta = ControladorCompras::ctrLoadCarro($descuentoGlobalC);
    }
    //  ELIMINAR TODOS LOS ITEMS DEL CARRITO
    public $eliminarCarro;
    public function ajaxEliminarCarrito()
    {
        $eliminarCarro = $this->eliminarCarro;

        //Eliminar toda la sesión del carrito sin generar avisos si no existe
        if (isset($_SESSION['carritoC'])) {
            unset($_SESSION['carritoC']);
        }
        $_SESSION['carritoC'] = [];

        //Finalmente, actualizamos la sesión, como hicimos cuando agregamos un producto y volvemos al catálogo
        $respuesta = ControladorCompras::ctrLoadCarro(null);
    }
    public function ajaxGuardarCompra()
    {

        $datos = $_POST;
        $respuesta = ControladorCompras::ctrGuardarCompra($datos);
    }

    public function ajaxNotaCD()
    {
        $tipoComprobante = $_POST['tipoComprobante'];
        if ($tipoComprobante == '07' || $tipoComprobante == '08') {
            echo '
            
            <legend class="text-bold" style="margin-left:15px; font-size:1.3em; letter-spacing: 1px;">DATOS DEL COMPROBANTE QUE MODIFICA:</legend>

            <div class="col-md-3 col-xs-6">
              <div class="form-group">
              <div class="input-group">                                   
             <span class="input-group-addon"><i class="fa fa-key"></i></span>
           
              <select  class="form-control" name="compModificar" id="compModificar" value="" >
                  <option value="01">Factura</option>
                  <option value="03">Boleta</option>
                 
              </select>                                                                
              </div>
              </div>
            </div>
             <!-- ENTRADA SERIE DOC-->
             <div class="col-md-2 col-xs-6">
            <div class="form-group">
             <div class="input-group">
             <span class="input-group-addon"><i class="fas fa-barcode"></i></span>
             <input type="text" class="form-control" name="serieModificar" id="serieModificar" required placeholder="Serie">
              </div>                               
            </div>
            </div>
                   <!-- ENTRADA CORRELATIVO Modificar-->
            <div class="col-md-2 col-xs-6">
            <div class="form-group">
             <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-file-invoice"></i></span>
             <input type="text" class="form-control" name="correlativoModificar" id="correlativoModificar" required placeholder="Correlativo">
              </div>                               
            </div>
            </div>

            <div class="col-md-3 col-xs-6">
              <div class="form-group">
              <div class="input-group">                                   
             <span class="input-group-addon"><i class="fa fa-file-invoice"></i></span>
           
              <select  class="form-control" name="motivoModificar" id="motivoModificar" value="" >';

            $item = "tipo";
            if ($tipoComprobante == '07') {
                $valor = 'C';
            }
            if ($tipoComprobante == '08') {
                $valor = 'D';
            }
            $codigo = null;
            $motivo = ControladorSunat::ctrMostrarTablaParametrica($item, $valor, $codigo);
            foreach ($motivo as $k => $value) {
                echo '<option value="' . $value['codigo'] . '">' . $value['descripcion'] . '</option>';
            }

            echo '</select>                                                                
              </div>
              </div>
            </div>

                     <!-- ENTRADA FECHA DOC-->
                     <div class="col-md-2 col-xs-6">
            <div class="form-group">
             <div class="input-group">
             <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
             <input type="text" class="form-control" name="fechaModificar" id="fechaModificar"  value="' . date("d/m/Y") . '" required>
              </div>                               
            </div>
            </div>
            
            
            
            
            ';
            echo "<script>
            $('#fechaModificar').datepicker({
                autoclose: true,
                'language' : 'es'
                })
            </script>";
        }
    }
    public function ajaxAnularCompra()
    {
        $idCompra = $_POST['idCompra'];
        $respuesta = ControladorCompras::ctrAnularCompra($idCompra);
        echo $respuesta;
    }
    public function ajaxBuscarProducto()
    {
        $valor = $_POST['buscarP'];
        $respuesta = ControladorCompras::ctrBucarProducto($valor);
        if ($respuesta) {

            foreach ($respuesta as $k => $v) {
                echo '<legend style="margin:0px !important; padding:4px !important; font-size: 17px;"><a href="#" class="btn-add-item" idProducto="' . $v['id'] . '" >' . ++$k . '. ' . $v['codigo'] . ' - <b style="font-size: 14px; color: #444; font-weight: 600; letter-spacing: 1px;">' . $v['descripcion'] . '(S/ ' . $v['precio_unitario'] . ')</b></a></legend>';
            }
        }
    }

    public function ajaxMostrarProductos()
    {
        $item = 'id';
        $valor = $_POST['idProducto'];
        $respuesta = ControladorProductos::ctrMostrarProductos($item, $valor);
        echo json_encode($respuesta);
    }
}

if (isset($_POST['subtotal'])) {
    $objCarrito = new AjaxCompras();
    $objCarrito->ajaxLlenarCarrito();
}
//DESCUENTO GLOBAL
if (isset($_POST['descontarG'])) {
    $objDesc = new AjaxCompras();
    $objDesc->descuentoGlobalC = $_POST['descuentoGlobalC'];
    $objDesc->ajaxDescuentoGlobal();
}
// ELIMINAR ITEM DEL CARRO
if (isset($_POST['idEliminarCarroC'])) {
    $objEliminarItemCarro = new AjaxCompras();
    $objEliminarItemCarro->idEliminarCarroC = $_POST['idEliminarCarroC'];
    $objEliminarItemCarro->ajaxEliminarItemCarritoC();
}
// ELIMINAR CARRO
if (isset($_POST['eliminarCarro'])) {
    $objEliminarCarro = new AjaxCompras();
    $objEliminarCarro->eliminarCarro = $_POST['eliminarCarro '];
    $objEliminarCarro->ajaxEliminarCarrito();
}
if (isset($_POST['docIdentidad'])) {
    $objCompras = new AjaxCompras();
    $objCompras->ajaxGuardarCompra();
}
if (isset($_POST['tipoComprobante'])) {
    $objCompras = new AjaxCompras();
    $objCompras->ajaxNotaCD();
}
if (isset($_POST['idCompra'])) {
    $objComprasBaja = new AjaxCompras();
    $objComprasBaja->ajaxAnularCompra();
}
if (isset($_POST['buscarProducto'])) {
    $objProducto = new AjaxCompras();
    $objProducto->ajaxBuscarProducto();
}
if (isset($_POST['idProducto'])) {
    $objProducto = new AjaxCompras();
    $objProducto->ajaxMostrarProductos();
}
// CARGAR COMPRAS
if (isset($_POST['cargarCompras'])) {
    $objCompras = new AjaxCompras();
    $objCompras->ajaxCargarCompras();
}
