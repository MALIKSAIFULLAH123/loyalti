import React from 'react';
import moment from 'moment';
import { Tooltip } from '@mui/material';
import { useGlobal } from '@metafox/framework';

const convertDateTime = (date: any) => {
  if (!date) {
    return null;
  }

  const dateTime = new Date(date);
  const today = new Date();
  let format = 'DD MMMM, yyyy';

  const _date = moment(date);

  if (!_date.isValid()) return '';

  if (today.setHours(0, 0, 0, 0) == dateTime.setHours(0, 0, 0, 0)) {
    format = 'LT';
  }

  return _date.format(format);
};

export type FormatDateProps = {
  component?: any;
  value: string;
  format?: string;
  'data-testid': string;
  phrase?: string;
};

export default function FormatDateRelativeToday({
  component: As = 'time',
  value,
  'data-testid': testid
}) {
  const { moment } = useGlobal();

  if (!value) return null;

  const date = moment(value);
  const title = date.format('llll');

  return (
    <Tooltip title={title}>
      <As data-testid={testid} aria-label={title}>
        {convertDateTime(value)}
      </As>
    </Tooltip>
  );
}
