import { AppResourceAction } from '@metafox/framework';
import { call } from 'redux-saga/effects';
import getGlobalContext from './getGlobalContext';
import { isString } from 'lodash';

export default function* handleActionConfirm(
  config: AppResourceAction,
  extraData?: Record<string, any>
): Generator<unknown, boolean, unknown> {
  if (!config?.confirm) return true;

  let ok: boolean = true;

  const { dialogBackend, i18n, compactData } = yield* getGlobalContext();

  let { confirm } = config;

  if (confirm) {
    if (confirm === true) {
      confirm = {
        title: 'Are you sure?',
        message: 'This action could not rollback!'
      };
    }

    const {
      title: titlePhrase,
      message: messagePhrase,
      positiveButton = null,
      negativeButton = null,
      phraseParams
    } = confirm;
    let title = titlePhrase;
    let message = messagePhrase;

    if (extraData && phraseParams) {
      title =
        isString(titlePhrase) && titlePhrase
          ? i18n.formatMessage(
              {
                id: titlePhrase
              },
              compactData(phraseParams, extraData)
            )
          : titlePhrase;

      message =
        isString(messagePhrase) && messagePhrase
          ? i18n.formatMessage(
              {
                id: messagePhrase
              },
              compactData(phraseParams, extraData)
            )
          : messagePhrase;
    }

    ok = yield call(dialogBackend.confirm, { title, message, positiveButton, negativeButton });
  } else if (config.alert) {
    const { title, message } = config.alert;
    yield dialogBackend.alert({ title, message });
  }

  return ok;
}
