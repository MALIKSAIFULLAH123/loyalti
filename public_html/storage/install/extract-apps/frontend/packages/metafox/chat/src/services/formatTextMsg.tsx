import { URL_REGEX } from '../constants';
import { triggerClick } from '../utils';
import { escape } from 'lodash';

function nl2brSupport(str: string): string {
  return str.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br/>' + '$2');
}

const mentionLink = '<a onclick="triggerClick(\'$2\')">@$1</a>';

function generalSupport(msg: any, extra: any = {}) {
  const { mentions = [] } = extra;
  let result = msg.replace(/\[(\s)*\]\(([^\)]+)\)/gm, '');

  if (mentions && mentions.length > 0) {
    result = result.replace(/@([^\s\\#@:]+)/giu, (match, username) => {
      const user = mentions.find(x => x.username === username);

      return user && user.name ? `@${user.name}` : match;
    });
  }

  return result;
}

function mentionSupport(str: string, extra: any = {}) {
  const { mentions = [] } = extra;
  // eslint-disable no-console, no-control-regex
  let result = str.replace(/@\[([^\]]+)\]\(([^#@:]+)\)/giu, mentionLink);

  if (mentions && mentions.length > 0) {
    result = result.replace(/@([^\s\\#@:]+)/giu, (match, username) => {
      const user = mentions.find(x => x.username === username);

      return user && user.name
        ? `<a onclick="triggerClick(\'/${username}\')">@${user.name}</a>`
        : match;
    });
  }

  return result;
}

window.triggerClick = triggerClick;

function linkSupport(str: string): string {
  return str.replace(URL_REGEX, text => {
    const link = text.startsWith('http') ? text : `https://${text}`;

    return `<a href="${link}" target="_blank">${link}</a>`;
  });
}

const emojiSupport = (text: string) =>
  text.replace(/:\w+:/gi, (name: string): string => name);

export const formatGeneralMsg = (text: string, extra: any = {}) => {
  return text ? generalSupport(text, extra) : null;
};

function escapeHTMLSupport(msg) {
  return escape(msg);
}

export default function formatTextMsg(text: string, extra: any = {}): string {
  return text
    ? nl2brSupport(
        emojiSupport(
          mentionSupport(
            linkSupport(escapeHTMLSupport(generalSupport(text))),
            extra
          )
        )
      )
    : null;
}

export function formatLastMsg(lastMessage, user = null, isOwner = true) {
  const type = lastMessage?.type;

  if (['messageDelete', 'delete'].includes(type)) {
    let result = [
      {
        id: 'you_deleted_a_message'
      }
    ];

    if (!isOwner) {
      if (user) {
        result = [
          {
            id: 'user_message_is_removed',
            user: user.full_name || user.user_name
          }
        ];
      } else {
        result = [{ id: 'message_was_deleted' }];
      }
    }

    return result;
  }

  return lastMessage?.message
    ? nl2brSupport(
        emojiSupport(
          mentionSupport(
            escapeHTMLSupport(generalSupport(lastMessage?.message)),
            {
              mentions: []
            }
          )
        )
      )
    : null;
}
