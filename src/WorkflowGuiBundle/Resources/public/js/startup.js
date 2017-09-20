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

pimcore.registerNS("pimcore.plugin.workflowgui");

pimcore.plugin.workflowgui = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.workflowgui";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker) {
        var user = pimcore.globalmanager.get('user');
        var perspectiveCfg = pimcore.globalmanager.get("perspective");

        if(user.isAllowed("workflows") && perspectiveCfg.inToolbar("settings.workflows")) {
            var settingsMenu = new Ext.Action({
                text: t("workflows"),
                iconCls: "pimcore_icon_workflow",
                handler : this.showWorkflows
            });

            layoutToolbar.settingsMenu.add(settingsMenu);
        }
    },

    showWorkflows: function () {
        try {
            pimcore.globalmanager.get("workflows").activate();
        }
        catch (e) {
            pimcore.globalmanager.add("workflows", new pimcore.plugin.workflowgui.panel());
        }
    },
});

var workflowguiPlugin = new pimcore.plugin.workflowgui();

