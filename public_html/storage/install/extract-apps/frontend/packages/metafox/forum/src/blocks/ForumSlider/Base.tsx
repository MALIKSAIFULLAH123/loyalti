import { ListViewBlockProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader, BlockTitle } from '@metafox/layout';
import { LineIcon } from '@metafox/ui';
import { IconButton, styled, Box } from '@mui/material';
import React from 'react';
import Slider from 'react-slick';
import 'slick-carousel/slick/slick-theme.css';
import 'slick-carousel/slick/slick.css';
import { range, get } from 'lodash';

const name = 'ForumItemMainCard';

const NavSliderWrapperStyled = styled(Box, {
  name,
  slot: 'navSliderWrapperStyled'
})(({ theme }) => ({
  display: 'inline-flex',
  alignItems: 'center'
}));

const ForumSliderStyled = styled(Slider, {
  name,
  slot: 'forumSliderStyled'
})(({ theme }) => ({
  '& .slick-list': {
    margin: theme.spacing(0, -1),
    '& .slick-slide': {
      padding: theme.spacing(0, 1)
    },
    '& .slick-track': {
      marginLeft: 0,
      marginRight: 0
    }
  },
  '& .slick-arrow': {
    display: 'none !important'
  }
}));

export type Props = ListViewBlockProps;

export default function ForumSlider({ title, themeId, itemView }: Props) {
  const { jsxBackend, useFetchItems, getAcl, i18n } = useGlobal();
  const [showNav, setShowNav] = React.useState(false);
  const [startEndSlide, setStartEndSlide] = React.useState('start');
  const refSlider = React.useRef(null);
  const acl = getAcl();
  const canViewForum = get(acl, 'forum.forum.view');
  const [items, loading] = useFetchItems({
    dataSource: {
      apiParams: 'view=quick_navigation',
      apiUrl: '/forum'
    },
    normalize: true,
    data: [],
    cache: true
  });
  const ItemView = jsxBackend.get(itemView);
  const Skeleton = jsxBackend.get(`${itemView}.skeleton`);

  React.useEffect(() => {
    if (refSlider && refSlider.current) {
      const sliderObj = refSlider.current;
      const breakpoint = sliderObj.state?.breakpoint;
      const sliderPropsDefault = sliderObj.props;
      const sliderPropsMobile = sliderPropsDefault.responsive.find(
        (k: any) => k.breakpoint === breakpoint
      )?.settings;
      const sliderProps = { ...sliderObj.props, ...sliderPropsMobile };
      const slidesShow = sliderProps.slidesToShow;

      setShowNav(items.length > slidesShow);
    }
  }, [items.length]);

  const checkStartEnd = (index: number, totalShow: number) => {
    let status = '';
    switch (index) {
      case 0:
        status = 'start';
        break;
      case items.length - totalShow:
        status = 'end';
        break;
      default:
      // code block
    }
    setStartEndSlide(status);
  };

  const sliderSetting = {
    dots: false,
    infinite: false,
    speed: 500,
    slidesToShow: 4,
    slidesToScroll: 1,
    useTransform: false,
    adaptiveHeight: true,
    accessibility: false,
    afterChange: index => {
      checkStartEnd(index, 4);
    },
    responsive: [
      {
        breakpoint: 375,
        settings: {
          infinite: 1 < items.length,
          slidesToShow: 1,
          afterChange: (index: number) => {
            checkStartEnd(index, 1);
          }
        }
      },
      {
        breakpoint: 1024,
        settings: {
          infinite: 2 < items.length,
          slidesToShow: 2,
          afterChange: (index: number) => {
            checkStartEnd(index, 2);
          }
        }
      },
      {
        breakpoint: 1199,
        settings: {
          infinite: 3 < items.length,
          slidesToShow: 3,
          afterChange: (index: number) => {
            checkStartEnd(index, 3);
          }
        }
      }
    ]
  };

  const sliderNext = () => {
    refSlider.current.slickNext();
  };

  const sliderPrev = () => {
    refSlider.current.slickPrev();
  };

  if (!canViewForum) {
    return null;
  }

  return (
    <Block>
      <BlockHeader>
        <BlockTitle>{i18n.formatMessage({ id: title })}</BlockTitle>
        {showNav && (
          <NavSliderWrapperStyled>
            <IconButton
              disabled={startEndSlide === 'start'}
              size="small"
              color="default"
              onClick={sliderPrev}
            >
              <LineIcon icon="ico-angle-left" />
            </IconButton>
            <IconButton
              disabled={startEndSlide === 'end'}
              size="small"
              color="default"
              onClick={sliderNext}
            >
              <LineIcon icon="ico-angle-right" />
            </IconButton>
          </NavSliderWrapperStyled>
        )}
      </BlockHeader>
      <BlockContent>
        <ForumSliderStyled
          ref={sliderMethod => (refSlider.current = sliderMethod)}
          {...sliderSetting}
        >
          {loading && Skeleton ? range(0, 4).map(index => <Skeleton />) : null}
          {items &&
            items.map(item => (
              <ItemView
                identity={`forum.entities.forum.${item.id}`}
                key={item.id?.toString()}
              />
            ))}
        </ForumSliderStyled>
      </BlockContent>
    </Block>
  );
}
ForumSlider.displayName = 'ForumSliderBlock';
