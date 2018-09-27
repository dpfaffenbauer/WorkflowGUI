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

namespace WorkflowGuiBundle\EventListener;

use Pimcore\Event\Model\WorkflowEvent;
use Pimcore\Event\WorkflowEvents;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WorkflowGuiBundle\Validation\ValidationManager;

class WorkflowListener implements EventSubscriberInterface
{
    /**
     * @var ValidationManager
     */
    protected $validationManager;

    /**
     * @param ValidationManager $validationManager
     */
    public function __construct(ValidationManager $validationManager)
    {
        $this->validationManager = $validationManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            WorkflowEvents::PRE_ACTION => 'onPreAction',
        ];
    }

    /**
     * @param WorkflowEvent $event
     * @throws \Exception
     */
    public function onPreAction(WorkflowEvent $event)
    {
        $manager = $event->getWorkflowManager();
        $element = $manager->getElement();

        if ($element instanceof Concrete) {
            if (!$this->validationManager->isValid($manager)) {
                $errors = $this->validationManager->getErrors();
                throw new \Exception(implode('<br>', $errors));
            }
        }
    }
}
