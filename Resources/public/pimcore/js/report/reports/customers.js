/*
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GPLv3 and CCL
 *
 */

pimcore.registerNS('coreshop.report.reports.customers');
coreshop.report.reports.customers = Class.create(coreshop.report.abstract, {

    reportType: 'customers',

    getName: function () {
        return t('coreshop_report_customers');
    },

    getIconCls: function () {
        return 'coreshop_icon_customer';
    },

    getStoreFields: function () {
        return [
            {name: 'emailAddress', type: 'string'},
            {name: 'orderCount', type: 'integer'},
            {name: 'sales', type: 'number'}
        ];
    },

    showPaginator: function () {
        return true;
    },

    getGrid: function () {
        return new Ext.Panel({
            layout: 'fit',
            height: 275,
            items: {
                xtype: 'grid',
                store: this.getStore(),
                columns: [
                    {
                        text: t('email'),
                        dataIndex: 'emailAddress',
                        flex: 3
                    },
                    {
                        text: t('coreshop_report_customers_count'),
                        dataIndex: 'orderCount',
                        flex: 1,
                        align: 'right'
                    },
                    {
                        text: t('coreshop_report_customers_sales'),
                        dataIndex: 'sales',
                        flex: 1,
                        align: 'right',
                        renderer: function (value, metadata, record) {
                            return record.get('salesFormatted');
                        }
                    }
                ]
            }
        });
    }
});
