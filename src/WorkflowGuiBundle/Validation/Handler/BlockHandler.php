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

namespace WorkflowGuiBundle\Validation\Handler;

use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\Data\BlockElement;
use WorkflowGuiBundle\Validation\DependencyInjection;
use WorkflowGuiBundle\Validation\ValidationErrorsTrait;

class BlockHandler implements HandlerInterface, DependencyInjection\ServiceLocatorAwareInterface
{
    use ValidationErrorsTrait;
    use DependencyInjection\ServiceLocatorAwareTrait;

    /**
     * @param DependencyInjection\ServiceLocator $serviceLocator
     */
    public function __construct(DependencyInjection\ServiceLocator $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    /**
     * @param Data/Block $data
     * @param array|null $value
     * @param array $params
     * @return bool
     */
    public function isValid(Data $data, $value, array $params): bool
    {
        if (!$data instanceof Data\Block) {
            throw new \InvalidArgumentException(sprintf(
                "Data must be '%s', '%s' given",
                Data\Block::class,
                get_class($data)
            ));
        }

        if (is_null($value)) {
            $value = [];
        }

        if (!is_array($value)) {
            throw new \InvalidArgumentException(sprintf(
                "Value must be '%s', '%s' given",
                'array',
                gettype($value)
            ));
        }

        $this->setErrors([]);

        foreach ($params as $fieldName => $fieldParams) {
            $field = $this->getFieldDefinition($data, $fieldName);
            $handler = $this->getServiceLocator()->getHandler($field);

            foreach ($value as $block) {
                /** @var BlockElement $element */
                $element = $block[$fieldName];
                if (!$handler->isValid($field, $element->getData(), $fieldParams)) {
                    foreach ($handler->getErrors() as $error) {
                        $this->addError($error);
                    }
                    break;
                }
            }
        }

        return !$this->hasErrors();
    }

    /**
     * @param Data\Block $data
     * @param string $name
     * @return Data
     */
    protected function getFieldDefinition(Data\Block $data, string $name): Data
    {
        $field = $data->getFielddefinition($name, ['suppressEnrichment' => true]);

        if (!$field instanceof Data) {
            throw new \UnexpectedValueException(sprintf(
                "Field definition must be '%s', '%s' given",
                Data::class,
                is_object($field) ? get_class($field) : gettype($field)
            ));
        }

        return $field;
    }
}
