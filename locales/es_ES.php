<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & FranÃ§ois Legastelois
// Purpose of file: plugin order v1.3.0 - GLPI 0.78.3
// ---------------------------------------------------------------------- */

$LANG['plugin_order']['title'][1] = "Gestión de Pedidos";

$LANG['plugin_order'][0] = "Número de pedido";
$LANG['plugin_order'][1] = "Fecha de pedido";
$LANG['plugin_order'][2] = "Descripción";
$LANG['plugin_order'][3] = "Presupuesto";
$LANG['plugin_order'][4] = "Detalle de proveedor";
$LANG['plugin_order'][5] = "Validación";
$LANG['plugin_order'][6] = "Entrega";
$LANG['plugin_order'][7] = "Pedido";
$LANG['plugin_order'][8] = "Otros materiales";
$LANG['plugin_order'][9] = "Tipo de otros materiales";
$LANG['plugin_order'][10] = "Calidad";
$LANG['plugin_order'][13] = "Precio sin IVA";
$LANG['plugin_order'][14] = "Precio con IVA";
$LANG['plugin_order'][15] = "Precio + portes sin IVA";
$LANG['plugin_order'][25] = "IVA";
$LANG['plugin_order'][26] = "Portes";
$LANG['plugin_order'][28] = "Número de factura";
$LANG['plugin_order'][30] = "Número de presupuesto";
$LANG['plugin_order'][31] = "Número de pedido";
$LANG['plugin_order'][32] = "Condiciones de pago";
$LANG['plugin_order'][39] = "Nombre del pedido";
$LANG['plugin_order'][40] = "Lugar de entrega";
$LANG['plugin_order'][42] = "No se pueden asociar varios ítems a una sola línea de detalle";
$LANG['plugin_order'][44] = "El número de pedido es obligatorio!";
$LANG['plugin_order'][45] = "No se pueden generar ítems no entregados";
$LANG['plugin_order'][46] = "No se pueden asociar ítems no entregados";
$LANG['plugin_order'][47] = "Información del pedido";
$LANG['plugin_order'][48] = "Una o varias filas seleccionadas no tienen ítems asociados";

$LANG['plugin_order']['budget'][1] = "Pedidos asociados";
$LANG['plugin_order']['budget'][2] = "Presupuesto ya utilizado";
$LANG['plugin_order']['budget'][3] = "El presupuesto tiene valor nulo. Por favor, añada un valor";

$LANG['plugin_order']['config'][0] = "Configuración del plugin";
$LANG['plugin_order']['config'][1] = "IVA predeterminado";
$LANG['plugin_order']['config'][2] = "Utilizar proceso de validación";
$LANG['plugin_order']['config'][3] = "Acciones automáticas a la entrega";
$LANG['plugin_order']['config'][4] = "Activar generación automática";
$LANG['plugin_order']['config'][5] = "Nombre predeterminado";
$LANG['plugin_order']['config'][6] = "Número de serie predeterminado";
$LANG['plugin_order']['config'][7] = "Número de inventario predeterminado";
$LANG['plugin_order']['config'][8] = "Entidad predeterminada";
$LANG['plugin_order']['config'][9] = "Categoría predeterminada";
$LANG['plugin_order']['config'][10] = "Título predeterminado";
$LANG['plugin_order']['config'][11] = "Descripción predeterminada";
$LANG['plugin_order']['config'][12] = "Estado predeterminado";

$LANG['plugin_order']['delivery'][1] = "Material entregado";
$LANG['plugin_order']['delivery'][2] = "Recibir material";
$LANG['plugin_order']['delivery'][3] = "Generar material";
$LANG['plugin_order']['delivery'][4] = "Recibir materiales (masivamente)";
$LANG['plugin_order']['delivery'][5] = "Ítems entregados";
$LANG['plugin_order']['delivery'][6] = "Cantidad a recibir";
$LANG['plugin_order']['delivery'][9] = "Generar";
$LANG['plugin_order']['delivery'][11] = "Vincular con material ya existente";
$LANG['plugin_order']['delivery'][12] = "Borrar vínculo con material";
$LANG['plugin_order']['delivery'][13] = "Material generado mediante un pedido";
$LANG['plugin_order']['delivery'][14] = "Material vinculado a un pedido";
$LANG['plugin_order']['delivery'][15] = "Desvincular material del pedido";
$LANG['plugin_order']['delivery'][16] = "Material ya enlazado a otro";
$LANG['plugin_order']['delivery'][17] = "No hay materiales para generar";

