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

use Glpi\Tests\DbTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PluginOrderOrder;
use RuntimeException;

final class OrderTest extends DbTestCase
{
    public static function invalidTemplateNameProvider(): iterable
    {
        yield 'path traversal' => ['../../../etc/passwd.odt'];
        yield 'embedded path separator' => ['sub/template.odt'];
        yield 'disallowed extension' => ['template.docx'];
    }

    #[DataProvider('invalidTemplateNameProvider')]
    public function testGenerateOrderRejectsInvalidTemplateName(string $template): void
    {
        $order = new PluginOrderOrder();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid template name');

        $order->generateOrder([
            'id'       => 0,
            'template' => $template,
            'sign'     => '',
        ]);
    }

    public static function invalidSignatureNameProvider(): iterable
    {
        yield 'path traversal via slash' => ['../../../etc/passwd.png'];
        yield 'path traversal via backslash' => ['..\\..\\signature.png'];
        yield 'disallowed extension' => ['signature.php'];
    }

    #[DataProvider('invalidSignatureNameProvider')]
    public function testGenerateOrderRejectsInvalidSignatureName(string $signature): void
    {
        $order = new PluginOrderOrder();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid signature file name');

        $order->generateOrder([
            'id'       => 0,
            'template' => 'template.odt',
            'sign'     => $signature,
        ]);
    }

    public static function acceptedSignatureProvider(): iterable
    {
        yield 'no signature' => [''];
        yield 'valid png signature' => ['signature.png'];
    }

    #[DataProvider('acceptedSignatureProvider')]
    public function testGenerateOrderAcceptsValidSignatureAndReachesFileCheck(string $signature): void
    {
        $order = new PluginOrderOrder();

        // The template file does not exist in the test environment: reaching this
        // exception proves the name/signature validation passed successfully.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Template file not found or not readable');

        $order->generateOrder([
            'id'       => 0,
            'template' => 'template.odt',
            'sign'     => $signature,
        ]);
    }
}
