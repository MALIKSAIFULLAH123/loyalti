/**
 * @type: block
 * name: group.manage.ruleList
 * title: Group - Manage - Rule List
 * keywords: group
 * description: Group - Manage - Rule List
 * thumbnail:
 * experiment: true
 */
import { createBlock, ListViewBlockProps } from '@metafox/framework';

export default createBlock<ListViewBlockProps>({
  name: 'GroupRule',
  extendBlock: 'core.block.listview',
  overrides: {
    headerActions: [{ as: 'group.addNewRuleButton' }],
    authRequired: true
  },
  defaults: {
    moduleName: 'group',
    resourceName: 'group_rule',
    actionName: 'viewAll',
    emptyPage: 'core.block.no_content_with_icon',
    title: 'set_up_group_rules',
    canLoadMore: true,
    itemView: 'groupRule.itemView.mainCard',
    blockLayout: 'App List Pending Posts',
    gridLayout: 'Group - Rule - Main Card',
    itemLayout: 'Group - Rule - Main Card',
    emptyPageProps: {
      title: 'no_rules_set_up',
      description: 'no_rules_set_up_description',
      image: 'ico-note-paper-o'
    }
  }
});
