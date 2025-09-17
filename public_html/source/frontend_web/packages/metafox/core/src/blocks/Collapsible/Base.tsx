import {
  getPagingSelector,
  GlobalState,
  initPagingState,
  ListViewBlockProps,
  PagingState,
  useGlobal
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
// layout
import { Box, Grid } from '@mui/material';
// components
import { isArray, range, get } from 'lodash';
import React from 'react';
import { useSelector } from 'react-redux';

export interface Props extends ListViewBlockProps {
  collapsible: any;
  errorPage: any;
}

export default function Collapsible({
  title,
  itemView,
  itemProps = {},
  gridItemProps = {},
  gridContainerProps = { spacing: 2 },
  pagingId,
  collapsible,
  numberOfItemsPerPage,
  emptyPage = 'core.block.no_content',
  emptyPageProps,
  errorPage,
  loadMore
}: Props) {
  const { jsxBackend, i18n } = useGlobal();
  const ItemView = jsxBackend.get(itemView);
  const Skeleton = jsxBackend.get(`${itemView}.skeleton`);
  const { limit: limitCollapse = 10, as: uiButtonToggle } = collapsible || {};
  const ButtonToggle = jsxBackend.get(uiButtonToggle);
  const [collapsed, setCollapsed] = React.useState(true);

  const paging =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, pagingId)
    ) || initPagingState();

  const { refreshing, error, ended, initialized, dirty } = paging ?? {};

  let data = paging.ids.slice(0, numberOfItemsPerPage || paging.ids.length);

  const canCollapsed = collapsible && paging.ids.length > limitCollapse;

  const isCollapsed = canCollapsed && collapsed;

  if (canCollapsed) {
    data = isCollapsed ? paging.ids.slice(0, limitCollapse) : paging.ids;
  }

  React.useEffect(() => {
    if (!refreshing || dirty) return;

    loadMore();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [initialized, refreshing]);

  if (!ItemView) return null;

  if (!gridItemProps.xs) {
    gridItemProps.xs = 12;
  }

  if (error) {
    if (errorPage === 'hide') return null;

    const message =
      get(error, 'response.data.error') || get(error, 'response.data.message');

    const errorName =
      get(error, 'response.status') === 403
        ? 'core.block.error403'
        : 'core.block.error404';
    const ErrorBlock = jsxBackend.get(errorName);

    return <ErrorBlock title={message} />;
  }

  if (!data.length && (ended || error)) {
    if (emptyPage === 'hide') return null;

    if (typeof emptyPage !== 'string') return emptyPage;

    const NoResultsBlock = jsxBackend.get(emptyPage);

    if (!NoResultsBlock) return null;

    const { noBlock, contentStyle } = emptyPageProps || {};

    if (noBlock) {
      return (
        <Box>
          <NoResultsBlock {...emptyPageProps} />
        </Box>
      );
    }

    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent {...contentStyle}>
          {React.createElement(NoResultsBlock, { ...emptyPageProps })}
        </BlockContent>
      </Block>
    );
  }

  if (!initialized) {
    if (!Skeleton) {
      return <div>{i18n.formatMessage({ id: 'loading_dots' })}</div>;
    }

    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <Grid
            container
            sx={{ '&:empty': { display: 'none' } }}
            {...gridContainerProps}
          >
            {range(0, limitCollapse).map(index => (
              <Skeleton
                wrapAs={Grid}
                wrapProps={gridItemProps}
                itemProps={itemProps}
                key={index.toString()}
              />
            ))}
          </Grid>
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block>
      <BlockHeader title={title} data={data} />
      <BlockContent>
        <Grid container {...gridContainerProps}>
          {isArray(data) &&
            data.map((id, index) => (
              <ItemView
                index={index}
                identity={id}
                itemProps={itemProps}
                key={id.toString()}
                wrapAs={Grid}
                wrapProps={gridItemProps}
                pagingId={pagingId}
              />
            ))}
        </Grid>
        {canCollapsed && ButtonToggle ? (
          <ButtonToggle setCollapsed={setCollapsed} collapsed={collapsed} />
        ) : null}
      </BlockContent>
    </Block>
  );
}
