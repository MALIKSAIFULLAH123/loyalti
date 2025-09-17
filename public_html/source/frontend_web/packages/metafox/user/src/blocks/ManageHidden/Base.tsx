import {
  BlockViewProps,
  useGlobal,
  useResourceAction,
  useResourceForm
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { Box, styled } from '@mui/material';
import * as React from 'react';

const name = 'ManageHidden';

const ListContainerStyled = styled(Box, { name, slot: 'ListContainer' })(
  ({ theme }) => ({
    marginTop: theme.spacing(2)
  })
);

export type Props = BlockViewProps;

export default function ManageHidden({ title }: Props) {
  const { jsxBackend, usePageParams, ListView } = useGlobal();
  const formSchema = useResourceForm('feed', 'activity_snooze', 'search');

  const pageParams = usePageParams();
  const { type: typeParam, q } = pageParams;
  const type = typeParam || formSchema?.value?.type;
  const QuickSearchBlock = jsxBackend.get('core.block.quickSearchForm');

  const pagingId = React.useMemo(() => {
    return `/manage-hidden/tab=${type}/${q}`;
  }, [type, q]);

  const dataSource = useResourceAction('feed', 'activity_snooze', 'searchItem');

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <QuickSearchBlock
          noTitle
          formSchema={formSchema}
          actionConfig={dataSource}
        />
        <ListContainerStyled>
          <ListView
            pageParamsDefault={{ type }}
            canLoadMore
            dataSource={dataSource}
            pagingId={pagingId}
            itemView={'user.itemView.hiddenUser'}
            emptyPage="core.block.no_content"
            emptyPageProps={{
              title: `no_${type}_found`,
              contentStyle: { sx: { p: 2 } }
            }}
            gridLayout="User - Manage Hidden"
            itemLayout="User - Manage Hidden"
          />
        </ListContainerStyled>
      </BlockContent>
    </Block>
  );
}
