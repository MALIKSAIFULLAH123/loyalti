/**
 * @type: ui
 * name: forum.filterThread
 */

import { useGlobal, useResourceForm } from '@metafox/framework';
import React from 'react';
import { FormBuilder } from '@metafox/form';
import { isEqual } from 'lodash';
import qs from 'query-string';

const FilterThread = () => {
  const { navigate } = useGlobal();

  const formSchema = useResourceForm(
    'forum',
    'forum_thread',
    'homepage_search'
  );

  const onChange = ({ values, schema, form }) => {
    if (isEqual(values, form?.initialValues)) {
      return;
    }

    navigate(
      {
        search: qs.stringify({
          ...schema.value,
          ...values
        })
      },
      { replace: true }
    );
  };

  return (
    <FormBuilder
      noHeader
      noBreadcrumb
      formSchema={formSchema}
      onChange={onChange}
    />
  );
};

export default FilterThread;
