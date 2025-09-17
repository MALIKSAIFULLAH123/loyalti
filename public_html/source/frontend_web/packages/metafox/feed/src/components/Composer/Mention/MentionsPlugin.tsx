import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';
import {
  LexicalTypeaheadMenuPlugin,
  MenuTextMatch,
  useBasicTypeaheadTriggerMatch
} from '@lexical/react/LexicalTypeaheadMenuPlugin';
import { TextNode, $isTextNode, $createTextNode } from 'lexical';
import { useCallback, useEffect, useMemo, useState } from 'react';
import * as React from 'react';
import { $createMentionNode } from './MentionNode';
import { Box, styled } from '@mui/material';
import { useEditorFocus } from '@metafox/lexical';
import { Popper } from '@metafox/ui';
import { useGlobal } from '@metafox/framework';

const Wrapper = styled(Box, {
  name: 'FeedMention',
  slot: 'wrapper'
})(({ theme }) => ({
  width: '300px',
  background: theme.palette.background.paper,
  position: 'relative',
  zIndex: theme.zIndex.tooltip - 1,
  boxShadow: theme.shadows[2],
  borderRadius: theme.shape.borderRadius
}));

const PUNCTUATION =
  '\\.,\\+\\*\\?\\$\\@\\|#{}\\(\\)\\^\\-\\[\\]\\\\/!%\'"~=<>_:;';
const NAME = `\\b[A-Z][^\\s${PUNCTUATION}]`;

const DocumentMentionsRegex = {
  NAME,
  PUNCTUATION
};

const PUNC = DocumentMentionsRegex.PUNCTUATION;

const TRIGGERS = ['@'].join('');

// Chars we expect to see in a mention (non-space, non-punctuation).
const VALID_CHARS = `[^${TRIGGERS}${PUNC}\\s]`;

// Non-standard series of chars. Each series must be preceded and followed by
// a valid char.
const VALID_JOINS =
  `${
    '(?:' +
    '\\.[ |$]|' + // E.g. "r. " in "Mr. Smith"
    ' |' + // E.g. " " in "Josh Duck"
    '['
  }${PUNC}]|` + // E.g. "-' in "Salier-Hellendag"
  ')';

const LENGTH_LIMIT = 75;

const AtSignMentionsRegex = new RegExp(
  `${'(^|\\s|\\()(' + '['}${TRIGGERS}]` +
    `((?:${VALID_CHARS}${VALID_JOINS}){0,${LENGTH_LIMIT}})` +
    ')$'
);

// 50 is the longest alias length limit.
const ALIAS_LENGTH_LIMIT = 50;

// Regex used to match alias.
const AtSignMentionsRegexAliasRegex = new RegExp(
  `${'(^|\\s|\\()(' + '['}${TRIGGERS}]` +
    `((?:${VALID_CHARS}){0,${ALIAS_LENGTH_LIMIT}})` +
    ')$'
);

// At most, 5 suggestions are shown in the popup.
const SUGGESTION_LIST_LENGTH_LIMIT = 5;
const SUGGESTION_LIST_LENGTH_LIMIT_MOBILE = 4;

const mentionsCache = new Map();

function useMentionLookupService(mentionString: string | null, onSearchChange) {
  const [results, setResults] = useState<Array<string>>([]);

  useEffect(() => {
    const cachedResults = mentionsCache.get(mentionString);

    if (mentionString == null) {
      setResults([]);

      return;
    }

    if (cachedResults === null) {
      return;
    } else if (cachedResults !== undefined) {
      setResults(cachedResults);

      return;
    }

    setResults([]);
    // mentionsCache.set(mentionString, null);
    onSearchChange({ value: mentionString }, newResults => {
      mentionsCache.set(mentionString, newResults);
      setResults(newResults);
    });
  }, [mentionString]);

  return results;
}

function checkForAtSignMentions(
  text: string,
  minMatchLength: number
): MenuTextMatch | null {
  let match = AtSignMentionsRegex.exec(text);

  if (match === null) {
    match = AtSignMentionsRegexAliasRegex.exec(text);
  }

  if (match !== null) {
    // The strategy ignores leading whitespace but we need to know it's
    // length to add it to the leadOffset
    const maybeLeadingWhitespace = match[1];

    const matchingString = match[3];

    if (matchingString.length >= minMatchLength) {
      return {
        leadOffset: match.index + maybeLeadingWhitespace.length,
        matchingString,
        replaceableString: match[2]
      };
    }
  }

  return null;
}

