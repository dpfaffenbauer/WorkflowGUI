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
use Pimcore\Model\DataObject\Localizedfield;
use WorkflowGuiBundle\Validation\DependencyInjection;
use WorkflowGuiBundle\Validation\ValidationErrorsTrait;

class LocalizedfieldsHandler implements HandlerInterface, DependencyInjection\ServiceLocatorAwareInterface
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
     * @param Data\Localizedfields $data
     * @param Localizedfield $value
     * @param array $params
     * @return bool
     */
    public function isValid(Data $data, $value, array $params): bool
    {
        if (!$data instanceof Data\Localizedfields) {
            throw new \InvalidArgumentException(sprintf(
                "Data must be '%s', '%s' given",
                Data\Localizedfields::class,
                get_class($data)
            ));
        }

        if (!$value instanceof Localizedfield) {
            throw new \InvalidArgumentException(sprintf(
                "Value must be '%s', '%s' given",
                Localizedfield::class,
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        $this->setErrors([]);
        $languages = $this->getLanguages();

        foreach ($params as $fieldName => $fieldParams) {
            $field = $this->getFieldDefinition($data, $fieldName);
            $handler = $this->getServiceLocator()->getHandler($field);

            foreach ($languages as $language) {
                $fieldValue = $value->getLocalizedValue($fieldName, $language, true);
                if (!$handler->isValid($field, $fieldValue, $fieldParams)) {
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
     * @return array
     */
    protected function getLanguages(): array
    {
        return \Pimcore\Tool::getValidLanguages();
    }

    /**
     * @param Data\Localizedfields $data
     * @param string $name
     * @return Data
     */
    protected function getFieldDefinition(Data\Localizedfields $data, string $name): Data
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
