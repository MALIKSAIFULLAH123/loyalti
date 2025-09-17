import { useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { PhotoDetailProps } from '@metafox/photo/types';
import React from 'react';
import { Box, styled } from '@mui/material';

export type Props = PhotoDetailProps;

const name = 'ViewMediaPage';

const Root = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  padding: '0 !important',
  paddingTop: '0 !important',
  display: 'flex',
  overflowY: 'visible',
  [theme.breakpoints.down('md')]: {
    height: 'auto',
    flexFlow: 'column',
    flexDirection: 'column',
    '& > div': {
      overflow: 'inherit'
    }
  }
}));

export default function PhotoDetail({ item, identity }: Props) {
  const { jsxBackend, usePageParams } = useGlobal();
  const { photo_set, photo_album, photo_id, media_type } = usePageParams();
  const MediaViewContainer = jsxBackend.get('media.ui.viewBlock');

  const [render, setRender] = React.useState({
    position: 'fixed',
    left: 0,
    right: 0,
    bottom: 0,
    top: 0,
    zIndex: 999
  });
  const ref = React.useRef();

  const refResize = React.useRef<ResizeObserver>();

  const onResize = React.useCallback(() => {
    if (!ref.current) return;

    const ele: HTMLDivElement = ref.current;
    setRender(prev => ({ ...prev, top: ele.getBoundingClientRect().top }));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (!ref.current) return;

    // need listener observer because div height will change when load image
    refResize.current = new ResizeObserver(() => {
      // Do what you want to do when the size of the element changes
      onResize();
    });

    refResize.current?.observe(ref.current);

    return () => refResize?.current.disconnect(); // clean up
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [onResize, ref.current]);

  if (!item) return null;

  return (
    <Block testid={`detailview ${item.resource_name}`}>
      <BlockContent>
        <Box ref={ref}>
          <Root sx={render}>
            <MediaViewContainer
              photo_set={photo_set}
              photo_album={photo_album}
              identity={identity}
              photo_id={photo_id}
              media_type={media_type}
            />
          </Root>
        </Box>
      </BlockContent>
    </Block>
  );
}
