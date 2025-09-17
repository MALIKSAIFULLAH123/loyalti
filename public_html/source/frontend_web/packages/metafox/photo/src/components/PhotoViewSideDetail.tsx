/**
 * @type: ui
 * name: photo.view.sideBlock
 * chunkName: photoDetail
 */
import { Link, useGlobal } from '@metafox/framework';
import { CategoryList, LineIcon } from '@metafox/ui';
import { Box, Divider, styled } from '@mui/material';
import * as React from 'react';

const name = 'PhotoViewSideBlock';

const StyledHeaderItemAlbum = styled('div', { name, slot: 'HeaderAlbum' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    padding: theme.spacing(0, 2)
  })
);
const StyledAlbumNameWrapper = styled('div', {
  name,
  slot: 'AlbumNameWrapper'
})(({ theme }) => ({
  '& .ico.ico-photos-o': {
    fontSize: theme.mixins.pxToRem(18),
    marginRight: theme.spacing(1)
  },
  display: 'flex',
  alignItems: 'center'
}));
const AlbumName = styled('div', { name, slot: 'AlbumName' })(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15)
}));

const StyledWrapperStatistic = styled(Box, { name, slot: 'WrapperStatistic' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    height: '100%'
  })
);

export default function PhotoViewSideDetail({
  identity,
  loading = false
}: {
  identity?: string;
  loading?: boolean;
}) {
  const { ItemDetailInteractionInModal, i18n, useGetItems, useGetItem } =
    useGlobal();
  const item = useGetItem(identity);

  const categories = useGetItems<{ id: number; name: string }>(
    item?.categories
  );
  const itemAlbum = useGetItem(item?.album);

  if (!item) return null;

  return (
    <StyledWrapperStatistic>
      <StyledHeaderItemAlbum>
        {itemAlbum && !itemAlbum?.is_default ? (
          <Box sx={{ pt: 2 }}>
            <StyledAlbumNameWrapper>
              <LineIcon icon=" ico-photos-o" />
              <AlbumName>
                {i18n.formatMessage(
                  { id: 'from_album_name' },
                  {
                    name: <Link to={itemAlbum?.link}>{itemAlbum?.name}</Link>
                  }
                )}
              </AlbumName>
            </StyledAlbumNameWrapper>
            <Box sx={{ pt: 2 }}>
              <Divider />
            </Box>
          </Box>
        ) : null}
        <CategoryList
          data={categories}
          sx={{
            pt: 2,
            mb: { sm: 1, xs: 0 }
          }}
          displayLimit={2}
        />
      </StyledHeaderItemAlbum>
      <Box sx={{ flex: 1, minHeight: 0 }}>
        <ItemDetailInteractionInModal
          menuName="detailActionMenu"
          identity={identity}
          loading={loading}
        />
      </Box>
    </StyledWrapperStatistic>
  );
}
