/**
 * @type: siteDock
 * name: AlertReminder
 */

import { useGlobal } from '@metafox/framework';
import React from 'react';
import { get, last } from 'lodash';

const name = 'AlertReminder';

const checkEnabled = (x, y) => {
  if (!x) return false;

  if (!y) return true;

  return x.toString() !== y.toString();
};

type AlertConfig = {
  id: number;
  title: string;
  description: string;
  primary_action: Record<string, any>;
  reminders: Array<any>;
  dialogProps: Record<string, any>;
  extra: Record<string, any>;
  as?: string;
  skipDismiss: boolean;
};

const CachedHideTempForAction = 'AlertReminderHideTempForAction';
const CachedDismiss = 'AlertReminderDismiss';

export default function AlertReminder() {
  const {
    i18n,
    useSession,
    dispatch,
    localStore,
    cookieBackend,
    dialogBackend,
    moment
  } = useGlobal();
  const { user, meta } = useSession();
  const data = meta?.pending_actions;
  const getId = id => `${name}_${id}`;
  const hideTemp = cookieBackend.get(CachedHideTempForAction) === '1';

  const handleDismiss = ({ id, reminders, skipDismiss }) => {
    if (skipDismiss) return;

    const { valueCached } = getCachedDismiss(id, reminders);
    const data = (localStore.getJSON(CachedDismiss) || {}) as Object;
    const userData = get(data, user?.id) || {};
    userData[id] = valueCached;
    localStore.set(
      CachedDismiss,
      JSON.stringify({ ...data, [user?.id]: userData })
    );
  };

  const hideTempForAction = () => {
    // 10s
    const time = new Date(new Date().getTime() + 10 * 1000);
    cookieBackend.set(CachedHideTempForAction, '1', { expires: time });
  };

  const handleAction = item => {
    const { primary_action, skipDismiss, id } = item;
    const { action } = primary_action || {};

    if (data.length > 1 || !skipDismiss) {
      dialogBackend.dismissById(getId(id));
    }

    handleDismiss(item);
    hideTempForAction();

    if (action?.type) {
      dispatch(action);
    }
  };

  const getCachedDismiss = (id, reminders) => {
    try {
      const cachedTime = get(
        localStore.getJSON(CachedDismiss),
        `${user?.id}.${id}`
      );
      let enabled = true;
      let valueCached = 1;

      if (reminders?.length > 0) {
        const remindersUnix: number[] = reminders
          .map(x => moment(x).unix())
          .sort((a, b) => a - b);
        const now = moment().unix();
        const time =
          remindersUnix.find(x => x >= now) || last(remindersUnix) + 1;
        enabled = checkEnabled(time, cachedTime);

        valueCached = time;
      } else {
        enabled = !cachedTime;
      }

      return { enabled, valueCached };
    } catch (error) {
      return {};
    }
  };

  const dataFilters = data?.filter(({ id, reminders }) => {
    const { enabled } = getCachedDismiss(id, reminders);

    return enabled;
  });

  const handleDismissAll = React.useCallback(() => {
    if (!dataFilters.length) return;

    dataFilters.forEach(x => {
      handleDismiss(x);
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const presentAlert = React.useCallback((item: AlertConfig) => {
    const {
      id,
      title,
      description,
      primary_action,
      dialogProps = {},
      extra = {},
      as
    } = item;

    dialogBackend.present({
      component: as || 'core.dialog.alertReminderItem',
      dialogId: getId(id),
      props: {
        onClose: () => {
          handleDismiss(item);
        },
        disableClose: !extra?.can_close,
        title: title ? i18n.formatMessage({ id: title }) : '',
        message: description ? i18n.formatMessage({ id: description }) : '',
        positiveButton: primary_action
          ? {
              label: primary_action?.label,
              onClick: () => handleAction(item)
            }
          : {},
        ...dialogProps
      }
    });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (hideTemp) {
      cookieBackend.remove(CachedHideTempForAction);

      return;
    }

    if (!dataFilters?.length) return;

    if (dataFilters.length === 1) {
      presentAlert(dataFilters[0]);
    } else {
      const disableCloseAll = !!dataFilters.find(x => !x?.extra?.can_close);

      dialogBackend.present({
        component: 'core.dialog.alertReminderList',
        props: {
          data: dataFilters,
          presentAlert,
          disableCloseAll,
          handleDismissAll
        }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return null;
}
