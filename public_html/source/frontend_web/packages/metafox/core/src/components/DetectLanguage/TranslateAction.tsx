/**
 * @type: ui
 * name: core.ui.detectLanguageButton
 * chunkName: feed
 */

import { useGlobal, useSession } from '@metafox/framework';
import { Box, styled, Link, CircularProgress } from '@mui/material';
import React from 'react';
import {
  CORE_LANGUAGE_DETECT_ACTION,
  CORE_LANGUAGE_TRANSLATE_ACTION
} from '@metafox/core/constant';
import { stripTags } from '@metafox/utils';

const name = 'DetectLanguageButton';

const regexUrl =
  /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)/gm;

const Wrapper = styled(Box, {
  name,
  slot: 'wrapper',
  overridesResolver(props, styles) {
    return [styles.wrapper];
  },
  shouldForwardProp: props => props !== 'active'
})<{ active?: boolean }>(({ theme, active }) => ({
  display: 'block',
  ...(active && {
    borderLeft: theme.mixins.border('secondary'),
    borderWidth: '3px',
    paddingLeft: theme.spacing(2)
  })
}));

const Button = styled(Link, {
  name,
  slot: 'buttonTranslate',
  overridesResolver(props, styles) {
    return [styles.buttonTranslate];
  }
})(({ theme }) => ({
  display: 'inline-flex',
  color: `${theme.palette.text.secondary} !important`,
  ...theme.typography.body2
}));

function getText(html) {
  // Remove HTML tags using a regular expression
  const cleanText = stripTags(html).replace(regexUrl, '');
  // Count the length of the remaining text
  const length = cleanText?.replace(/\s/g, '').length;

  return { length, cleanText };
}

const MIN_LENGTH = 20;

function DetectLanguageButton(props) {
  const { text, identity, onTranslateSuccess, sx, TranslateView } = props;
  const { i18n, dispatch, getSetting } = useGlobal();
  const session = useSession();
  const { user: authUser } = session;
  const enableApp = getSetting('translation.enable_translate');
  const [enable, setEnable] = React.useState(false);
  const [result, setResult] = React.useState();
  const [loading, setLoading] = React.useState(false);
  const [toggle, setToggle] = React.useState(true);
  const targetLanguage = authUser?.language_id;

  React.useEffect(() => {
    if (!targetLanguage) return;

    const { length, cleanText } = getText(text);

    if (length < MIN_LENGTH) return;

    dispatch({
      type: CORE_LANGUAGE_DETECT_ACTION,
      payload: { text: cleanText },
      meta: {
        onSuccess: result => {
          if (result) {
            setEnable(targetLanguage !== result);
          }
        }
      }
    });
  }, []);

  const handleToggle = () => {
    setToggle(prev => !prev);
  };

  const handleClick = () => {
    if (loading) return;

    dispatch({
      type: CORE_LANGUAGE_TRANSLATE_ACTION,
      payload: { identity },
      meta: {
        onStart: () => {
          setLoading(true);
        },
        onSuccess: result => {
          setResult(result);
          onTranslateSuccess && onTranslateSuccess(result);
        },
        onFinally: () => {
          setLoading(false);
        }
      }
    });
  };

  if (!enableApp) return null;

  if (!enable || !identity || !targetLanguage) return null;

  return (
    <Wrapper sx={sx} active={!!(result && toggle)}>
      {toggle && result ? <TranslateView result={result} /> : null}
      <Box sx={{ display: 'flex', alignItems: 'center' }} mt={1}>
        <Button
          data-testid="buttonTranslate"
          role="button"
          onClick={result ? handleToggle : handleClick}
        >
          {i18n.formatMessage({
            id: result && toggle ? 'hide_translation' : 'view_translation'
          })}
        </Button>
        {loading ? (
          <CircularProgress
            sx={{
              marginLeft: '4px',
              color: theme => theme.palette.text.secondary
            }}
            size={12}
          />
        ) : null}
      </Box>
    </Wrapper>
  );
}

export default DetectLanguageButton;
