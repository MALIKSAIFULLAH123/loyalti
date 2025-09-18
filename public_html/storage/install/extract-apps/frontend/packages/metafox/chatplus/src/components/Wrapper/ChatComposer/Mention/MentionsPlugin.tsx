import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';
import {
  MenuTextMatch,
  LexicalTypeaheadMenuPlugin,
  useBasicTypeaheadTriggerMatch
} from '@lexical/react/LexicalTypeaheadMenuPlugin';
import { TextNode } from 'lexical';
import { useCallback, useEffect, useState } from 'react';
import * as React from 'react';
import { $createMentionNode } from './MentionNode';
import { Paper, styled } from '@mui/material';
import { useEditorFocus } from '@metafox/lexical';
import { Popper } from '@metafox/ui';
import { isEmpty } from 'lodash';
import { useGlobal } from '@metafox/framework';
import { isIOS } from '@metafox/utils';
import './base.css';

const PopperWrapper = styled(Popper, {
  name: 'PopperWrapper'
})<{}>(({ theme }) => ({
  maxWidth: '300px',
  minWidth: '240px',
  background: theme.palette.background.paper,
  position: 'relative',
  zIndex: theme.zIndex.tooltip - 1,
  borderRadius: theme.shape.borderRadius
}));

const PaperMenu = styled(Paper, {
  name: 'MuiActionMenu-menu'
})<{}>(({ theme }) => ({
  padding: theme.spacing(1, 0)
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

const CAN_USE_DOM =
  typeof window !== 'undefined' &&
  typeof window.document !== 'undefined' &&
  typeof window.document.createElement !== 'undefined';

const IS_MOBILE = CAN_USE_DOM && window.matchMedia('(pointer: coarse)').matches;

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
  const {
    entryComponent: AsEntryComponent,
    onSearchChange,
    initData = [],
    rid,
    sx
  } = props;
  const { usePageParams } = useGlobal();
  const pageParams = usePageParams();
  const [editor] = useLexicalComposerContext();
  const focusState = useEditorFocus();
  const [open, setOpen] = React.useState(true);
  const [queryString, setQueryString] = useState<string | null>(null);
  const dataSearch = useMentionLookupService(queryString, onSearchChange);
  const [anchorEl, setAnchorEl] = useState<DOMRect | null>(null);
  const [focus, setFocus] = React.useState(false);
  const refPopper = React.useRef();

  const checkForSlashTriggerMatch = useBasicTypeaheadTriggerMatch('/', {
    minLength: 0
  });

  const results = React.useMemo(() => {
    if (isEmpty(queryString)) return initData;

    return dataSearch;
  }, [queryString, dataSearch, initData]);

  const openValue = React.useMemo(() => {
    const result = open && results.length > 0;

    if (pageParams?.isAllPageMessages) {
      if (pageParams?.rid === rid) return result;

      return false;
    }

    return result;
  }, [
    rid,
    open,
    results.length,
    pageParams?.isAllPageMessages,
    pageParams?.rid
  ]);

  const updateCursorPosition = () => {
    const selection = window.getSelection();

    if (selection && selection.rangeCount > 0) {
      const range = selection.getRangeAt(0);
      const rect = range.getBoundingClientRect();
      setAnchorEl(rect);
    }
  };

  React.useEffect(() => {
    updateCursorPosition();
  }, [queryString]);

  React.useEffect(() => {
    setFocus(focusState);
  }, [focusState]);

  React.useEffect(() => {
    if (isIOS && IS_MOBILE) {
      window.document.body.style.position = focus ? 'fixed' : 'static';
      setOpen(focus);

      return;
    }

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

        mentionNode.select();
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

  React.useEffect(() => {
    if (!isIOS || !IS_MOBILE) return;

    const handleTouch = event => {
      if (
        IS_MOBILE &&
        refPopper.current &&
        !refPopper.current.contains(event.target as Node)
      ) {
        editor.blur();
      }
    };

    document.addEventListener('scroll', updateCursorPosition);

    // Add event listener
    document.addEventListener('touchstart', handleTouch);

    // Cleanup on unmount
    return () => {
      if (isIOS && IS_MOBILE) {
        document.removeEventListener('touchstart', handleTouch);
        document.removeEventListener('scroll', updateCursorPosition);
      }
    };
  }, []);

  const preventBodyScroll = (event: TouchEvent) => {
    const target = refPopper.current;

    if (!target) return;

    const { scrollTop, scrollHeight, clientHeight } = target;
    const atTop = scrollTop === 0;
    const atBottom = scrollTop + clientHeight === scrollHeight;

    if (
      (atTop && event.touches[0].clientY > event.touches[0].pageY) || // Scrolling up at the top
      (atBottom && event.touches[0].clientY < event.touches[0].pageY) // Scrolling down at the bottom
    ) {
      event.preventDefault(); // Prevent the body from scrolling
    }
  };

  React.useEffect(() => {
    if (!isIOS) return;

    const target = refPopper.current;

    if (target) {
      target.addEventListener('touchstart', () => {}, { passive: false });
      target.addEventListener('touchmove', preventBodyScroll, {
        passive: false
      });
    }

    return () => {
      if (target) {
        target.removeEventListener('touchstart', () => {});
        target.removeEventListener('touchmove', preventBodyScroll);
      }
    };
  }, []);

  return (
    <LexicalTypeaheadMenuPlugin
      onQueryChange={setQueryString}
      onSelectOption={onSelectOption}
      triggerFn={checkForMentionMatch}
      options={results}
      anchorClassName="chat-mention-lexical"
      menuRenderFn={(
        anchorElementRef,
        { selectedIndex, selectOptionAndCleanUp, setHighlightedIndex }
      ) => {
        return (
          <PopperWrapper
            anchorEl={{
              getBoundingClientRect: () => anchorEl || new DOMRect()
            }}
            open={openValue}
            zIndex={999999}
            placement="top-start"
            popperOptions={{
              strategy: 'fixed'
            }}
          >
            <PaperMenu ref={refPopper} sx={sx}>
              {results.map((option, i: number) => (
                <AsEntryComponent
                  index={i}
                  isSelected={selectedIndex === i}
                  onClick={() => {
                    setHighlightedIndex(i);
                    selectOptionAndCleanUp({
                      ...option,
                      name: `@${option.name}`
                    });
                  }}
                  onMouseEnter={() => {
                    setHighlightedIndex(i);
                  }}
                  key={option.key}
                  mention={option}
                />
              ))}
            </PaperMenu>
          </PopperWrapper>
        );
      }}
    />
  );
}
