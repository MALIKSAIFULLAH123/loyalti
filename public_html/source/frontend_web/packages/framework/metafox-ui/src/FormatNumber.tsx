/* eslint-disable react/react-in-jsx-scope */
import { FormattedNumber } from 'react-intl';
import React from 'react';

export type FormatNumberProps = {
  value: number;
  formatOptions?: any;
};

export default function FormatNumber({ value, formatOptions }) {
  return <FormattedNumber value={value} {...formatOptions} />;
}
