import {
  PAGINATION,
  useGlobal,
  useResourceAction,
  getPagingSelector,
  GlobalState,
  PagingState,
  initPagingState
} from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { OnStickerClick } from '@metafox/sticker';
import { useStickerSets } from '@metafox/sticker/hooks';
import { LineIcon } from '@metafox/ui';
import { compactData, getImageSrc } from '@metafox/utils';
import { Box, Paper, Tooltip, IconButton, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import Slider from 'react-slick';
import { STICKER, STICKER_SET } from '@metafox/sticker/constant';
import qs from 'query-string';
import { useSelector } from 'react-redux';
import 'slick-carousel/slick/slick-theme.css';
import 'slick-carousel/slick/slick.css';

// import ArrowBackIosIcon from '@mui/icons-material/ArrowBackIos';

const Tabs = styled('div', { name: 'StickerPicker', slot: 'Tabs' })(
  ({ theme }) => ({
    flex: 1,
    display: 'flex',
    flexDirection: 'row'
  })
);

const Tab = styled('div', {
  name: 'StickerPicker',
  slot: 'Tab',
  shouldForwardProp: prop => prop !== 'active'
})<{ active?: boolean }>(({ theme, active }) =>
  Object.assign(
    {
      height: 40,
      width: 40,
      fontSize: 16,
      padding: theme.spacing(0, 1, 0, 1),
      alignItems: 'center',
      justifyContent: 'center',
      display: 'flex!important',
      borderTop: '2px solid',
      borderTopColor: 'transparent'
    },
    active && {
      borderTopColor: theme.palette.primary.main
    }
  )
);

const ContainerList = styled('div', { slot: 'ContainerList' })(({ theme }) => ({
  width: '240px',
  margin: theme.spacing(0),
  padding: theme.spacing(0),
  '& .slick-track': {
    display: 'flex',
    flexWrap: 'wrap',
    width: '100%',
    flexDirection: 'row',
    margin: 0
  },
  '& .slick-initialized .slick-slide': {
    display: 'flex',
    height: 'auto',
    '& > div': {
      width: '100%',
      height: '100%'
    }
  },
  '& .slick-list': {
    cursor: 'pointer'
  },
  '& .slick-next': {
    display: 'flex!important',
    alignItems: 'center',
    width: '35px',
    right: '0px',
    fontSize: '24px',
    justifyContent: 'center',
    borderLeft: '1px solid',
    borderRight: '1px solid',
    '&:before': {
      display: 'none'
    },
    '&:hover': {
      color: 'unset'
    },
    '&.slick-disabled': {
      display: 'none !important'
    }
  },
  '& .slick-prev': {
    display: 'flex!important',
    alignItems: 'center',
    width: '35px',
    left: '0px',
    fontSize: '24px',
    justifyContent: 'center',
    '&:before': {
      display: 'none'
    },
    '&:hover': {
      color: 'unset'
    },
    '&.slick-disabled': {
      display: 'none !important'
    },
    borderRight: '1px solid'
  }
}));

const TabImg = styled('img', { name: 'StickerPicker', slot: 'tabImg' })(
  ({ theme }) => ({
    height: 24,
    maxWidth: 32
  })
);

const NavStickerStyled = styled('div', { name: 'NavStickerStyled' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTop: '1px solid',
    borderColor: theme.palette.border?.secondary
  })
);

const EmptySticker = styled(Box, { name: 'EmptySticker' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  color: theme.palette.text.secondary,
  alignItems: 'center',
  justifyContent: 'center',
  height: '100%'
}));

const IconEmpty = styled(LineIcon, { name: 'iconEmpty' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(26),
  marginBottom: theme.spacing(1)
}));

const NextRow = styled('div', { name: 'nextButton' })(({ theme }) => ({
  zIndex: 99,
  background: `${theme.palette.background.paper}!important`,
  height: '100%!important',
  color: `${theme.palette.text.primary} !important`,
  borderColor: `${theme.palette.border.secondary}!important`,
  '& span': {
    padding: theme.spacing(1)
  }
}));

interface Props {
  multiple?: boolean;
  onStickerClick: OnStickerClick;
}

