import {
  FeedEmbedCardProps,
  LineIcon,
  Image,
  FeedEmbedCard
} from '@metafox/ui';
import { UserItemShape } from '@metafox/user';
import { Box, Typography, useTheme } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const Item = styled(Box, { name: 'ItemInner' })(({ theme }) => ({
  width: '100%',
  textAlign: 'center',
  display: 'flex',
  overflow: 'hidden',
  padding: theme.spacing(1.5, 3),
  alignItems: 'center',
  justifyContent: 'center'
}));

const IconRelation = styled(Box, { name: 'IconRelation' })(({ theme }) => ({
  display: 'inline-flex',
  alignItems: 'center',
  justifyContent: 'center',
  width: theme.spacing(6),
  height: theme.spacing(6),
  borderRadius: '100%',
  background: theme.palette.background.default,
  color: theme.palette.grey[600],
  fontSize: '24px'
}));

const ImageRelation = styled(Image, { name: 'ImageRelation' })(({ theme }) => ({
  width: theme.spacing(6),
  height: theme.spacing(6),
  margin: '2px auto 0',
  '& img': {
    background: 'transparent'
  }
}));

type RalationType = { label: string; value: number };
type UserProfileItemShape = {
  user: UserItemShape;
  relation: RalationType;
  relation_with?: string;
  relation_image?: string;
  relation_image_dark?: string;
};
type EmbedUserProfileItemProps = FeedEmbedCardProps & {
  item: UserProfileItemShape;
};

export default function EmbedUserProfileItem({
  feed,
  item,
  isShared
}: EmbedUserProfileItemProps) {
  const { relation, relation_image, relation_image_dark } = item || {};

  const theme = useTheme();
  const imageRelation =
    theme.palette.mode === 'dark' ? relation_image_dark : relation_image;

  if (!item) return null;

  return (
    <FeedEmbedCard
      item={item}
      feed={feed}
      isShared={isShared}
      variant="default"
    >
      <Item>
        {relation ? (
          <Box>
            {relation_image ? (
              <ImageRelation src={imageRelation} aspectRatio={'11'} />
            ) : (
              <IconRelation>
                <LineIcon icon="ico-user-couple" />
              </IconRelation>
            )}
            <Typography mt={1.5} color="text.secondary" variant="body1">
              {relation.label}
            </Typography>
          </Box>
        ) : null}
      </Item>
    </FeedEmbedCard>
  );
}
