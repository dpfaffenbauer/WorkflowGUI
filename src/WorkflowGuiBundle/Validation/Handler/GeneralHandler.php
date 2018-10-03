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
use Pimcore\Model\Element\ValidationException;
use WorkflowGuiBundle\Validation\ValidationErrorsTrait;

class GeneralHandler implements HandlerInterface
{
    use ValidationErrorsTrait;

    /**
     * @param Data $data
     * @param mixed $value
     * @param array $params
     * @return bool
     */
    public function isValid(Data $data, $value, array $params): bool
    {
        $this->setErrors([]);

        try {
            $this->checkValidity($data, $value);
        } catch (ValidationException $ex) {
            $this->addError($ex->getMessage());
        }

        return !$this->hasErrors();
    }

    /**
     * @param Data $data
     * @param $value
     * @throws ValidationException
     */
    protected function checkValidity(Data $data, $value)
    {
        $exception = null;

        $mandatory = $data->getMandatory();
        $data->setMandatory(true);

        try {
            $data->checkValidity($value);
        } catch (ValidationException $ex) {
            $exception = $ex;
        } finally {
            $data->setMandatory($mandatory);
        }

        if ($exception instanceof ValidationException) {
            throw $exception;
        }
    }
}
