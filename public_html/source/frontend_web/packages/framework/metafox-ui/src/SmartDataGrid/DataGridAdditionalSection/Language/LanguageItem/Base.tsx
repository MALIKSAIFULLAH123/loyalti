import { Link, useGlobal } from '@metafox/framework';
import React from 'react';
import { getImageSrc } from '@metafox/utils';
import {
  ItemViewProps,
  ItemTitle,
  ItemText,
  ItemView,
  Image
} from '@metafox/ui';
import { styled, Typography } from '@mui/material';

const name = 'DatagridLanguageRecommendItem';

const Price = styled(Typography, { name })(({ theme }) => ({
  color: theme.palette.warning.main,
  fontSize: 18,
  lineHeight: 1,
  [theme.breakpoints.down('lg')]: {
    fontSize: 16
  }
}));

export default function LanguageItem(
  props: ItemViewProps<{
    name?: string;
    image?: Record<string, any>;
    id: string | number;
  }>
) {
  const { item, wrapProps, wrapAs } = props;
  const { i18n } = useGlobal();

  if (!item) return null;

  const { name, image, id, author, price, discount } = item || {};
  const imageSrc = getImageSrc(image, '500');
  const link = `/app/store/product/${id}`;

  return (
    <ItemView
      wrapProps={wrapProps}
      wrapAs={wrapAs}
      testid="languageRecommendItem"
      sx={{
        borderRadius: theme => `${theme.shape.borderRadius}px`,
        overflow: 'hidden'
      }}
    >
      <Image link={link} src={imageSrc} aspectRatio={'169'} />
      <ItemText
        p={2}
        sx={{ background: theme => theme.palette.background.paper }}
      >
        <ItemTitle fontWeight={'700'} variant="body1">
          <Link to={link}>{name}</Link>
        </ItemTitle>
        {author ? (
          <Typography sx={{ color: 'text.secondary', mt: 0.5 }}>
            <Link to={author?.url} target="_blank">
              {author?.name}
            </Link>
          </Typography>
        ) : null}
        {price ? (
          <Typography sx={{ color: 'text.secondary', mt: 0.5 }}>
            {discount && (
              <Typography
                sx={{ textDecoration: 'line-through' }}
                color="text.hint"
                variant="body1"
              >
                ${price}
              </Typography>
            )}
            <Price>
              {Number(price)
                ? `$${Number(discount || price)}`
                : i18n.formatMessage({ id: 'free' })}
            </Price>
          </Typography>
        ) : null}
      </ItemText>
    </ItemView>
  );
}
