// types
import {
  getPagingSelector,
  GlobalState,
  initPagingState,
  ListViewBlockProps,
  PagingState,
  useGlobal,
  useScrollEnd
} from '@metafox/framework';
// layout
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { GridItem } from '@metafox/ui';
// utils
import { Grid } from '@mui/material';
import { isArray, get } from 'lodash';
import * as React from 'react';
import { useSelector } from 'react-redux';
import useStyles from './styles';

export interface TItemBase {
  id: number;
  resource_name?: string;
  module_name?: string;
}

export interface Props extends ListViewBlockProps {
  columns?: number;
  margin?: number;
  rowHeight?: number;
  direction?: string;
  itemHeight?: number;
  pagingId: string;
}

const wrapProps = {};

export default function CasualView({
  rowHeight = 200,
  blockProps,
  gridContainerProps,
  itemProps,
  title,
  itemView,
  pagingId,
  canLoadMore,
  numberOfItemsPerPage,
  displayLimit
}: Props) {
  const classes = useStyles();
  const { jsxBackend, i18n } = useGlobal();
  const ItemView = jsxBackend.get(itemView);

  const [currentPage, setCurrentPage] = React.useState<number>(1);
  const perPage = numberOfItemsPerPage || 20;
  const limit = displayLimit || 4;
  const paging: PagingState =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, pagingId)
    ) || initPagingState();

  // eslint-disable-next-line no-console
  // console.log(`render ${pagingId}`);

  useScrollEnd(
    canLoadMore ? () => setCurrentPage(prev => prev + 1) : undefined
  );

  const data = canLoadMore
    ? paging.ids.slice(0, currentPage * perPage)
    : paging.ids.slice(0, limit || 4);

  const { ended, error, initialized } = paging ?? {};

  if (!ItemView) return null;

  if (error) {
    const message =
      get(error, 'response.data.error') || get(error, 'response.data.message');

    const errorName =
      get(error, 'response.status') === 403
        ? 'core.block.error403'
        : 'core.block.error404';
    const ErrorBlock = jsxBackend.get(errorName);

    return <ErrorBlock title={message} />;
  }

  if (!initialized)
    return (
      <div data-testid="loadingIndicator">
        {i18n.formatMessage({ id: 'loading_dots' })}
      </div>
    );

  if (!data.length && ended) {
    const NoResultsBlock = jsxBackend.get('core.block.no_results');

    return (
      <Block>
        <BlockHeader title={title} />
        <BlockContent>
          <NoResultsBlock />
        </BlockContent>
      </Block>
    );
  }

  return (
    <Block>
      <BlockHeader title={title} />
      <BlockContent>
        <Grid container className={classes.root} {...gridContainerProps}>
          {isArray(data) &&
            data.map(id => (
              <ItemView
                identity={id}
                key={id.toString()}
                itemProps={itemProps}
                classes={classes}
                wrapAs={GridItem}
                wrapProps={wrapProps}
                rowHeight={rowHeight}
              />
            ))}
          <div className={classes.lastItem}></div>
        </Grid>
      </BlockContent>
    </Block>
  );
}
