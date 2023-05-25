<?php

/**
 * -------------------------------------------------------------------------
 * Order plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Order.
 *
 * Order is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Order is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Order. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2009-2023 by Order plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

$sign = array_key_exists('sign', $_GET) ? $_GET['sign'] : '';

// Avoid access to another directory or to files that does not match allowed extension
if (preg_match('/(\\\\|\/)/', $sign) !== 0
    || preg_match('/\.' . preg_quote(PLUGIN_ORDER_SIGNATURE_EXTENSION, '/') . '$/', $sign) === 0) {
   header('HTTP/1.1 403 Forbidden');
   exit();
}

$filename = PLUGIN_ORDER_SIGNATURE_DIR . $sign;

if (empty($sign) || !@is_file($filename)) {
   header('HTTP/1.1 404 Not Found');
   exit();
}

Toolbox::sendFile($filename, $sign);
