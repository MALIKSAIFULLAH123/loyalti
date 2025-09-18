import FormBuilder from '@metafox/form/FormBuilder';
import {
  BlockViewProps,
  useGlobal,
  useResourceAction,
  useResourceForm
} from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { TypeAll } from '@metafox/saved/constant';
import { whenParamRules } from '@metafox/utils';
import { isEqualWith, isNil, isNumber, omitBy } from 'lodash';
import qs from 'query-string';
import React from 'react';

export interface Props extends BlockViewProps {
  formName?: string;
}

const serializeParams = x => ({ type: TypeAll, ...x });

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

export default function SidebarTypeFilter({ formName = 'sidebar' }: Props) {
  const { usePageParams, compactUrl, useContentParams, navigate } = useGlobal();
  const pageParams = usePageParams();
  const contentParams = useContentParams();
  const { appName, resourceName, type, collection_id } = pageParams;

  const config = useResourceAction(appName, resourceName, 'viewAll');
  const formSchema = useResourceForm(appName, resourceName, formName);
  const [currentValue, setCurrentValue] = React.useState();

  const action = collection_id
    ? `/saved/list/${collection_id}`
    : formSchema?.action;

  const onSubmit = () => {};

  const onChange = ({ values }: any) => {
    if (compareParams(values, currentValue)) {
      return;
    }

    setCurrentValue(values);

    const apiRules =
      contentParams?.mainListing?.dataSource?.apiRules || config.apiRules;

    const params = whenParamRules(values, apiRules);
    const url = compactUrl(action, { type });

    navigate(`${url}?${qs.stringify(params)}`, { replace: true });
  };

  return (
    <Block testid="blockTypeFilter">
      <BlockContent>
        <FormBuilder
          noHeader
          noBreadcrumb
          formSchema={formSchema}
          onSubmit={onSubmit}
          onChange={onChange}
        />
      </BlockContent>
    </Block>
  );
}
