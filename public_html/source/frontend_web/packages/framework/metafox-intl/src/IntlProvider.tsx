/**
 * @type: service
 * name: IntlProvider
 */
import { MFOX_LOCALE, useGlobal } from '@metafox/framework';
import { detectBrowserLanguage } from '@metafox/utils';
import React from 'react';
import { createIntl, createIntlCache, RawIntlProvider } from 'react-intl';
import defaultRichTextElements from './defaultRichTextElements';

const cache = createIntlCache();
const icuRegex = /(?:\{[^{}]+\}|:\w+)/g;

export default function IntlProvider({ children }) {
  const {
    usePreference,
    manager,
    getSetting,
    useIntlMessages = () => ({}),
    moment,
    useSession
  } = useGlobal();
  const { userLanguage } = usePreference();
  const supports = getSetting<object>('localize.languages');
  const rawDisplay = getSetting('localize.display_translation_key');
  // const messages = getConfig<Messages>('messages');
  const messages = useIntlMessages();
  const defaultLocale = MFOX_LOCALE;
  const { user } = useSession();
  const { language_id } = user || {};

  const locale =
    language_id ||
    userLanguage ||
    detectBrowserLanguage(supports) ||
    MFOX_LOCALE;
  const intl = createIntl(
    {
      locale,
      defaultLocale,
      messages,
      defaultRichTextElements,
      onWarn: (warning: string) => {},
      onError: err => {
        // console.warn(err);
      }
    },
    cache
  );
  const fn = intl.formatMessage;

  intl.formatMessage = (descriptor, ...args) => {
    const result = fn(descriptor, ...args);

    if (!rawDisplay && icuRegex.test(result) && descriptor?.id !== result) {
      return fn({ defaultMessage: ' ', ...descriptor }, ...args);
    }

    return result;
  };
  manager.use({ i18n: intl });
  moment.locale([locale, defaultLocale].filter(Boolean));

  return <RawIntlProvider value={intl}>{children}</RawIntlProvider>;
}
