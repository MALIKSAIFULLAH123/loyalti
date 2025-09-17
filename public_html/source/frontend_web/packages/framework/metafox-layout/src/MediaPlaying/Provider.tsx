/**
 * @type: service
 * name: MediaPlayingProvider
 */
import MediaPlaying from './Context';
import React from 'react';

function MediaPlayingProvider({ children }) {
  const state = React.useState<Record<string, any>>({});

  return (
    <MediaPlaying.Provider value={state}>{children}</MediaPlaying.Provider>
  );
}

export default MediaPlayingProvider;
