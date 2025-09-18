/**
 * @type: block
 * name: event.block.invitationCard
 * title: Event Invitations
 * keywords: event
 * description: Display event invitations.
 */
import inviteItemActions from '@metafox/event/actions/inviteItemActions';
import {
  connectItemView,
  connectSubject,
  createBlock
} from '@metafox/framework';
import Base from './Base';

const Enhance = connectSubject(connectItemView(Base, inviteItemActions));

const EventInvitationBlock = createBlock({
  extendBlock: Enhance,
  overrides: {
    contentType: 'event'
  }
});

export default EventInvitationBlock;
