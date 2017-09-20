<?php

namespace WorkflowGuiBundle\Controller;

use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/workflow")
 */
class WorkflowAdminController extends AdminController
{
    /**
     * @Route("/users")
     *
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function usersAction()
    {
        $this->checkPermission('workflow');

        $userList = new Model\User\Listing();
        $userList->load();

        $roleList = new Model\User\Role\Listing();
        $roleList->load();

        $allItems = array_merge($userList->getUsers(), $roleList->getRoles());
        $returnItems = [];

        foreach ($allItems as $item) {
            $returnItems[] = [
                "id" => $item->getId(),
                "text" => $item->getName(),
                "type" => $item->getType()
            ];
        }

        return $this->json($returnItems);
    }

    /**
     * @Route("/tree")
     *
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function treeAction()
    {
        $workflows = [];

        $list = new Model\Workflow\Listing();
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

        return $this->json($workflows);
    }

    /**
     * @Route("/get")
     *
     * @param Request $request
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function getAction(Request $request)
    {
        $id = $request->get('id');
        $workflow = Model\Workflow::getById($id);

        if ($workflow instanceof Model\Workflow) {
            return $this->json(['success' => true, 'workflow' => get_object_vars($workflow)]);
        }

        return $this->json(['success' => false]);
    }

    /**
     * @Route("/add")
     *
     * @param Request $request
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function addAction(Request $request)
    {
        $workflow = new Model\Workflow();
        $workflow->setName($request->get('name'));
        $workflow->save();

        return $this->json(['success' => true, "id" => $workflow->getId()]);
    }

    /**
     * @Route("/update")
     *
     * @param Request $request
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function updateAction(Request $request)
    {
        $id = $request->get('id');
        $data = $request->get('data');
        $workflow = Model\Workflow::getById($id);

        if (!$workflow instanceof Model\Workflow) {
            return $this->json(['success' => false]);
        }

        $data = $this->decodeJson($data);

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

        return $this->json(['success' => true, 'workflow' => get_object_vars($workflow)]);
    }

    /**
     * @Route("/delete")
     *
     * @param Request $request
     * @return \Pimcore\Bundle\AdminBundle\HttpFoundation\JsonResponse
     */
    public function deleteAction(Request $request)
    {
        $id = $request->get('id');
        $workflow = Model\Workflow::getById($id);

        if ($workflow instanceof Model\Workflow) {
            $workflow->delete();
        }

        return $this->json(['success' => true]);
    }
}
