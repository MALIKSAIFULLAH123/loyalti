/**
 * @type: ui
 * name: statusComposer.plugin.linkify
 * lazy: false
 */

import {
  AutoLinkPlugin,
  createLinkMatcherWithRegExp
} from '@lexical/react/LexicalAutoLinkPlugin';
import * as React from 'react';

const URL_REGEX =
  // eslint-disable-next-line max-len
  /((https?:\/\/(www\.)?)|(www\.))[-a-zA-Z0-9@:%._+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}([^\s]*[\w@?^=%&/~+#-])?/u;

// const EMAIL_REGEX =
//   // eslint-disable-next-line max-len
//   /(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/;

const MATCHERS = [
  createLinkMatcherWithRegExp(URL_REGEX, text => {
    return text.startsWith('http') ? text : `https://${text}`;
  })
  // createLinkMatcherWithRegExp(EMAIL_REGEX, text => {
  //   return `mailto:${text}`;
  // })
];

function LexicalAutoLinkPlugin({ handlePreviewLink }): JSX.Element {
  return <AutoLinkPlugin matchers={MATCHERS} onChange={handlePreviewLink} />;
}

export default function Linkify(plugins) {
  plugins.push(LexicalAutoLinkPlugin);
}
