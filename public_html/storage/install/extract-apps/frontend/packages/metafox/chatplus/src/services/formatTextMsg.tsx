import { escape, isEmpty } from 'lodash';
import { triggerClick } from '../utils';
import { URL_REGEX } from '../constants';

function nl2brSupport(str: string): string {
  return str.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br/>' + '$2');
}

const mentionLink = '<a onclick="triggerClick(\'$2\')">@$1</a>';

// support old version and new version
const mentionReg1 = /@([^\s\\#@:]+)/gimu;
// new version
const mentionReg2 =
  /\[(username|mention)=(\w+)\]([^[]+)\[\/(username|mention)\]/gimu;

function getTextMention(user: any, textRegex = '') {
  if (isEmpty(user)) return '';

  const result = '';

  if (user && (textRegex || user.name)) {
    return textRegex || `@${user.name}`;
  }

  return result;
}

function generalSupport(msg: any, extra: any = {}) {
  const { mentions = [], onlyShowText = false } = extra;
  const result = msg.replace(/\[(\s)*\]\(([^\)]+)\)/gm, '');

  if (mentions && mentions.length > 0) {
    if (mentionReg2.test(result)) {
      return result.replace(mentionReg2, (...rest) => {
        const [match, , username, textRegex] = rest;
        const user = mentions.find(x => x.username === username);

        const textMention = getTextMention(user, textRegex);

        if (/all|here/g.test(username)) {
          return textRegex;
        }

        if (onlyShowText) {
          return textMention ? textMention : match;
        }

        return textMention
          ? `<a href="${username}" data-lexical-mention="true">${textRegex}</a>`
          : match;
      });
    }

    if (mentionReg1.test(result)) {
      return result.replace(mentionReg1, (match, username) => {
        const user = mentions.find(x => x.username === username);

        const textMention = getTextMention(user);

        if (onlyShowText) {
          return textMention ? textMention : match;
        }

        return user && user.name
          ? `<a href="${username}" data-lexical-mention="true">@${user.name}</a>`
          : match;
      });
    }
  }

  return result;
}

function mentionSupport(str: string, extra: any = {}) {
  const { mentions = [] } = extra;
  // eslint-disable no-console, no-control-regex
  const result = str.replace(/@\[([^\]]+)\]\(([^#@:]+)\)/giu, mentionLink);

  if (mentions && mentions.length > 0) {
    if (mentionReg2.test(result)) {
      return result.replace(mentionReg2, (...rest) => {
        const [match, , username, name] = rest;
        const user = mentions.find(x => x.username === username);

        if (/all|here/g.test(username)) {
          return name;
        }

        return user && user.name
          ? `<a class="mention" onclick="triggerClick(\'/${username}\')">${name}</a>`
          : match;
      });
    }

    if (mentionReg1.test(result)) {
      return result.replace(mentionReg1, (match, username) => {
        const user = mentions.find(x => x.username === username);

        return user && user.name
          ? `<a class="mention" onclick="triggerClick(\'/${username}\')">@${user.name}</a>`
          : match;
      });
    }
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

export const isCallMessage = (type: any) =>
  /^(\w+)_(audio|video)_call_([cpd]$)/.test(type);

export function formatLastMsg(lastMessage) {
  const text = lastMessage?.msgRaw || lastMessage?.msg;

  return text
    ? nl2brSupport(
        emojiSupport(
          mentionSupport(escapeHTMLSupport(generalSupport(text)), {
            mentions: lastMessage?.mentions || []
          })
        )
      )
    : null;
}

export function formatTextCopy(message: any): string {
  return message?.msg
    ? generalSupport(message.msg, {
        mentions: message?.mentions || [],
        onlyShowText: true
      })
    : '';
}
