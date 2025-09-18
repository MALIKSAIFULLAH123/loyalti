/* eslint-disable @typescript-eslint/no-unused-vars */
import { AnnouncementItemShape } from '@metafox/announcement/types';
import { PagingState, useGlobal } from '@metafox/framework';
import React from 'react';
import AutoHeight from 'embla-carousel-auto-height';
import { Box, CircularProgress, styled } from '@mui/material';
import {
  Carousel,
  CountDisplayCarousel,
  NextButtonCarousel,
  PrevButtonCarousel,
  EmblaCarouselType
} from '@metafox/core';

const name = 'CarouselListing';

const FooterBlockStyled = styled(Box, {
  name,
  slot: 'FooterBlockStyled'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  marginTop: theme.spacing(1)
}));

const NavButtonWrapper = styled('div', {
  name,
  slot: 'NavButtonWrapper'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));

const PrevButtonStyled = styled(PrevButtonCarousel, {
  name,
  slot: 'PrevButtonStyled'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(18),
  padding: 0,
  justifyContent: 'flex-start',
  maxWidth: 'fit-content',
  '&:hover:not([disabled])': {
    color: theme.palette.primary.dark
  }
}));

const NextButtonStyled = styled(NextButtonCarousel, {
  name,
  slot: 'NextButtonStyled'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(18),
  padding: 0,
  justifyContent: 'flex-end',
  maxWidth: 'fit-content',
  '&:hover:not([disabled])': {
    color: theme.palette.primary.dark
  }
}));

const CountDisplayStyled = styled(CountDisplayCarousel, {
  name,
  slot: 'CountDisplayStyled'
})(({ theme }) => ({
  margin: theme.spacing(0, 1)
}));

const UnreadButton = styled('span', {
  name: 'LayoutBlock',
  slot: 'UnreadButton',
  overridesResolver(props, styles) {
    return [styles.unreadButton];
  },
  shouldForwardProp: props => props !== 'isRead'
})<{ isRead?: boolean }>(({ theme, isRead }) => ({
  color: theme.palette.primary.main,
  userSelect: 'none',
  '&:hover': {
    borderBottom: `solid 1px ${theme.palette.primary.main}`,
    cursor: 'pointer'
  },
  ...(isRead && {
    color: theme.palette.grey['400'],
    '&:hover': {}
  })
}));

const LoadingOverplay = styled(Box, { name, slot: 'LoadingOverplay' })(
  ({ theme }) => ({
    backgroundColor: 'rgba(0, 0, 0, 0.1)',
    position: 'absolute',
    top: 0,
    right: 0,
    left: 0,
    bottom: 0,
    display: 'flex',
    borderRadius: theme.shape.borderRadius,
    justifyContent: 'center',
    alignItems: 'center'
  })
);

type CarouselListingType = {
  data: AnnouncementItemShape[];
  total: number;
  loadMore?: () => void;
  carousel?: any;
  itemView?: any;
  paging?: PagingState;
};

const OPTIONS_CAROUSEL = {
  watchResize: (emblaApi: any, entries: any) => {
    return false;
  }
};

const Item = ({ loading, item, direction, itemView: ItemView, isLast }) => {
  return (
    <Box position={'relative'} display={'flex'} dir={direction}>
      {React.createElement(ItemView, {
        identity: `announcement.entities.announcement.${item.id}`
      })}
      {isLast && loading && (
        <LoadingOverplay data-testid="loadingIndicator">
          <CircularProgress size={20} />
        </LoadingOverplay>
      )}
    </Box>
  );
};

const CarouselListing = React.forwardRef(
  (
    {
      data,
      total,
      itemView: ItemView,
      paging,
      carousel,
      onInit
    }: CarouselListingType,
    ref: any
  ) => {
    const { useTheme, useLoggedIn, i18n, dispatch } = useGlobal();
    const { direction } = useTheme() || {};
    const isLoggedIn = useLoggedIn();

    const { loading } = paging || {};
    const item: AnnouncementItemShape = data[carousel?.currentSelected];

    const onMarkAsRead = () => {
      if (item.is_read) return;

      dispatch({
        type: 'announcement/markAsRead',
        payload: { id: item.id, isDetail: false }
      });
    };

    return (
      <>
        <Carousel
          onInit={onInit}
          options={OPTIONS_CAROUSEL}
          plugins={[AutoHeight()]}
        >
          {data.length &&
            data.map((item, index) => (
              <Item
                key={item?.id.toString()}
                item={item}
                isLast={index === data?.length - 1}
                itemView={ItemView}
                direction={direction}
                loading={loading}
              />
            ))}
        </Carousel>
        <FooterBlockStyled>
          <NavButtonWrapper dir={direction}>
            <PrevButtonStyled carousel={carousel} disableRipple />
            <CountDisplayStyled carousel={carousel} total={total} />
            <NextButtonStyled carousel={carousel} disableRipple />
          </NavButtonWrapper>
          {item && isLoggedIn && (
            <UnreadButton isRead={item?.is_read} onClick={onMarkAsRead}>
              {item?.is_read
                ? i18n.formatMessage({ id: 'i_have_read_this' })
                : i18n.formatMessage({ id: 'mark_as_read' })}
            </UnreadButton>
          )}
        </FooterBlockStyled>
      </>
    );
  }
);

export default React.memo(CarouselListing);
