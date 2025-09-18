import { ListViewBlockProps, useGlobal, useIsMobile } from '@metafox/framework';
import React from 'react';

export default function MainListViewBlock(props: ListViewBlockProps) {
  const {
    jsxBackend,
    usePageParams,
    compactUrl,
    useContentParams,
    compactData
  } = useGlobal();
  const isMobile = useIsMobile();
  const component = jsxBackend.get('core.block.listview');

  const { pageParams } = usePageParams();
  const { mainListing } = useContentParams();
  const {
    dataSource,
    canLoadMore: canLoadMoreMain,
    ...blockProps
  } = mainListing;

  const apiParams = compactData(
    dataSource.apiParams,
    pageParams,
    dataSource.apiRules
  );

  const canLoadMore = props.canLoadMore ?? canLoadMoreMain;

  const apiUrl = compactUrl(dataSource.apiUrl, dataSource.apiParams);
  
  const compactDataSource = {
    apiUrl,
    apiParams
  };

  let emptyPage = {
    image: 'ico-music-note-o',
    title: 'no_songs_found',
    description: 'no_songs_found_description',
    noBlock: true
  };

  let ItemView = isMobile ? 'music_song.itemView.smallCard' : 'music_song.itemView.mainCard';

  if (dataSource.apiParams.entity_type === 'music/playlist') {
    ItemView = isMobile ? 'music_playlist.itemView.smallCard' : 'music_playlist.itemView.mainCard';
    emptyPage = {
      image: 'ico-music-list-o',
      title: 'no_playlists_found',
      description: 'no_playlists_found_description',
      noBlock: true
    };
  } else if (dataSource.apiParams.entity_type === 'music/album') {
    ItemView = isMobile ? 'music_album.itemView.smallCard' : 'music_album.itemView.mainCard';
    emptyPage = {
      image: 'ico-music-album',
      title: 'no_albums_found',
      description: 'no_albums_found_description',
      noBlock: true
    };
  }

  return React.createElement(component, {
    ...props,
    dataSource: compactDataSource,
    canLoadMore,
    emptyPageProps: emptyPage,
    itemView: ItemView,
    ...blockProps
  });
}
