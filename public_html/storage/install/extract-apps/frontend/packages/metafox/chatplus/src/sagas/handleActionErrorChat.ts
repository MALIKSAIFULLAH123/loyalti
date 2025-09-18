import { getGlobalContext } from '@metafox/framework';
import { isNumber } from 'lodash';

export default function* handleActionErrorChat(error: any) {
  if (!error) return;

  const {
    error: errorProp,
    reason,
    message: messageProp,
    details = {}
  } = error;
  const { i18n, dialogBackend } = yield* getGlobalContext();

  if (isNumber(errorProp)) return null;

  const title = i18n.formatMessage({ id: 'oops' });

  const errId = errorProp?.replace(/-/g, '_');

  let translatedMsg = errId
    ? i18n.formatMessage({ id: errId }, { ...details })
    : messageProp || i18n.formatMessage({ id: 'error_not_allowed' });

  if (
    errId &&
    translatedMsg &&
    translatedMsg === errId &&
    /_/g.test(translatedMsg)
  ) {
    if (details?.action) {
      translatedMsg = i18n.formatMessage({
        id: `${errId}_${details.action.toLowerCase()}`
      });
    } else if (reason) {
      translatedMsg = reason;
    }
  }

  yield dialogBackend.alert({
    title: i18n.formatMessage({ id: title }),
    message: translatedMsg
  });
}
