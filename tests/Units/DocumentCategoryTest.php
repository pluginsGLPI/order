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
 * @copyright Copyright (C) 2009-2026 by Order plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 */

declare(strict_types=1);

namespace GlpiPlugin\Order\Tests\Units;

use DocumentCategory;
use Glpi\Tests\DbTestCase;
use PluginOrderDocumentCategory;

final class DocumentCategoryTest extends DbTestCase
{
    private const XSS_PAYLOAD = '<script>alert(1);</script>';

    public function testShowForDocumentCategoryEscapesPrefix(): void
    {
        $this->login();

        $document_category = $this->createItem(DocumentCategory::class, [
            'name' => $this->getUniqueString(),
        ]);

        $this->createItem(PluginOrderDocumentCategory::class, [
            'documentcategories_id'     => $document_category->getID(),
            'documentcategories_prefix' => self::XSS_PAYLOAD,
        ]);

        ob_start();
        PluginOrderDocumentCategory::showForDocumentCategory($document_category);
        $output = ob_get_clean();

        $this->assertStringNotContainsString(self::XSS_PAYLOAD, $output);
        $this->assertStringContainsString(htmlescape(self::XSS_PAYLOAD), $output);
    }
}
