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

pimcore.registerNS("pimcore.plugin.workflowgui.validation");
pimcore.plugin.workflowgui.validation = Class.create({

    initialize: function (parent, record) {
        this.parent = parent;
        this.record = record;
    },

    getWindow: function () {

        if (!this.window) {
            this.window = new Ext.window.Window({
                width: 800,
                height: 700,
                modal: true,
                resizeable: true,
                layout: 'fit',
                title: t('workflow_validation_rules'),
                items: [
                    {
                        xtype: 'panel',
                        border: false,
                        layout: 'border',
                        items: [
                            this.getClassesPanel(),
                            this.getValidationPanel()
                        ]
                    }
                ]
            });
        }

        return this.window;
    },

    getClassesPanel: function () {

        if (!this.classesPanel) {
            var children = [];
            var settings = this.parent.getSettingsPanel().getForm().getFieldValues();

            if (settings.types.includes('object')) {
                for (var i = 0; i < settings.classes.length; i++) {
                    var classId = settings.classes[i];
                    var record = this.getObjectTypesStore().getById(classId);
                    if (record) {
                        children.push({
                            id: record.get('id'),
                            text: record.get('text'),
                            icon: record.get('icon'),
                            leaf: true
                        });
                    }
                }

                children.sort(function (a, b) {
                    return a.text > b.text;
                });
            }

            var store = Ext.create('Ext.data.TreeStore', {
                root: {
                    id: 0,
                    expanded: true,
                    children: children
                }
            });

            this.classesPanel = Ext.create('Ext.tree.Panel', {
                store: store,
                rootVisible: false,
                region: 'west',
                autoScroll: true,
                animate: false,
                containerScroll: true,
                width: 200,
                split: true,
                listeners: {
                    itemclick: this.loadValidationRules.bind(this)
                }
            });
        }

        return this.classesPanel;
    },

    getValidationPanel: function () {

        if (!this.validationPanel) {
            this.validationPanel = Ext.create('Ext.panel.Panel', {
                region: 'center',
                layout: 'fit'
            });
        }

        return this.validationPanel;
    },

    loadValidationRules: function (panel, record, item, index, e, eOpts) {
        var classId = record.get('id');

        if (!Ext.getCmp('workflow_validation_rules_panel_' + classId)) {
            this.getValidationPanel().removeAll(true);
            this.getValidationPanel().setLoading(true);

            Ext.Ajax.request({
                url: '/admin/class/get',
                params: {
                    id: classId
                },
                success: this.addValidationRulesPanel.bind(this, classId)
            });
        }
    },

    addValidationRulesPanel: function (classId, response) {
        var data = Ext.decode(response.responseText);

        var validationRulesPanel = Ext.create('Ext.tree.Panel', {
            id: 'workflow_validation_rules_panel_' + classId,
            autoScroll: true,
            root: {
                id: "0",
                root: true,
                text: t("base"),
                leaf: true,
                iconCls: "pimcore_icon_class",
                isTarget: true
            },
            listeners: {
                checkchange: function (node, checked, eOpts) {
                    this.save(classId, validationRulesPanel.getChecked());
                }.bind(this)
            }
        });

        this.getValidationPanel().setLoading(false);
        this.getValidationPanel().add(validationRulesPanel);

        if (data.layoutDefinitions) {
            if (data.layoutDefinitions.childs) {
                for (var i = 0; i < data.layoutDefinitions.childs.length; i++) {
                    validationRulesPanel.getRootNode().appendChild(
                        this.recursiveAddNode(
                            data.layoutDefinitions.childs[i],
                            this.getChecked(classId),
                            validationRulesPanel.getRootNode()
                        )
                    );
                }
                validationRulesPanel.getRootNode().expand();
            }
        }
    },

    recursiveAddNode: function (con, checked, scope) {
        var fn = null;
        var newNode = null;

        if (con.datatype == "layout") {
            fn = this.addLayoutChild.bind(scope, con.fieldtype, con);
        } else if (con.datatype == "data") {
            fn = this.addDataChild.bind(scope, con.fieldtype, con, checked);
        }

        newNode = fn();

        if (con.childs) {
            for (var i = 0; i < con.childs.length; i++) {
                this.recursiveAddNode(con.childs[i], checked, newNode);
            }
        }

        return newNode;
    },

    addLayoutChild: function (type, initData) {

        var nodeLabel = t(type);

        if (initData) {
            if (initData.name) {
                nodeLabel = initData.name;
            }
        }

        var newNode = {
            text: nodeLabel,
            value: nodeLabel,
            type: "layout",
            iconCls: "pimcore_icon_" + type,
            leaf: false,
            expandable: false,
            expanded: true
        };

        newNode = this.appendChild(newNode);

        //to hide or show the expanding icon depending if childs are available or not
        newNode.addListener('remove', function (node, removedNode, isMove) {
            if (!node.hasChildNodes()) {
                node.set('expandable', false);
            }
        });

        newNode.addListener('append', function (node) {
            node.set('expandable', true);
        });

        this.expand();

        return newNode;
    },

    addDataChild: function (type, initData, checked) {

        var nodeLabel = t(type);

        if (initData) {
            if (initData.name) {
                nodeLabel = initData.name;
            }
        }

        var prefix = '';
        if (!this.data.root && this.data.type == 'data') {
            prefix = this.data.value + '.';
        }

        var newNode = {
            text: nodeLabel,
            value: prefix + nodeLabel,
            type: "data",
            leaf: true,
            iconCls: "pimcore_icon_" + type
        };

        if (type === "block" || type == "localizedfields") {
            newNode.leaf = false;
            newNode.expanded = true;
            newNode.expandable = false;
        } else {
            newNode.checked = false;
        }

        if (checked.includes(newNode.value)) {
            newNode.checked = true;
        }

        newNode = this.appendChild(newNode);

        this.expand();

        return newNode;
    },

    getChecked: function (classId) {
        var validation = this.record.get('validation');
        for (var i = 0; i < validation.length; i++) {
            if (validation[i].classId == classId) {
                return validation[i].rules;
            }
        }
        return [];
    },

    save: function (classId, checked) {
        var rules = [];
        for (var i = 0; i < checked.length; i++) {
            rules.push(checked[i].get('value'));
        }

        var validation = this.record.get('validation');
        for (var i = 0; i < validation.length; i++) {
            if (validation[i].classId == classId) {
                validation[i].rules = rules;
                this.record.set('validation', validation);
                break;
            }
        }
    },

    show: function () {
        this.getWindow().show();
    },

    getObjectTypesStore: function () {

        if (!this.objectTypeStore) {
            this.objectTypeStore = pimcore.globalmanager.get("object_types_store");
        }

        return this.objectTypeStore;
    }
});