$LANG['plugin_order']['detail'][1] = "Material";
$LANG['plugin_order']['detail'][2] = "Referencia";
$LANG['plugin_order']['detail'][4] = "Precio unitario sin IVA";
$LANG['plugin_order']['detail'][5] = "Añadir al pedido";
$LANG['plugin_order']['detail'][6] = "Tipo";
$LANG['plugin_order']['detail'][7] = "Cantidad";
$LANG['plugin_order']['detail'][18] = "Precio con descuento sin IVA";
$LANG['plugin_order']['detail'][19] = "Estado";
$LANG['plugin_order']['detail'][20] = "No hay ítems para recibir";
$LANG['plugin_order']['detail'][21] = "Fecha de entrega";
$LANG['plugin_order']['detail'][25] = "Descuento (%)";
$LANG['plugin_order']['detail'][27] = "Por favor elija un proveedor";
$LANG['plugin_order']['detail'][29] = "No hay ítems seleccionados";
$LANG['plugin_order']['detail'][30] = "Item seleccionado con éxito";
$LANG['plugin_order']['detail'][31] = "Item recibido con éxito";
$LANG['plugin_order']['detail'][32] = "Item ya recibido";
$LANG['plugin_order']['detail'][33] = "El porcentaje de descuento debe estar entre 0 y 100";
$LANG['plugin_order']['detail'][34] = "Añadir referencia";
$LANG['plugin_order']['detail'][35] = "Borrar referencia";
$LANG['plugin_order']['detail'][36] = "¿Realmente quiere borrar las líneas de detalle? Los ítems entregados no se asociarán al pedido !";
$LANG['plugin_order']['detail'][37] = "No hay suficientes ítems para entregar";
$LANG['plugin_order']['detail'][38] = "¿Quiere realmente cancelar el pedido? ¡Esta opción es irreversible!";
$LANG['plugin_order']['detail'][39] = "¿Quiere cancelar la validación de la aprobación?";
$LANG['plugin_order']['detail'][40] = "¿Quiere realmente editar el pedido? ";

$LANG['plugin_order']['generation'][0] = "Generación";
$LANG['plugin_order']['generation'][1] = "Generar el pedido";
$LANG['plugin_order']['generation'][2] = "Pedido";
$LANG['plugin_order']['generation'][3] = "Dirección de facturación";
$LANG['plugin_order']['generation'][4] = "Dirección de entrega";
$LANG['plugin_order']['generation'][5] = "El";
$LANG['plugin_order']['generation'][6] = "Cantidad";
$LANG['plugin_order']['generation'][7] = "Designation";
$LANG['plugin_order']['generation'][8] = "Precio unitario";
$LANG['plugin_order']['generation'][9] = "Total sin IVA";
$LANG['plugin_order']['generation'][10] = "Emitir pedido";
$LANG['plugin_order']['generation'][11] = "Receptor";
$LANG['plugin_order']['generation'][12] = "Número de pedido";
$LANG['plugin_order']['generation'][13] = "Porcentaje de descuento";
$LANG['plugin_order']['generation'][14] = "TOTAL sin IVA";
$LANG['plugin_order']['generation'][15] = "TOTAL con IVA";
$LANG['plugin_order']['generation'][16] = "Firma para emitir el pedido";
$LANG['plugin_order']['generation'][17] = "€";

$LANG['plugin_order']['history'][2] = "Añadir";
$LANG['plugin_order']['history'][4] = "Suprimir";

$LANG['plugin_order']['infocom'][1] = "Algunos campos no se pueden modificar porque pertenecen a un pedido";

$LANG['plugin_order']['item'][0] = "Material vinculado";
$LANG['plugin_order']['item'][2] = "Item no asociado";

$LANG['plugin_order']['mailing'][2] = "para";

$LANG['plugin_order']['menu'][0] = "Menú";
$LANG['plugin_order']['menu'][1] = "Gestionar pedidos";
$LANG['plugin_order']['menu'][2] = "Gestionar referencias de productos";
$LANG['plugin_order']['menu'][3] = "Gestionar presupuestos";
$LANG['plugin_order']['menu'][4] = "Pedidos";
$LANG['plugin_order']['menu'][5] = "Referencias";
$LANG['plugin_order']['menu'][6] = "Presupuestos";

$LANG['plugin_order']['parser'][0] = "Ficheros";
$LANG['plugin_order']['parser'][1] = "Usar este modelo";
$LANG['plugin_order']['parser'][2] = "No hay ficheros en el directorio";
$LANG['plugin_order']['parser'][3] = "Utilizar esta firma";
$LANG['plugin_order']['parser'][4] = "Por favor, seleccione un modelo en sus preferencias";

