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

namespace WorkflowGuiBundle\Validation;

use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\WorkflowManagement\Workflow\Manager as WorkflowManager;

class ValidationManager implements DependencyInjection\ServiceLocatorAwareInterface
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
     * @param WorkflowManager $workflowManager
     * @return bool
     */
    public function isValid(WorkflowManager $workflowManager): bool
    {
        $this->setErrors([]);

        $object = $this->getObject($workflowManager);
        $class = $object->getClass();
        $params = $this->getParams($workflowManager, (int) $class->getId());

        foreach ($params as $fieldName => $fieldParams) {
            $field = $this->getFieldDefinition($class, $fieldName);
            $handler = $this->getServiceLocator()->getHandler($field);

            $fieldValue = $this->getValue($object, $field);
            $handler->isValid($field, $fieldValue, $fieldParams);
            $errors = $handler->getErrors();

            if (!empty($errors) && $class->getAllowInherit()) {
                $getInheritedValues = AbstractObject::doGetInheritedValues();
                AbstractObject::setGetInheritedValues(true);

                $fieldValue = $this->getValue($object, $field);
                $handler->isValid($field, $fieldValue, $fieldParams);
                $errors = $handler->getErrors();

                AbstractObject::setGetInheritedValues($getInheritedValues);
            }

            foreach ($errors as $error) {
                $this->addError($error);
            }
        }

        return !$this->hasErrors();
    }

    /**
     * @param WorkflowManager $workflowManager
     * @return Concrete
     */
    protected function getObject(WorkflowManager $workflowManager): Concrete
    {
        $element = $workflowManager->getElement();

        if (!$element instanceof Concrete) {
            throw new \UnexpectedValueException(sprintf(
                "Element must be '%s', '%s' given",
                Concrete::class,
                get_class($element)
            ));
        }

        return $element;
    }

    /**
     * @param ClassDefinition $class
     * @param string $name
     * @return ClassDefinition\Data
     */
    protected function getFieldDefinition(ClassDefinition $class, string $name): ClassDefinition\Data
    {
        $field = $class->getFieldDefinition($name, ['suppressEnrichment' => true]);

        if (!$field instanceof ClassDefinition\Data) {
            throw new \UnexpectedValueException(sprintf(
                "Field definition must be '%s', '%s' given",
                ClassDefinition\Data::class,
                is_object($field) ? get_class($field) : gettype($field)
            ));
        }

        return $field;
    }

    /**
     * @param Concrete $object
     * @param ClassDefinition\Data $data
     * @return mixed
     */
    protected function getValue(Concrete $object, ClassDefinition\Data $data)
    {
        $name = $data->getName();
        $getter = 'get' . ucfirst($name);

        return $object->$getter();
    }

    /**
     * @param WorkflowManager $workflowManager
     * @param int $classId
     * @return array
     */
    protected function getParams(WorkflowManager $workflowManager, int $classId): array
    {
        $params = [];

        $actionName = $workflowManager->getActionData()['action'];
        $action = $workflowManager->getWorkflow()->getActionConfig($actionName) ?? [];
        $validation = $action['validation'] ?? [];

        $rules = [];
        foreach ($validation as $item) {
            if ($classId == $item['classId']) {
                $rules = $item['rules'];
                break;
            }
        }

        foreach ($rules as $rule) {
            $this->convertRule($rule, $params);
        }

        return $params;
    }

    /**
     * @param string $rule
     * @param array $params
     */
    protected function convertRule(string $rule, array &$params)
    {
        $items = explode('.', $rule);
        $key = array_shift($items);

        if (!isset($params[$key])) {
            $params[$key] = [];
        }

        if (!empty($items)) {
            $rule = implode('.', $items);
            $this->convertRule($rule, $params[$key]);
        }
    }
}
