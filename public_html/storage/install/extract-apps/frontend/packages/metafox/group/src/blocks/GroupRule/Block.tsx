/**
 * @type: block
 * name: group.manager.groupRule
 * title: Group - Manage - Rule
 * keywords: group
 * description:
 * thumbnail:
 * experiment: true
 */
import {
  connectItemView,
  connectSubject,
  createBlock,
  ListViewBlockProps
} from '@metafox/framework';
import Base from './Base';

const Enhancer = connectSubject(connectItemView(Base, () => {}));

export default createBlock<ListViewBlockProps>({
  name: 'GroupRule',
  extendBlock: Enhancer,
  overrides: {
    emptyPage: 'hide',
    headerActions: [{ as: 'group.addNewRuleButton' }],
    authRequired: true
  },
  defaults: {
    title: 'Group Rules',
    itemView: 'groupRule.itemView.mainCard',
    gridLayout: 'Group - Rule - Main Card',
    itemLayout: 'Group - Rule - Main Card',
    blockLayout: 'App List Pending Posts',
    contentType: 'group'
  }
});
