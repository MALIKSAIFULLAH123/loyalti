import { AppUIConfig } from '@metafox/framework';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'music',
      icon: 'ico-music-album',
      to: '/music'
    }
  },
  sidebarSearch: {
    placeholder: 'Search music'
  },
  sidebarCategory: {
    dataSource: { apiUrl: '/music-genre' },
    href: '/music/genre',
    title: 'genres'
  },
  menus: {
    sidebarMenu: {
      items: [
        {
          to: '/music',
          label: 'Home',
          testid: 'landing',
          tab: 'landing',
          icon: 'ico-music-note-o'
        },
        {
          to: '/music/song',
          label: 'All Songs',
          icon: 'ico-music-album',
          testid: 'all_song',
          tab: 'all_song'
        },
        {
          to: '/music/my-songs',
          label: 'My Songs',
          icon: 'ico-music-list-o',
          tab: 'my_song',
          testid: 'my_song'
        },
        {
          to: '/music/playlists',
          label: 'All Playlists',
          icon: 'ico-music-list-o',
          tab: 'all_playlist',
          testid: 'all_playlist'
        },
        {
          to: '/music/my-playlists',
          label: 'My Playlists',
          icon: 'ico-music-list-o',
          tab: 'my_playlist',
          testid: 'my_playlist'
        },
        {
          to: '/music/albums',
          label: 'All Albums',
          icon: 'ico-music-album',
          testid: 'all_album',
          tab: 'all_album'
        },
        {
          to: '/music/my-albums',
          label: 'My Albums',
          icon: 'ico-music-list-o',
          tab: 'my_album',
          testid: 'my_album'
        }
      ]
    }
  }
};
export default initialState;
