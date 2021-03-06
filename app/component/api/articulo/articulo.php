<?php if (!is_null($values['articulo'])): ?>
	{
		"id": <?php echo $values['articulo']->get('id') ?>,
		"localizador": <?php echo $values['articulo']->get('localizador') ?>,
		"nombre": "<?php echo urlencode($values['articulo']->get('nombre')) ?>",
		"puc": <?php echo $values['articulo']->get('puc') ?>,
		"pvp": <?php echo $values['articulo']->get('pvp') ?>,
		"margen": <?php echo $values['articulo']->get('margen') ?>,
		"palb": <?php echo $values['articulo']->get('palb') ?>,
		"idMarca": <?php echo $values['articulo']->get('id_marca') ?>,
		"idProveedor": <?php echo $values['articulo']->get('id_proveedor') ?>,
		"stock": <?php echo $values['articulo']->get('stock') ?>,
		"stockMin": <?php echo $values['articulo']->get('stock_min') ?>,
		"stockMax": <?php echo $values['articulo']->get('stock_max') ?>,
		"loteOptimo": <?php echo $values['articulo']->get('lote_optimo') ?>,
		"iva": <?php echo $values['articulo']->get('iva') ?>,
		"fechaCaducidad": "<?php echo $values['articulo']->get('fecha_caducidad', 'm/Y') ?>",
		"mostrarFecCad": <?php echo $values['articulo']->get('mostrar_feccad') ? 'true' : 'false' ?>,
		"observaciones": "<?php echo urlencode($values['articulo']->get('observaciones')) ?>",
		"mostrarObsPedidos": <?php echo $values['articulo']->get('mostrar_obs_pedidos') ? 'true' : 'false' ?>,
		"mostrarObsVentas": <?php echo $values['articulo']->get('mostrar_obs_ventas') ? 'true' : 'false' ?>,
		"referencia": "<?php echo urlencode($values['articulo']->get('referencia')) ?>",
		"ventaOnline": <?php echo $values['articulo']->get('venta_online') ? 'true' : 'false' ?>,
		"mostrarEnWeb": <?php echo $values['articulo']->get('mostrar_en_web') ? 'true' : 'false' ?>,
		"idCategoria": <?php echo $values['articulo']->get('id_categoria') ?>,
		"descCorta": "<?php echo urlencode($values['articulo']->get('desc_corta')) ?>",
		"desc": "<?php echo urlencode($values['articulo']->get('desc')) ?>",
		"codigosBarras": [
<?php foreach ($values['articulo']->getCodigosBarras() as $i => $cb): ?>
			{
				"id": <?php echo $cb->get('id') ?>,
				"codigoBarras": <?php echo $cb->get('codigo_barras') ?>,
				"porDefecto": <?php echo $cb->get('por_defecto') ? 'true' : 'false' ?>
			}<?php if ($i<count($values['articulo']->getCodigosBarras())-1): ?>,<?php endif ?>
<?php endforeach ?>
		],
		"activo": <?php echo $values['articulo']->get('activo') ? 'true' : 'false' ?>
	}
<?php else: ?>
	null
<?php endif ?>