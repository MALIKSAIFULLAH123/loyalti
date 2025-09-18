import { useAnnouncements } from '@metafox/announcement/hooks';
import { AnnouncementItemShape } from '@metafox/announcement/types';
import { useCarousel, EmblaCarouselType } from '@metafox/core';
import {
  getPagingSelector,
  GlobalState,
  initPagingState,
  ListViewBlockProps,
  PAGINATION,
  PAGINATION_INIT,
  PagingState,
  useAbortControl,
  useGetItems,
  useGlobal,
  useResourceAction
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader, BlockTitle } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import { IconButton, styled } from '@mui/material';
import React from 'react';
import { omit } from 'lodash';
import { useSelector } from 'react-redux';
import { compactData } from '@metafox/utils';
import {
  APP_ANNOUNCEMENT,
  RESOURCE_ANNOUNCEMENT
} from '@metafox/announcement/constants';
import qs from 'query-string';
import CarouselListing from './CarouselListing';

export type Props = ListViewBlockProps;

const name = 'AnnouncementListing';

const BlockContentWrapper = styled(BlockContent, {
  name,
  slot: 'Content',
  overridesResolver(props, styles) {
    return [styles.content];
  }
})(({ theme }) => ({
  padding: theme.spacing(2),
  paddingBottom: theme.spacing(1),
  borderTop: theme.mixins.border('secondary')
}));

const numberOfItemsPerPage = 10;

const itemView = 'announcement.itemView.mainCard';

export default function AnnouncementListing({ title, blockId }: Props) {
  const { useCachedBlockEmpty, jsxBackend, usePageParams } = useGlobal();
  const { i18n, dispatch } = useGlobal();
  const pageParams = usePageParams();
  const ItemView = jsxBackend.get(itemView);
  const ItemViewLoading = jsxBackend.get(`${itemView}.skeleton`);

  const [open, setOpen] = React.useState(true);
  const [ready, setReady] = React.useState(false);
  const announcements = useAnnouncements();

  const config = useResourceAction(
    APP_ANNOUNCEMENT,
    RESOURCE_ANNOUNCEMENT,
    'viewAll'
  ) || { apiUrl: 'announcement' };

  const apiParams = compactData(
    config?.apiParams,
    pageParams,
    config?.apiRules
  );

  const pagingId = `${config?.apiUrl}?${qs.stringify(
    omit(apiParams, ['page'])
  )}`;

  const abortId = useAbortControl(pagingId);
  const [isCachedEmpty, setCachedEmpty] = useCachedBlockEmpty(
    `${pagingId}_${blockId}`
  );

  const [carouselApi, setCarouselApi] = React.useState<EmblaCarouselType>(null);

  const onInit = (emblaApi: EmblaCarouselType) => {
    setCarouselApi(emblaApi);
  };

  const carousel = useCarousel(carouselApi);

  const total = announcements?.statistic?.total;
  const currentSelected = carousel?.currentSelected;

  React.useEffect(() => {
    try {
      // make sure component loaded to slider calculate for plugin AutoHeight
      // ItemView.preload();
      ItemView.load().then(() => {
        setReady(true);
      });
    } catch (error) {}
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const toggleOpen = React.useCallback(() => {
    setOpen(open => !open);
  }, []);

  const paging =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, pagingId)
    ) || initPagingState();

  const { initialized, error, pagesOffset, loading } = paging ?? {};
  const ended =
    !pagesOffset?.total || paging.ids >= pagesOffset.total || paging?.ended;

  const listing = useGetItems(paging.ids);

  const data: AnnouncementItemShape[] = React.useMemo(
    () => listing.filter(item => !(item.can_be_closed && item.is_read)),
    [listing]
  );

  const loadMore = React.useCallback(
    (type: string = PAGINATION) => {
      if (type === PAGINATION_INIT && announcements.loaded) return;

      dispatch({
        type,
        payload: {
          apiUrl: config?.apiUrl,
          apiParams,
          pagingId,
          canLoadMore: true,
          numberOfItemsPerPage,
          lastIdMode: true
        },
        meta: {
          abortId,
          successAction: {
            type: 'announcement/updateStatistic',
            payload: { type }
          }
        }
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [apiParams, config, pagingId, abortId]
  );

  React.useEffect(() => {
    loadMore(PAGINATION_INIT);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (!carouselApi) return;

    if (
      !loading &&
      total > data?.length &&
      data?.length - currentSelected < numberOfItemsPerPage
    ) {
      loadMore();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [carouselApi, total, data, loading, currentSelected]);

  React.useEffect(() => {
    // clear cachedEmpty block
    if (isCachedEmpty && data.length) {
      setCachedEmpty(false);
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isCachedEmpty, data.length]);

  if (!initialized && !announcements.loaded) {
    return (
      <Block>
        <BlockHeader>
          {title ? (
            <BlockTitle>{i18n.formatMessage({ id: title })}</BlockTitle>
          ) : null}
        </BlockHeader>
        <BlockContentWrapper sx={{ pb: 2 }}>
          <ItemViewLoading />
        </BlockContentWrapper>
      </Block>
    );
  }

  if ((!data.length && (ended || isCachedEmpty)) || error) {
    if (!isCachedEmpty) {
      setCachedEmpty(true);
    }

    return null;
  }

  return (
    <Block>
      <BlockHeader>
        {title ? (
          <BlockTitle>{i18n.formatMessage({ id: title })}</BlockTitle>
        ) : null}
        <IconButton size="small" color="default" onClick={toggleOpen}>
          <LineIcon icon={open ? 'ico-angle-up' : 'ico-angle-down'} />
        </IconButton>
      </BlockHeader>
      {open && ready ? (
        <BlockContentWrapper>
          <CarouselListing
            data={data}
            total={total}
            carousel={carousel}
            itemView={ItemView}
            paging={paging}
            onInit={onInit}
          />
        </BlockContentWrapper>
      ) : null}
    </Block>
  );
}
