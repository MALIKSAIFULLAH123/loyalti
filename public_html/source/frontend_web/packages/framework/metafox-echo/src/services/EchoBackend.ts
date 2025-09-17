/**
 * @type: service
 * name: echoBackend
 */

import LaravelEcho from 'laravel-echo';
import 'pusher-js';
import { Manager } from '@metafox/framework/Manager';
import {
  BROADCAST_CONNECTIONS_PUSHER_KEY,
  BROADCAST_CONNECTIONS_PUSHER_OPTIONS_CLUSTER
} from '@metafox/framework';

class EchoBackend {
  public bootstrap(manager: Manager) {
    const KEY = BROADCAST_CONNECTIONS_PUSHER_KEY;
    const CLUSTER = BROADCAST_CONNECTIONS_PUSHER_OPTIONS_CLUSTER || 'ap1';

    return new LaravelEcho({
      broadcaster: 'pusher',
      key: KEY,
      cluster: CLUSTER,
      forceTLS: false
    });
  }
}
export default EchoBackend;
