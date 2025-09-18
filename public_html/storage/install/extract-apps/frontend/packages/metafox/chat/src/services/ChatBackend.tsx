/**
 * @type: service
 * name: chatBackend
 */
import { Manager } from '@metafox/framework';

class ChatBackend {
  /**
   *
   */
  private manager: Manager;

  public bootstrap(manager: Manager) {
    this.manager = manager;

    return this;
  }

  public listenRoomNotify(user_id: string | number) {
    const { echoBackend, dispatch } = this.manager;

    echoBackend.channel(`user.${user_id}`).listen('.UserMessage', (e: any) => {
      dispatch({ type: 'chat/addMessage', payload: e });
    });

    echoBackend
      .channel(`user.${user_id}`)
      .listen('.MessageUpdate', (e: any) => {
        dispatch({ type: 'chat/updateMessage', payload: e });
      });

    echoBackend.channel(`user.${user_id}`).listen('.RoomUpdated', (e: any) => {
      dispatch({ type: 'chat/updateRoom', payload: { room: e } });
    });

    echoBackend
      .channel(`user.${user_id}`)
      .listen('.RoomUserBlocked', (e: any) => {
        dispatch({
          type: 'chat/updateRoom',
          payload: { room: e, type: 'block' }
        });
      });

    echoBackend
      .channel(`user.${user_id}`)
      .listen('.RoomUserUnblocked', (e: any) => {
        dispatch({
          type: 'chat/updateRoom',
          payload: { room: e, type: 'unBlock' }
        });
      });

    echoBackend
      .channel(`user.${user_id}`)
      .listen('.RoomUserDeleted', (e: any) => {
        dispatch({ type: 'chat/roomUserDeleted', payload: e });
      });

    echoBackend.channel(`user.${user_id}`).listen('.RoomDeleted', (e: any) => {
      dispatch({ type: 'chat/room/deleteRoomBackend', payload: e });
    });

    echoBackend
      .channel(`user.${user_id}`)
      .listen('.NotificationUpdate', (e: any) => {
        dispatch({ type: 'chat/notifications/updateUnread', payload: e });
      });
  }
}

export default ChatBackend;
