import { HandleAction } from '@metafox/framework';

export default function livestreamItemActions(handleAction: HandleAction) {
  return {
    updateViewer: () => handleAction('livestreaming/updateViewer'),
    removeViewer: () => handleAction('livestreaming/removeViewer'),
    deleteItem: () => handleAction('deleteItem'),
    updateStatusOffline: () =>
      handleAction('livestreaming/updateStatusOffline'),
    removeLiveWatching: () => handleAction('livestreaming/removeLiveWatching')
  };
}
