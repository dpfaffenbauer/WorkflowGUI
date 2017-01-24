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
 * @copyright  Copyright (c) 2015-2017 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://github.com/dpfaffenbauer/pimcore-WorkflowGui/blob/master/LICENSE.md     GNU General Public License version 3 (GPLv3)
 */

use Pimcore\Model\Workflow;

class WorkflowGui_WorkflowSettingsController extends \Pimcore\Controller\Action\Admin\Element
{
    public function preDispatch()
    {
        parent::preDispatch();

        $this->checkPermission("workflows");
    }

    public function usersAction() {
        $userList = new \Pimcore\Model\User\Listing();
        $userList->load();

        $roleList = new \Pimcore\Model\User\Role\Listing();
        $roleList->load();

        $allItems = array_merge($userList->getUsers(), $roleList->getRoles());
        $returnItems = [];

        foreach($allItems as $item) {
            $returnItems[] = [
                "id" => $item->getId(),
                "text" => $item->getName(),
                "type" => $item->getType()
            ];
        }

        $this->_helper->json($returnItems);
    }

    public function treeAction()
    {
        $workflows = [];

        $list = new Workflow\Listing();
        $list->load();

        $items = $list->getWorkflows();

        foreach ($items as $item) {
            $workflows[] = [
                "id" => $item->getId(),
                "text" => $item->getName(),
                "leaf" => true,
                "iconCls" => "pimcore_icon_workflow"
            ];
        }

        $this->_helper->json($workflows);
    }

    public function getAction() {
        $id = $this->getParam("id");
        $workflow = Workflow::getById($id);

        if($workflow instanceof Workflow) {
            $this->_helper->json(['success' => true, 'workflow' => get_object_vars($workflow)]);
        }

        $this->_helper->json(['success' => false]);
    }

    public function addAction() {
        $workflow = new Workflow();
        $workflow->setName($this->getParam("name"));
        $workflow->save();

        $this->_helper->json(['success' => true, "id" => $workflow->getId()]);
    }

    public function updateAction() {
        $id = $this->getParam("id");
        $data = $this->getParam("data");
        $workflow = Workflow::getById($id);

        if(!$workflow instanceof Workflow) {
            $this->_helper->json(['success' => false]);
        }

        $data = \Zend_Json::decode($data);

        $classes = $data['settings']['classes'];
        $types = $data['settings']['types'];
        $assetTypes = $data['settings']['assetTypes'];
        $documentTypes = $data['settings']['documentTypes'];

        $workflowSubject = [
            "types" => $types,
            "classes" => $classes,
            "assetTypes" => $assetTypes,
            "documentTypes" => $documentTypes
        ];

        $workflow->setValues($data['settings']);
        $workflow->setWorkflowSubject($workflowSubject);
        $workflow->setStates($data['states']);
        $workflow->setStatuses($data['statuses']);
        $workflow->setActions($data['actions']);
        $workflow->setTransitionDefinitions($data['transitionDefinitions']);
        $workflow->save();

        $this->_helper->json(['success' => true, 'workflow' => get_object_vars($workflow)]);
    }

    public function deleteAction()
    {
        $id = $this->getParam("id");
        $workflow = Workflow::getById($id);

        if($workflow instanceof Workflow) {
            $workflow->delete();
        }

        $this->_helper->json(['success' => true]);
    }

    public function testAction() {
        include PIMCORE_DOCUMENT_ROOT . "/update/4016/postupdate.php";

        exit;
    }
}