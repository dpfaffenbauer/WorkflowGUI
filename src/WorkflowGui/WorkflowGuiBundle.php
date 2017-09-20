<?php

namespace WorkflowGuiBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class WorkflowGuiBundle extends AbstractPimcoreBundle
{
    public function getJsPaths()
    {
        return [
            '/bundles/workflowgui/js/startup.js',
            '/bundles/workflowgui/js/workflow/panel.js',
            '/bundles/workflowgui/js/workflow/item.js'
        ];
    }

    public function getCssPaths()
    {
        return [
            '/bundles/workflowgui/css/workflow.css'
        ];
    }
}
