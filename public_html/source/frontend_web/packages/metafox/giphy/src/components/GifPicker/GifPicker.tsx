import { useGlobal, useResourceAction } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { OnGifClick } from '@metafox/giphy';
import { Box, Paper, styled, Typography } from '@mui/material';
import React from 'react';
import { camelCase, debounce, isEmpty } from 'lodash';
import { APP_GIPHY, RESOURCE_GIF } from '@metafox/giphy/constants';
import { Image, SearchBox } from '@metafox/ui';
import ImageSvg from '../ImageSvg';
import GifMarkDark from '@metafox/giphy/assets/images/gif-mark-dark.png';
import GifMarkLight from '@metafox/giphy/assets/images/gif-mark-light.png';

const name = 'GifPicker';
const PaperStyled = styled(Paper, { name, slot: 'papper' })(({ theme }) => ({
  width: 272,
  height: 300,
  display: 'flex',
  flexDirection: 'column',
  zIndex: 1400,
  bgcolor: 'background.paper',
  paddingTop: 1,
  backgroundImage: 'unset'
}));

const WrapperContent = styled(Box, { name, slot: 'WrapperContent' })(
  ({ theme }) => ({
    flex: 1,
    overflowY: 'hidden',
    display: 'flex',
    flexDirection: 'column'
  })
);

const WrapperSearch = styled(Box, { name, slot: 'WrapperSearch' })(
  ({ theme }) => ({
    margin: theme.spacing(1)
  })
);

const EmptyGif = styled(Box, { name, slot: 'EmptyGif' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  color: theme.palette.text.secondary,
  alignItems: 'center'
}));

const ImageSvgEmpty = styled(Box, { name, slot: 'ImageSvgEmpty' })(
  ({ theme }) => ({
    '& svg': {
      width: '50px',
      height: '50px'
    }
  })
);

interface Props {
  onClickItem: OnGifClick;
}

export default function GifPicker({ onClickItem }: Props) {
  const { ListView, i18n, theme } = useGlobal();
  const [query, setQuery] = React.useState('');
  const [inputValue, setInputValue] = React.useState('');

  const dataSource = useResourceAction(APP_GIPHY, RESOURCE_GIF, 'viewTrending');
  const dataSourceSearch = useResourceAction(
    APP_GIPHY,
    RESOURCE_GIF,
    'viewSearch'
  );

  const debounce_fun = React.useCallback(
    debounce(value => {
      setQuery(value);
    }, 500),
    []
  );

  const handleChange = e => {
    const value = e.target.value;
    setInputValue(value);
    debounce_fun(value);
  };

  const EmptyListView = (
    <EmptyGif>
      <ImageSvgEmpty>
        <ImageSvg outline={false} />
      </ImageSvgEmpty>
      <Typography textAlign="center">
        {i18n.formatMessage({ id: 'no_gif_found' })}
      </Typography>
    </EmptyGif>
  );

  return (
    <PaperStyled data-testid={camelCase('popup gif picker')}>
      <WrapperContent>
        <WrapperSearch>
          <SearchBox
            placeholder={i18n.formatMessage({ id: 'search_gif' })}
            value={inputValue}
            onChange={handleChange}
          />
        </WrapperSearch>
        <ScrollContainer autoHeightMax={240}>
          <ListView
            pageParamsDefault={{ q: query }}
            dataSource={isEmpty(query) ? dataSource : dataSourceSearch}
            emptyPage={EmptyListView}
            errorPage="hide"
            itemView={'giphy.itemView.mainCard'}
            canLoadMore
            canLoadSmooth
            blockLayout="Giphy- All List"
            gridLayout="Giphy - Cards"
            itemLayout="Giphy - Cards"
            handleActionItem={onClickItem}
            limitItemsLoadSmooth={1}
            displayRowsLimit={1}
          />
        </ScrollContainer>
        <Image
          style={{ width: '100px', margin: '8px 0', display: 'inline-flex' }}
          src={theme.palette.mode === 'dark' ? GifMarkDark : GifMarkLight}
        />
      </WrapperContent>
    </PaperStyled>
  );
}