function getPossibleQueryMatch(text: string): MenuTextMatch | null {
  return checkForAtSignMentions(text, 0);
}

export default function NewMentionsPlugin(props): JSX.Element | null {
  const { entryComponent: AsEntryComponent, onSearchChange } = props;
  const { useIsMobile } = useGlobal();
  const [editor] = useLexicalComposerContext();
  const focusState = useEditorFocus();
  const [open, setOpen] = React.useState(true);
  const [queryString, setQueryString] = useState<string | null>(null);
  const results = useMentionLookupService(queryString, onSearchChange);
  const isMobile = useIsMobile();
  const [anchorEl, setAnchorEl] = useState<DOMRect | null>(null);
  const [focus, setFocus] = React.useState(false);
  const LIMIT_RESULT = isMobile
    ? SUGGESTION_LIST_LENGTH_LIMIT_MOBILE
    : SUGGESTION_LIST_LENGTH_LIMIT;

  const checkForSlashTriggerMatch = useBasicTypeaheadTriggerMatch('/', {
    minLength: 0
  });

  const options = useMemo(
    () => results.slice(0, LIMIT_RESULT),
    [results, LIMIT_RESULT]
  );

  React.useEffect(() => {
    updateCursorPosition();
  }, [results]);

  const updateCursorPosition = () => {
    const selection = window.getSelection();

    if (selection && selection.rangeCount > 0) {
      const range = selection.getRangeAt(0);
      const rect = range.getBoundingClientRect();
      setAnchorEl(rect);
    }
  };

  React.useEffect(() => {
    setFocus(focusState);
  }, [focusState]);

  React.useEffect(() => {
    updateCursorPosition();
    document.addEventListener('scroll', updateCursorPosition);

    return () => document.removeEventListener('scroll', updateCursorPosition);
  }, [queryString]);

  React.useEffect(() => {
    setTimeout(() => {
      setOpen(focus);
    }, 500);
  }, [focus]);

  const onSelectOption = useCallback(
    (selectedOption, nodeToReplace: TextNode | null, closeMenu: () => void) => {
      editor.update(() => {
        const mentionNode = $createMentionNode(selectedOption);

        if (nodeToReplace) {
          nodeToReplace.replace(mentionNode);
        }

        const parent = mentionNode.getParent();
        const nextSibling = mentionNode.getNextSibling();

        if (!nextSibling || !$isTextNode(nextSibling)) {
          const emptyTextNode = $createTextNode(' ');
          parent.append(emptyTextNode);
          emptyTextNode.select();
        } else {
          nextSibling.select();
        }

        closeMenu();
      });
    },
    [editor]
  );

  const checkForMentionMatch = useCallback(
    (text: string) => {
      const slashMatch = checkForSlashTriggerMatch(text, editor);

      if (slashMatch !== null) {
        return null;
      }

      return getPossibleQueryMatch(text);
    },
    [checkForSlashTriggerMatch, editor]
  );

  const configPopper = isMobile
    ? {
        placement: 'bottom-start'
      }
    : {
        placement: 'top-start'
      };

  return (
    <LexicalTypeaheadMenuPlugin
      onQueryChange={setQueryString}
      onSelectOption={onSelectOption}
      triggerFn={checkForMentionMatch}
      options={options}
      menuRenderFn={(
        anchorElementRef,
        { selectedIndex, selectOptionAndCleanUp, setHighlightedIndex }
      ) =>
        (anchorElementRef.current ? (
          <Popper
            anchorEl={{
              getBoundingClientRect: () => anchorEl || new DOMRect()
            }}
            open={open && results.length > 0}
            zIndex={999999}
            {...configPopper}
          >
            <Wrapper>
              {options.map((option, i: number) => (
                <AsEntryComponent
                  index={i}
                  isSelected={selectedIndex === i}
                  onClick={() => {
                    setHighlightedIndex(i);
                    selectOptionAndCleanUp(option);
                  }}
                  onMouseEnter={() => {
                    setHighlightedIndex(i);
                  }}
                  key={option.key}
                  mention={option}
                />
              ))}
            </Wrapper>
          </Popper>
        ) : null)
      }
    />
  );
}
