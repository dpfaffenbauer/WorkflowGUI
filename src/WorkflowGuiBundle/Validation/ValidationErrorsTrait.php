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

trait ValidationErrorsTrait
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    protected function setErrors(array $errors)
    {
        $this->errors = [];

        foreach ($errors as $error) {
            $this->addError($error);
        }
    }

    /**
     * @param string $error
     */
    protected function addError(string $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @return bool
     */
    protected function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
