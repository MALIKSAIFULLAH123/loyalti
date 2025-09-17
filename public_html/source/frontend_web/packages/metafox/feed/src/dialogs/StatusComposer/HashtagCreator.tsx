/**
 * @type: ui
 * name: statusComposer.plugin.hashtag
 * lazy: false
 */

import { HashtagPlugin } from '@lexical/react/LexicalHashtagPlugin';

export default function HashtagCreator(plugins: any[]) {
  plugins.push(HashtagPlugin);
}
