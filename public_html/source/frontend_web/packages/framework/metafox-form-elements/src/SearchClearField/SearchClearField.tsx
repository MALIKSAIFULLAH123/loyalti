/**
 * @type: formElement
 * name: form.element.ClearSearch
 * chunkName: formElement
 */

import { FormFieldProps } from '@metafox/form/types';
import { Box, Link } from '@mui/material';
import { pick, omit, omitBy, debounce, isEqualWith, isNumber } from 'lodash';
import React from 'react';
import { useFormSchema } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { useFormikContext } from 'formik';

export default function ClearSearchField({
  config: {
    excludeFields,
    component, // fix React warning.
    label = 'reset',
    align = 'right',
    ...restConfig
  },
  name,
  formik
}: FormFieldProps) {
  // this component only use sidebar search app. if want clear form please check ClearSearchForm element
  const { i18n } = useGlobal();
  const { value: valueDefault, acceptPageParams } = useFormSchema();
  const { values = {}, setValues } = useFormikContext();
  const defaultRelatedValues = omit({ ...valueDefault }, excludeFields);
  const relatedValues = omit(Object.assign({}, values), excludeFields);
  const excludeValues = pick(Object.assign({}, values), excludeFields);
  const disableReset = isEqualWith(
    defaultRelatedValues,
    omitBy(relatedValues, v => !v),
    (a, b) => {
      if (acceptPageParams) {
        // accept pageParams will not distinguish between numbers and string
        if (isNumber(a) || isNumber(b)) {
          return a == b;
        }
      }
    }
  );

  const handleReset = React.useCallback(() => {
    if (disableReset) return;

    const baseValue = { ...excludeValues, ...defaultRelatedValues };

    setValues(baseValue);

    return;

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [defaultRelatedValues, disableReset, excludeValues]);

  const debounceSearch = debounce(handleReset, 200);

  const onClick = () => {
    debounceSearch();
  };

  return (
    <Box sx={{ textAlign: align }} {...restConfig}>
      <Link
        color="primary"
        component="button"
        variant="body2"
        onClick={onClick}
        disabled={disableReset}
        type="button"
      >
        {label && i18n.formatMessage({ id: label })}
      </Link>
    </Box>
  );
}