export default function StickerPicker({ onStickerClick }: Props) {
  const { dispatch, i18n, ListView, getSetting } = useGlobal();
  const [activeTab, setActiveTab] = React.useState<number>(-1);

  const displayLimitRecent = getSetting(
    'sticker.maximum_recent_sticker_can_create'
  );

  const config = useResourceAction(STICKER, STICKER_SET, 'viewMyStickerSet');

  const dataSource = useResourceAction(STICKER, STICKER, 'viewRecentSticker');

  const dataSourceByStickerSet = useResourceAction(
    STICKER,
    STICKER,
    'viewByStickerSet'
  );
  const pagingIdMySticker = `${config?.apiUrl}?${qs.stringify(
    config?.apiParams
  )}`;

  const pagingIdRecent = 'sticker/myRecentSet';
  const pagingDataRecent =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, pagingIdRecent)
    ) || initPagingState();

  const paging =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, pagingIdMySticker)
    ) || initPagingState();

  const stickerSets = useStickerSets(paging.ids);

  React.useEffect(() => {
    dispatch({ type: 'sticker/openStickerPickerDialog' });

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const changeTab = React.useCallback((index: number) => {
    setActiveTab(index);
  }, []);

  const openDialog = () => {
    dispatch({ type: 'sticker/openDialogSticker' });
  };

  const loadMore = () => {
    dispatch({
      type: PAGINATION,
      payload: {
        apiUrl: config?.apiUrl,
        apiParams: { ...config?.apiParams, limit: 8 },
        pagingId: pagingIdMySticker,
        canLoadMore: true
      }
    });
  };

  React.useEffect(() => {
    if (
      pagingDataRecent.ended &&
      stickerSets.length &&
      !pagingDataRecent?.ids.length
    ) {
      setActiveTab(0);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [
    pagingDataRecent?.ids.length,
    pagingDataRecent.ended,
    stickerSets.length
  ]);

  const settings = {
    infinite: false,
    speed: 400,
    slidesToShow: 6,
    slidesToScroll: 4,
    autoplay: false,
    loop: false,
    nextArrow: (
      <NextRow>
        <LineIcon icon="ico-angle-right" onClick={loadMore} />
      </NextRow>
    ),
    prevArrow: (
      <NextRow>
        <LineIcon icon="ico-angle-left" />
      </NextRow>
    )
  };

  const gridItemProps = {
    xs: 3,
    md: 3,
    sm: 3,
    xl: 3,
    lg: 3
  };

  const EmptyRecent = (
    <EmptySticker>
      <IconEmpty icon="ico-sticker" />
      <Typography textAlign="center">
        {i18n.formatMessage({ id: 'no_sticker_used_yet' })}
      </Typography>
    </EmptySticker>
  );

  return (
    <Paper
      sx={{
        width: 272,
        height: 280,
        display: 'flex',
        flexDirection: 'column',
        zIndex: 1400,
        bgcolor: 'background.paper',
        paddingTop: 1,
        backgroundImage: 'unset'
      }}
      data-testid="popupStickerPicker "
    >
      <Box sx={{ flex: 1, overflowY: 'hidden' }}>
        <ScrollContainer autoHeightMax={240} scrollbarWidth={false}>
          <ListView
            dataSource={
              activeTab === -1
                ? dataSource
                : {
                    apiUrl: dataSourceByStickerSet.apiUrl,
                    apiParams: compactData(dataSourceByStickerSet.apiParams, {
                      id: stickerSets[activeTab].id
                    })
                  }
            }
            {...(activeTab === -1 && {
              pagingId: pagingIdRecent,
              displayLimit: displayLimitRecent
            })}
            emptyPage={activeTab === -1 ? EmptyRecent : 'hide'}
            errorPage="hide"
            itemView={'sticker.itemView.mainCard'}
            canLoadMore={!(activeTab === -1)}
            blockLayout="App List"
            {...(!(activeTab === -1) && {
              numberOfItemsPerPage: 20,
              canLoadSmooth: true
            })}
            handleActionItem={onStickerClick}
            gridContainerProps={{
              style: {
                width: '100%',
                marginLeft: 0
              },
              spacing: 2
            }}
            gridItemProps={gridItemProps}
          />
        </ScrollContainer>
      </Box>
      <NavStickerStyled>
        <Tabs>
          <ContainerList>
            <Slider {...settings}>
              <Tab
                active={activeTab === -1}
                role="button"
                onClick={() => changeTab(-1)}
              >
                <Tooltip
                  title={i18n.formatMessage({ id: 'recent_stickers' })}
                  placement="bottom"
                >
                  <LineIcon sx={{ fontSize: '1.125rem' }} icon="ico-clock-o" />
                </Tooltip>
              </Tab>
              {stickerSets.map((data, index) => (
                <Tab
                  active={activeTab === index}
                  role="button"
                  onClick={() => changeTab(index)}
                  key={index.toString()}
                >
                  <Tooltip title={data.title} placement="bottom">
                    <TabImg
                      draggable={false}
                      alt="tabItem"
                      height="24px"
                      src={getImageSrc(data?.image, '200')}
                    />
                  </Tooltip>
                </Tab>
              ))}
            </Slider>
          </ContainerList>
        </Tabs>

        <IconButton color="primary" onClick={openDialog}>
          <LineIcon sx={{ fontSize: '1.125rem' }} icon="ico-plus" />
        </IconButton>
      </NavStickerStyled>
    </Paper>
  );
}
