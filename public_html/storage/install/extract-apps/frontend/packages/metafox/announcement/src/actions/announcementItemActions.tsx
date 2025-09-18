import { HandleAction } from '@metafox/framework';

export default function announcementItemActions(handleAction: HandleAction) {
  return {
    closeAnnouncement: onSuccess =>
      handleAction('announcement/close', { onSuccess })
  };
}
