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

/**
 * Serves the configured e-mail header image.
 *
 * Deliberately unauthenticated: mail clients fetch this URL without a GLPI
 * session. It only ever streams the single administrator-uploaded image from
 * a fixed directory - no user input reaches the filesystem lookup.
 */

$config = PluginOrderConfig::getConfig();
$path   = $config->getMailHeaderPath();

if ($path === null) {
    http_response_code(404);
    exit;
}

$mimes = [
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
];
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
header('Content-Length: ' . filesize($path));
header('Cache-Control: public, max-age=86400');
readfile($path);
exit;
