import {
  ListViewBlockProps,
  useGlobal,
  useLocation,
  useResourceForm
} from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import * as React from 'react';
import { SmartFormBuilder } from '@metafox/form';
import { isEqual, omit } from 'lodash';
import qs from 'query-string';

export interface Props extends ListViewBlockProps {
  moduleName: string;
  resourceName: string;
  actionName: string;
}

export default function GroupSortMemberBlock({
  moduleName,
  resourceName,
  actionName
}: Props) {
  const { usePageParams, navigate } = useGlobal();
  const location = useLocation();
  const pageName = React.useRef<any>();

  const pageParams = usePageParams();
  const { stab } = pageParams || {};

  const formSchema = useResourceForm(moduleName, resourceName, actionName);

  const onChange = ({ values, schema, form }: any) => {
    const _search = omit(
      {
        stab,
        ...schema.value,
        ...values
      },
      ['view']
    );

    const search = qs.stringify(_search);

    if (pageName.current === search || isEqual(values, form?.initialValues)) {
      return;
    }

    pageName.current = search;
    navigate({ pathname: location.pathname, search });
  };

  return (
    <Block>
      <BlockContent>
        <SmartFormBuilder noTitle formSchema={formSchema} onChange={onChange} />
      </BlockContent>
    </Block>
  );
}
