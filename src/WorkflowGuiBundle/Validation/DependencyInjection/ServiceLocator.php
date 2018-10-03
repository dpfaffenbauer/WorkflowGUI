<?php
/**
 * Workflow GUI Pimcore Plugin
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2018 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://github.com/dpfaffenbauer/pimcore-WorkflowGui/blob/master/LICENSE.md     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace WorkflowGuiBundle\Validation\DependencyInjection;

use Pimcore\Model\DataObject\ClassDefinition\Data;
use Symfony\Component\DependencyInjection\ServiceLocator as SymfonyServiceLocator;
use WorkflowGuiBundle\Validation\Handler\HandlerInterface;

class ServiceLocator extends SymfonyServiceLocator
{
    const FALLBACK_HANDLER_ID = 'general';

    /**
     * @param Data $data
     * @return HandlerInterface
     */
    public function getHandler(Data $data): HandlerInterface
    {
        $id = $this->has($data->getFieldtype()) ? $data->getFieldtype() : self::FALLBACK_HANDLER_ID;

        return $this->get($id);
    }
}
