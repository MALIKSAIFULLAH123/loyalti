/**
 * @type: dialog
 * name: layout.dialog.FeedSchedule
 * chunkName: feed
 */

import { useGlobal } from '@metafox/framework';
import { FormBuilder, FormSchemaShape } from '@metafox/form';
import { Dialog } from '@mui/material';
import React from 'react';
import { DialogTitle, DialogContent } from '@metafox/dialog';
import { get, isEmpty } from 'lodash';

type Values = {
  schedule_time: string;
};

export default function FeedScheduleDialog({ value }) {
  const { useDialog, i18n, moment, dialogBackend } = useGlobal();
  const dialogItem = useDialog();
  const {
    closeDialog,
    setDialogValue,
    dialogProps,
    disableBackdropClick,
    setDialogResolveValue
  } = dialogItem;
  const [valueState, setValueState] = React.useState(value);
  const [isValid, setIsValid] = React.useState(true);

  const initialValues: Values = React.useMemo(
    () => ({
      schedule_time: value || ''
    }),
    []
  );

  const onChange = ({ values, form }) => {
    setIsValid(isEmpty(form.errors));
    setValueState(get(values, 'schedule_time'));
  };

  const onSubmit = () => {
    if (!isValid) return;

    setDialogValue(valueState);
    closeDialog();
  };

  const onBackClick = async () => {
    if (valueState && valueState !== value && isValid) {
      const ok = await dialogBackend.confirm({
        message: i18n.formatMessage({
          id: 'the_change_you_made_will_not_be_saved'
        }),
        title: i18n.formatMessage({
          id: 'unsaved_changes'
        })
      });

      if (!ok) {
        return;
      }
    }

    setDialogResolveValue(value);
    closeDialog();
  };

  React.useEffect(() => {
    disableBackdropClick(true);
  });

  const formSchema: FormSchemaShape = {
    component: 'Form',
    elements: {
      basic: {
        component: 'Container',
        name: 'basic',
        testid: 'field basic',
        variant: 'horizontal',
        elements: {
          schedule_time: {
            component: 'Datetime',
            variant: 'outlined',
            name: 'schedule_time',
            returnKeyType: 'next',
            disabled: false,
            displayFormat: 'DD/MM/YYYY - HH:mm',
            minDateTime: moment().toISOString(),
            timeFormat: 24,
            timeSuggestion: true,
            labelTimePicker: i18n.formatMessage({ id: 'time' }),
            labelDatePicker: i18n.formatMessage({ id: 'date' }),
            testid: 'field schedule_time',
            nullable: true
          },
          clear: {
            component: 'FeedScheduleClear',
            name: 'clear',
            schedule_time_key: 'schedule_time',
            testid: 'field sschedule_clear',
            label: i18n.formatMessage({ id: 'clear' })
          }
        }
      }
    },
    validation: {
      type: 'object',
      properties: {
        schedule_time: {
          type: 'date',
          errors: {
            typeError: i18n.formatMessage({ id: 'error_invalid_date' }),
            min: i18n.formatMessage({
              id: 'you_cant_schedule_a_post_to_send_in_the_past'
            })
          },
          nullable: true,
          min: moment().toISOString(),
          label: null
        }
      }
    }
  };

  return (
    <Dialog {...dialogProps} maxWidth="sm">
      <DialogTitle
        onBackClick={onBackClick}
        onDoneClick={onSubmit}
        enableBack
        enableDone
        disableClose
      >
        {i18n.formatMessage({ id: 'schedule' })}
      </DialogTitle>
      <DialogContent>
        <FormBuilder
          initialValues={initialValues}
          formSchema={formSchema}
          onChange={onChange}
        />
      </DialogContent>
    </Dialog>
  );
}
