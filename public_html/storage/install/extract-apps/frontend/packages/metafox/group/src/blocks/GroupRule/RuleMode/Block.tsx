/**
 * @type: block
 * name: group.manage.ruleMode
 * title: Group - Manage - Rule Mode
 * keywords: group
 * description: Manager Rule Mode
 * thumbnail:
 * experiment: true
 */
import {
  connectItemView,
  connectSubject,
  createBlock
} from '@metafox/framework';
import Base from './Base';

const Enhancer = connectSubject(connectItemView(Base, () => {}));

export default createBlock<any>({
  extendBlock: Enhancer,
  overrides: {
    blockLayout: 'App List Pending Posts',
    gridLayout: 'Group - Rule - Main Card',
    itemLayout: 'Group - Rule - Main Card',
    contentType: 'group',
    title: 'group_rules',
    showWhen: ['truthy', 'profile.extra.can_update_rule_confirmation']
  }
});
