import { AnnouncementItemShape } from '@metafox/announcement/types';
import {
  getPagingSelector,
  GlobalState,
  initPagingState,
  ListViewBlockProps,
  PagingState,
  useGlobal,
  PAGINATION_REFRESH
} from '@metafox/framework';
import { Block, BlockContent, BlockHeader, BlockTitle } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import { IconButton, styled } from '@mui/material';
import React from 'react';
import Slider from 'react-slick';
import 'slick-carousel/slick/slick-theme.css';
import 'slick-carousel/slick/slick.css';
import { useSelector } from 'react-redux';

export type Props = ListViewBlockProps;

const NavButtonWrapper = styled('div', {
  name: 'NavButtonWrapper',
  shouldForwardProp: prop => prop !== 'themeId'
})<{ themeId: string }>(({ theme, themeId }) => ({
  color: theme.palette.primary.main,
  display: 'flex',
  alignItems: 'center',

  '& > span': {
    fontSize: theme.mixins.pxToRem(18),
    cursor: 'pointer',
    '&:hover': {
      color: theme.palette.primary.dark
    }
  }
}));
const SlideCount = styled('div', {
  name: 'SlideCount'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  color: theme.palette.text.secondary,
  marginLeft: theme.spacing(1.5),
  marginRight: theme.spacing(1.5)
}));
const UnreadButton = styled('span', {
  shouldForwardProp: props => props !== 'isRead'
})<{ isRead?: boolean }>(({ theme, isRead }) => ({
  right: theme.spacing(2),
  bottom: theme.spacing(2),
  position: 'absolute',
  color: theme.palette.primary.main,
  borderBottom: 'solid 1px transparent',
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

const BlockContentWrapper = styled(BlockContent)(({ theme }) => ({
  '& .slick-list': {
    marginBottom: theme.spacing(2.5)
  }
}));

export default function AnnouncementListing({ title }: Props) {
  const PrevButtonRef = React.useRef<HTMLInputElement>();
  const { jsxBackend, usePageParams, useGetItems } = useGlobal();
  const [open, setOpen] = React.useState(true);
  const { i18n, dispatch } = useGlobal();
  const [currentSlideState, setCurrentSlideState] = React.useState(0);
  const pageParams = usePageParams();
  const pagingId = `groupAnnouncement/${pageParams.id}`;
  const paging =
    useSelector<GlobalState, PagingState>((state: GlobalState) =>
      getPagingSelector(state, pagingId)
    ) || initPagingState();

  const { loading, pagesOffset, dirty, ids } = paging ?? {};
  const data = useGetItems(ids);

  React.useEffect(() => {
    dispatch({
      type: 'group-announcement/list'
    });

    if (dirty) {
      dispatch({
        type: PAGINATION_REFRESH,
        payload: { pagingId }
        // meta: { abortId }
      });

      return;
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [dirty]);
  const handlePrevClick = currentSlide => {
    if (currentSlide === 0) return;

    PrevButtonRef.current.click();
  };

  const ItemView = jsxBackend.get('group_announcement.itemView.mainCard');
  const ItemViewLoading = jsxBackend.get(
    'group_announcement.itemView.mainCard.skeleton'
  );

  const item: AnnouncementItemShape = data[currentSlideState];

  const onMarkAsRead = () => {
    if (item?.is_marked_read) return;

    dispatch({
      type: 'group-announcement/markAsRead',
      payload: item._identity
    });
  };

  const NextArrow = props => {
    const { onClick, currentSlide } = props;
    const { usePreference } = useGlobal();
    const { themeId } = usePreference();

    return (
      <NavButtonWrapper themeId={themeId}>
        <LineIcon
          icon="ico-angle-left"
          onClick={() => handlePrevClick(currentSlide)}
        />
        <SlideCount>
          {currentSlide + 1}/{data?.length}
        </SlideCount>
        <LineIcon icon="ico-angle-right" onClick={onClick} />
      </NavButtonWrapper>
    );
  };

  const PrevArrow = props => {
    const { onClick } = props;

    return (
      <div
        style={{ display: 'none' }}
        onClick={onClick}
        ref={PrevButtonRef}
      ></div>
    );
  };

  const beforeChange = (oldIndex, newIndex) => {
    setCurrentSlideState(newIndex);

    if (
      !loading &&
      pagesOffset?.total > data?.length &&
      pagesOffset?.total - newIndex < 2
    ) {
      dispatch({
        type: 'group-announcement/list'
      });
    }
  };

  const sliderSetting = {
    dots: false,
    infinite: true,
    speed: 500,
    slidesToShow: 1,
    slidesToScroll: 1,
    useTransform: false,
    adaptiveHeight: true,
    nextArrow: <NextArrow />,
    prevArrow: <PrevArrow />,
    beforeChange
  };

  const toggleOpen = React.useCallback(() => setOpen(open => !open), []);

  if (loading && !data.length) {
    return (
      <Block>
        <BlockHeader>
          <BlockTitle>{i18n.formatMessage({ id: title })}</BlockTitle>
        </BlockHeader>
        <BlockContent>
          <ItemViewLoading />
        </BlockContent>
      </Block>
    );
  }

  if (!data.length) return null;

  return (
    <Block>
      <BlockHeader>
        <BlockTitle>{i18n.formatMessage({ id: title })}</BlockTitle>
        <IconButton size="small" color="default" onClick={toggleOpen}>
          <LineIcon icon={open ? 'ico-angle-up' : 'ico-angle-down'} />
        </IconButton>
      </BlockHeader>
      {open ? (
        <BlockContentWrapper>
          <Slider {...sliderSetting}>
            {data.length &&
              data.map(item =>
                React.createElement(ItemView, {
                  identity: `group.entities.group_announcement.${item.id}`,
                  key: item.id
                })
              )}
          </Slider>
          <UnreadButton isRead={item?.is_marked_read} onClick={onMarkAsRead}>
            {item?.is_marked_read
              ? i18n.formatMessage({ id: 'i_have_read_this' })
              : i18n.formatMessage({ id: 'mark_as_read' })}
          </UnreadButton>
        </BlockContentWrapper>
      ) : null}
    </Block>
  );
}

AnnouncementListing.displayName = 'AnnouncementListing';
