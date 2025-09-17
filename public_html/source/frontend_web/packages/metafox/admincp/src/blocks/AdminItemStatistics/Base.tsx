/* eslint-disable react/jsx-key */
import { Block, BlockContent } from '@metafox/layout';
import {
  ItemMedia,
  ItemText,
  ItemTitle,
  LineIcon,
  UIBlockViewProps,
  FormatNumberCompact
} from '@metafox/ui';
import Slider from 'react-slick';
import 'slick-carousel/slick/slick-theme.css';
import 'slick-carousel/slick/slick.css';
import React from 'react';
import { Skeleton, styled } from '@mui/material';
import { useGlobal } from '@metafox/framework';

const ItemContent = styled('div', { slot: 'ItemContent' })(({ theme }) => ({
  display: 'flex !important',
  justifyContent: 'space-between',
  alignItems: 'center',
  backgroundColor: theme.palette.background.paper,
  margin: theme.spacing(0, 1),
  width: 'calc(100% - 16px) !important',
  padding: theme.spacing(2.5),
  borderRadius: theme.spacing(0.5),
  height: '100%'
}));

const ItemMediaWrapper = styled(ItemMedia, { slot: 'ItemMedia' })(
  ({ theme }) => ({
    paddingRight: theme.spacing(1.5),
    '& span': {
      fontSize: '40px',
      color: '#2681d5'
    }
  })
);

const Title = styled(ItemTitle, { slot: 'ItemTitle' })(({ theme }) => ({
  '& p': {
    color: theme.palette.grey[700],
    marginTop: theme.spacing(0.5),
    fontSize: 16
  }
}));

const Value = styled('div', { slot: 'Value' })(({ theme }) => ({
  fontSize: 28,
  fontWeight: 'bold',
  color: theme.palette.grey['A700']
}));

const BlockContentWrapper = styled(BlockContent, { slot: 'BlockContent' })(
  ({ theme }) => ({
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
      boxShadow: '1.8px 2.4px 7px 0 rgba(0, 0, 0, 0.15)',
      backgroundColor: '#fff !important',
      right: '0px',
      padding: theme.spacing(3),
      display: 'flex !important',
      alignItems: 'center',
      justifyContent: 'center',
      borderRadius: '50%',
      '&:before': {
        display: 'none !important'
      },
      '&.slick-disabled': {
        display: 'none !important'
      }
    },
    '& .slick-prev': {
      boxShadow: '1.8px 2.4px 7px 0 rgba(0, 0, 0, 0.15)',
      backgroundColor: '#fff !important',
      left: '0px',
      padding: theme.spacing(3),
      display: 'flex !important',
      alignItems: 'center',
      justifyContent: 'center',
      borderRadius: '50%',
      zIndex: '100',
      '&:before': {
        display: 'none !important'
      },
      '&.slick-disabled': {
        display: 'none !important'
      }
    }
  })
);
const Arrow = styled('div', { name: 'arrowButton' })(({ theme }) => ({
  cursor: 'pointer',
  fontSize: '24px !important',
  lineHeight: '24px !important',
  color: `${theme.palette.text.primary} !important`
}));
export interface Props extends UIBlockViewProps {}

export default function AdminItemStats({ blockProps, title }: Props) {
  const { useFetchDetail } = useGlobal();

  const [data, loading] = useFetchDetail({
    dataSource: {
      apiUrl: 'admincp/dashboard/item-statistic'
    }
  });
  const settings = {
    infinite: false,
    speed: 400,
    slidesToShow: 5,
    slidesToScroll: 1,
    autoplay: false,
    loop: false,
    swipeToSlide: true,
    nextArrow: (
      <Arrow>
        <LineIcon icon="ico-arrow-right" />
      </Arrow>
    ),
    prevArrow: (
      <Arrow>
        <LineIcon icon="ico-arrow-left" />
      </Arrow>
    ),
    responsive: [
      {
        breakpoint: 1500,
        settings: {
          slidesToShow: 4,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 992,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 2
        }
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
    ]
  };

  if (loading) {
    return (
      <Block>
        <BlockContentWrapper>
          <Slider {...settings}>
            <ItemContent>
              <ItemMediaWrapper>
                <Skeleton variant="circular" width={60} height={60} />
              </ItemMediaWrapper>
              <ItemText>
                <Skeleton variant="text" width="70%" />
                <Skeleton variant="text" width="90%" />
              </ItemText>
            </ItemContent>

            <ItemContent>
              <ItemMediaWrapper>
                <Skeleton variant="circular" width={60} height={60} />
              </ItemMediaWrapper>
              <ItemText>
                <Skeleton variant="text" width="70%" />
                <Skeleton variant="text" width="90%" />
              </ItemText>
            </ItemContent>

            <ItemContent>
              <ItemMediaWrapper>
                <Skeleton variant="circular" width={60} height={60} />
              </ItemMediaWrapper>
              <ItemText>
                <Skeleton variant="text" width="70%" />
                <Skeleton variant="text" width="90%" />
              </ItemText>
            </ItemContent>

            <ItemContent>
              <ItemMediaWrapper>
                <Skeleton variant="circular" width={60} height={60} />
              </ItemMediaWrapper>
              <ItemText>
                <Skeleton variant="text" width="70%" />
                <Skeleton variant="text" width="90%" />
              </ItemText>
            </ItemContent>

            <ItemContent>
              <ItemMediaWrapper>
                <Skeleton variant="circular" width={60} height={60} />
              </ItemMediaWrapper>
              <ItemText>
                <Skeleton variant="text" width="70%" />
                <Skeleton variant="text" width="90%" />
              </ItemText>
            </ItemContent>
          </Slider>
        </BlockContentWrapper>
      </Block>
    );
  }

  return (
    <Block>
      <BlockContentWrapper>
        {!loading && data?.length ? (
          <Slider {...settings}>
            {data.map((item, index) => (
              <ItemContent>
                {item.icon ? (
                  <ItemMediaWrapper>
                    <LineIcon icon={item.icon} />
                  </ItemMediaWrapper>
                ) : null}
                <ItemText>
                  <Value>
                    <FormatNumberCompact value={item.value} />
                  </Value>
                  <Title>{item.label}</Title>
                </ItemText>
              </ItemContent>
            ))}
          </Slider>
        ) : null}
      </BlockContentWrapper>
    </Block>
  );
}
