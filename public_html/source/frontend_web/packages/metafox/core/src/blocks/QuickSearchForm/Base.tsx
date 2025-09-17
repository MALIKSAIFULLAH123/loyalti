import { BlockViewProps, RemoteDataSource } from '@metafox/framework';
import { FormSchemaShape, SmartFormBuilder } from '@metafox/form';
import { Block, BlockContent } from '@metafox/layout';
import {
  whenParamRules,
  decodeSearchParams,
  encodeSearchParams
} from '@metafox/utils';
import { isNil, omitBy, debounce, isEqualWith, isNumber } from 'lodash';
import React, { useState } from 'react';
import { useSearchParams } from 'react-router-dom';

export interface Props extends BlockViewProps {
  formSchema?: FormSchemaShape;
  dataSource?: RemoteDataSource;
  actionConfig?: RemoteDataSource;
}

const serializeParams = x => ({ view: 'search', ...x });

const compareParams = (values, currentValue) =>
  isEqualWith(
    omitBy(serializeParams(values), v => isNil(v)),
    omitBy(serializeParams(currentValue), v => isNil(v)),
    (a, b) => {
      // url query string will not distinguish between numbers and string
      if (isNumber(a) || isNumber(b)) {
        // eslint-disable-next-line eqeqeq
        return a == b;
      }
    }
  );

export default function QuickSearchForm({
  formSchema,
  dataSource,
  actionConfig,
  ...rest
}: Props) {
  const [searchParams, setSearchParams] = useSearchParams();
  const objSearchParams = decodeSearchParams(searchParams);

  const [currentValue, setCurrentValue] = useState(objSearchParams);

  const onSubmit = (values, actions) => {
    actions.setSubmitting(false);
  };

  const handleSearch = React.useCallback(
    values => {
      if (compareParams(values, currentValue)) {
        return;
      }

      setCurrentValue(values);

      const apiRules = actionConfig?.apiRules;

      const params = whenParamRules(values, apiRules);
      setSearchParams(encodeSearchParams(params), { replace: true });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [currentValue, setCurrentValue]
  );
  // eslint-disable-next-line react-hooks/exhaustive-deps
  const debounceSearch = React.useCallback(debounce(handleSearch, 200), [
    handleSearch
  ]);

  const onChange = ({ values }: any) => {
    debounceSearch(values);
  };

  React.useEffect(() => {
    return () => {
      debounceSearch.cancel();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Block testid="quickSearchForm">
      <BlockContent>
        <SmartFormBuilder
          noHeader
          noBreadcrumb
          formSchema={formSchema}
          dataSource={dataSource}
          onSubmit={onSubmit}
          onChange={onChange}
          {...rest}
        />
      </BlockContent>
    </Block>
  );
}
