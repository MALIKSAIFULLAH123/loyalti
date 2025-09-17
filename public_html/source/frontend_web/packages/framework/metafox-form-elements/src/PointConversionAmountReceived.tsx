/**
 * @type: formElement
 * name: form.element.PointConversionAmountReceived
 * chunkName: formExtras
 */

import { Typography } from '@mui/material';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { camelCase } from 'lodash';
import { useGlobal } from '@metafox/framework';

const formatPriceCurrency = ({ price, pattern, label }) => {
  let result: any = 0;

  if (!price || !pattern) return label;

  result = pattern.replace(/{3\}/gi, price);

  return `${label} <b>${result}</b>`;
};

const PointConversionAmountReceived = ({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) => {
  const { i18n } = useGlobal();
  const {
    feePercentage,
    exchangeRate,
    exchangeRatePattern = {
      decimal_separator: '.',
      pattern: '',
      precision: 2,
      thousand_separator: ','
    },
    relatedField = 'points',
    testid,
    label,
    variant,
    tagName = 'span'
  } = config;

  const value = !isNaN(formik.values[relatedField])
    ? formik.values[relatedField]
    : 0;

  return (
    <Typography
      data-testid={camelCase(`field ${testid}`)}
      variant={variant as any}
      component={tagName as any}
    >
      <div
        dangerouslySetInnerHTML={{
          __html: formatPriceCurrency({
            price: i18n.formatNumber(
              value * exchangeRate * (1 - feePercentage),
              {
                maximumFractionDigits: exchangeRatePattern.precision,
                minimumFractionDigits: exchangeRatePattern.precision
              }
            ),
            pattern: exchangeRatePattern.pattern,
            label
          })
        }}
      />
    </Typography>
  );
};

export default PointConversionAmountReceived;