$LANG['plugin_order']['profile'][0] = "Gestión de permisos";
$LANG['plugin_order']['profile'][1] = "Validar pedido";
$LANG['plugin_order']['profile'][2] = "Cancelar pedido";
$LANG['plugin_order']['profile'][3] = "Editar un pedido validado";

$LANG['plugin_order']['reference'][1] = "Referencia del producto";
$LANG['plugin_order']['reference'][2] = "Añadir un proveedor";
$LANG['plugin_order']['reference'][3] = "Listar referencias";
$LANG['plugin_order']['reference'][5] = "Proveedor para la referencia";
$LANG['plugin_order']['reference'][6] = "Ya existe una referencia con ese nombre";
$LANG['plugin_order']['reference'][7] = "Referencia(s) usada";
$LANG['plugin_order']['reference'][8] = "No se puede crear una referencia sin nombre";
$LANG['plugin_order']['reference'][9] = "No se puede crear una referencia sin tipo";
$LANG['plugin_order']['reference'][10] = "Referencia del fabricante del producto";
$LANG['plugin_order']['reference'][11] = "Ver por tipo de ítem";
$LANG['plugin_order']['reference'][12] = "Seleccionar el tipo de ítem deseado";

$LANG['plugin_order']['status'][0] = "Estado del pedido";
$LANG['plugin_order']['status'][1] = "En proceso de entrega";
$LANG['plugin_order']['status'][2] = "Entregado";
$LANG['plugin_order']['status'][3] = "Estado de entrega";
$LANG['plugin_order']['status'][4] = "Estado no definido";
$LANG['plugin_order']['status'][7] = "Esperando aprobación";
$LANG['plugin_order']['status'][8] = "Recibido";
$LANG['plugin_order']['status'][9] = "Borrador";
$LANG['plugin_order']['status'][10] = "Cancelado";
$LANG['plugin_order']['status'][11] = "Pendiente de entrega";
$LANG['plugin_order']['status'][12] = "Validado";
$LANG['plugin_order']['status'][13] = "Estadísticas de entrega";

$LANG['plugin_order']['survey'][0] = "Calidad del proveedor";
$LANG['plugin_order']['survey'][1] = "Calidad del servicio administrativo (contrato, facturas, correo...)";
$LANG['plugin_order']['survey'][2] = "Calidad del servicio comercial, frecuencia de visitas, reactividad";
$LANG['plugin_order']['survey'][3] = "Disponibilidad de los interlocutores";
$LANG['plugin_order']['survey'][4] = "Calidad de los colaboradores del proveedor";
$LANG['plugin_order']['survey'][5] = "Fiabilidad de los plazos de entrega anunciados";
$LANG['plugin_order']['survey'][6] = "Muy insatisfecho";
$LANG['plugin_order']['survey'][7] = "Muy satisfecho";
$LANG['plugin_order']['survey'][8] = "Nota media sobre 10 (X puntos / 5)";
$LANG['plugin_order']['survey'][9] = "Nota global del proveedor";
$LANG['plugin_order']['survey'][10] = "Nota";
$LANG['plugin_order']['survey'][11] = "Comentario de evaluación";

$LANG['plugin_order']['validation'][0] = "Por favor, añada al menos un equipamiento a su pedido.";
$LANG['plugin_order']['validation'][1] = "Solicitar validación de pedido";
$LANG['plugin_order']['validation'][2] = "Pedido validado";
$LANG['plugin_order']['validation'][3] = "Pedido en proceso de recepción";
$LANG['plugin_order']['validation'][4] = "Pedido entregado";
$LANG['plugin_order']['validation'][5] = "Pedido cancelado";
$LANG['plugin_order']['validation'][6] = "Proceso de validación";
$LANG['plugin_order']['validation'][7] = "Validación de pedido solicitada correctamente";
$LANG['plugin_order']['validation'][8] = "Pedido en proceso de edición";
$LANG['plugin_order']['validation'][9] = "Validar pedido";
$LANG['plugin_order']['validation'][10] = "El pedido está validado";
$LANG['plugin_order']['validation'][11] = "Solicitar validación";
$LANG['plugin_order']['validation'][12] = "Cancelar pedido";
$LANG['plugin_order']['validation'][13] = "Cancelar solicitud de validación";
$LANG['plugin_order']['validation'][14] = "Se ha cancelado al solicitud de validación";
$LANG['plugin_order']['validation'][15] = "El pedido está en fase de borrador";
$LANG['plugin_order']['validation'][16] = "Validación cancelada satisfactoriamente";
$LANG['plugin_order']['validation'][17] = "Editar pedido";
$LANG['plugin_order']['validation'][18] = "Comentario de validación";
$LANG['plugin_order']['validation'][19] = "Editor de validación";

?>
