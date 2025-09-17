/**
 * @type: block
 * name: user.block.userProfileAboutOverview
 * title: User Profile About
 * keywords: user, profile
 * description: Display user information in profile page.
 * thumbnail:
 */
import {
  connectItemView,
  connectSubject,
  createBlock
} from '@metafox/framework';
import Base, { Props } from './Base';

const EnhanceBlock = connectSubject(connectItemView(Base, () => {}));

export default createBlock<Props>({
  name: 'UserProfileHeaderBlock',
  extendBlock: EnhanceBlock,
  defaults: {
    sectionName: 'basic_info'
  },
  custom: {
    section: {
      margin: 'normal',
      name: 'sectionName',
      title: 'text',
      label: 'Custom Group',
      component: 'Text',
      fullWidth: true,
      required: true,
      description: 'Fill profile custom group name, etc: basic_info, about, ...'
    }
  }
});
