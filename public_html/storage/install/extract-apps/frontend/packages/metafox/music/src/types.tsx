import { BlockViewProps } from '@metafox/framework';
import {
  EmbedItemInFeedItemProps,
  ItemShape,
  ItemViewProps
} from '@metafox/ui';

export interface SongItemShape extends ItemShape {
  title: string;
  length?: string;
  album?: string;
  name?: string;
  link?: string;
  url?: string;
}

export type SongItemState = {};

export type SongItemProps = ItemViewProps<SongItemShape>;

export type SongDetailViewProps = ItemViewProps<SongItemShape> & BlockViewProps;

export type EmbedSongInFeedItemProps = EmbedItemInFeedItemProps<SongItemShape>;

export interface AlbumItemShape extends ItemShape {
  title: string;
  name: string;
  year?: number;
  total_play?: number;
  music_song_total_play?: number; 
}

export type AlbumItemState = {};

export type EmbedAlbumInFeedItemProps =
  EmbedItemInFeedItemProps<AlbumItemShape>;

export type AlbumItemProps = ItemViewProps<AlbumItemShape>;

export interface PlaylistItemShape extends ItemShape {
  name: string;
  total_play?: number;
  total_track?: number;
  description?: string;
  music_song_total_play?: number; 
}

export type PlaylistItemProps = ItemViewProps<PlaylistItemShape>;

export type EmbedPlaylistInFeedItemProps =
  EmbedItemInFeedItemProps<PlaylistItemShape>;

export type PlaylistItemState = {};

export type AppState = {};
