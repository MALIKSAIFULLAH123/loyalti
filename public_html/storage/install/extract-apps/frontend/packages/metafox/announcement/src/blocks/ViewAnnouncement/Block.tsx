/**
 * @type: block
 * name: announcement.block.ViewAnnouncement
 * title: View Announcement Detail
 * keywords: announcement
 * description: Display announcement detail.
 */

import { connectSubject, createBlock } from '@metafox/framework';
import {
  actionCreators,
  connectItemView
} from '../../hocs/connectAnnouncementItem';
import Base from './Base';

const Enhance = connectSubject(connectItemView(Base, actionCreators, {}));

export default createBlock({
  extendBlock: Enhance
});
