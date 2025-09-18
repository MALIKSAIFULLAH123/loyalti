import { useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { Container } from '@metafox/ui';
import { getImageSrc } from '@metafox/utils';
import { Box } from '@mui/material';
import React from 'react';
import ProfileBanner from './ProfileBanner';

export default function EventBanner({ item, blockProps, identity }: any) {
  const { assetUrl, jsxBackend } = useGlobal();
  const PendingGroupPreview = jsxBackend.get(
    'event.itemView.pendingReviewEventCard'
  );

  if (!item) return null;

  const { title, image_position } = item;

  const image = getImageSrc(
    item.image,
    '1024',
    assetUrl('event.cover_no_image')
  );

  return (
    <Block>
      <BlockContent>
        <Box>
          <ProfileBanner
            identity={identity}
            image={image}
            alt={title}
            left={0}
            top={image_position || 0}
          />
        </Box>

        <Container maxWidth={'md'} gutter>
          <PendingGroupPreview item={item} />
        </Container>
      </BlockContent>
    </Block>
  );
}

EventBanner.LoadingSkeleton = () => null;
EventBanner.displayName = 'EventBanner';
