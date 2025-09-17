import type { LexicalEditor } from 'lexical';
import {
  EmbedConfig,
  LexicalAutoEmbedPlugin,
  URL_MATCHER
} from '@lexical/react/LexicalAutoEmbedPlugin';
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext';
import React from 'react';
import useModal from '@metafox/lexical/hooks/useModal';
import {
  INSERT_YOUTUBE_COMMAND,
  DataYouTubeCommandType
} from '../YouTubePlugin';
import { INSERT_IFRAME_COMMAND, DataIframeCommandType } from '../IframePlugin';
import UrlForm from './EmbedCode/UrlForm';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { INSERT_VIMEO_COMMAND, DataVimeoCommandType } from '../VimeoPlugin';

interface PlaygroundEmbedConfig extends EmbedConfig {
  // Human readable name of the embeded content e.g. Tweet or Google Map.
  contentName: string;

  // Icon for display.
  icon?: JSX.Element;

  // An example of a matching url https://twitter.com/jack/status/20
  exampleUrl: string;

  // For extra searching.
  keywords: Array<string>;

  // Embed a Figma Project.
  description?: string;
}

export const YoutubeEmbedConfig: PlaygroundEmbedConfig = {
  contentName: 'rich_text_editor_youtube_video',

  exampleUrl: 'https://www.youtube.com/watch?v=jNQXAC9IVRw',

  // Icon for display.
  icon: <LineIcon icon={'ico-youtube'} />,

  insertNode: (editor: LexicalEditor, result: DataYouTubeCommandType) => {
    editor.dispatchCommand(INSERT_YOUTUBE_COMMAND, result);
  },

  keywords: ['youtube', 'video'],

  // Determine if a given URL is a match and return url data.
  parseUrl: async (url: string) => {
    const match =
      /^.*(youtu\.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/.exec(url);

    const id = match ? (match?.[2].length === 11 ? match[2] : null) : null;

    if (id != null) {
      return {
        id,
        url
      };
    }

    return null;
  },

  type: 'youtube-video'
};

export const VimeoEmbedConfig: PlaygroundEmbedConfig = {
  contentName: 'rich_text_editor_vimeo_video',

  exampleUrl: '',

  // Icon for display.
  icon: <LineIcon icon={'ico-vimeo2'} />,

  insertNode: (editor: LexicalEditor, result: DataVimeoCommandType) => {
    editor.dispatchCommand(INSERT_VIMEO_COMMAND, result);
  },

  keywords: ['vimeo', 'video'],

  // Determine if a given URL is a match and return url data.
  parseUrl: async (url: string) => {
    const match =
      // eslint-disable-next-line max-len
      /(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:[a-zA-Z0-9_\-]+)?\/?((?:[a-zA-Z0-9_\-]+)?)/i.exec(
        url
      );
    const id = match ? match[1] : null;
    const h = match ? match[2] : null;

    if (id != null) {
      return {
        id,
        url,
        h
      };
    }

    return null;
  },

  type: 'vimeo-video'
};

export const IframeEmbedConfig: PlaygroundEmbedConfig = {
  contentName: 'rich_text_editor_embed_iframe',

  exampleUrl: 'https://www.youtube.com/watch?v=jNQXAC9IVRw',

  // Icon for display.
  icon: <LineIcon icon={'ico-file-code-o'} />,

  insertNode: (editor: LexicalEditor, result: DataIframeCommandType) => {
    editor.dispatchCommand(INSERT_IFRAME_COMMAND, result);
  },

  keywords: ['iframe', 'video'],

  // Determine if a given URL is a match and return url data.
  parseUrl: async (url: string) => {
    return { url };
  },

  type: 'iframe'
};

export const EmbedConfigs = [
  YoutubeEmbedConfig,
  // VimeoEmbedConfig,
  IframeEmbedConfig
];

export function AutoEmbedDialog({
  embedConfig,
  onClose
}: {
  embedConfig: PlaygroundEmbedConfig;
  onClose: () => void;
}): JSX.Element {
  const [editor] = useLexicalComposerContext();
  const { dialogBackend, i18n } = useGlobal();

  const handleData = (
    parseData: DataIframeCommandType | DataYouTubeCommandType
  ) => {
    if (parseData != null) {
      embedConfig.insertNode(editor, parseData);
    }
  };

  const handleSubmit = async values => {
    const { src } = values;
    const urlMatch = URL_MATCHER.exec(src);

    if (embedConfig != null && src != null && urlMatch != null) {
      const dataParseUrl = await embedConfig.parseUrl(src);

      if (dataParseUrl) {
        handleData({ ...values, ...dataParseUrl });
        onClose();

        return;
      }
    }

    dialogBackend.alert({
      message: i18n.formatMessage({ id: 'the_url_is_invalid' })
    });
    onClose();
  };

  return <UrlForm onSubmit={handleSubmit} />;
}

export default function AutoEmbedPlugin(): JSX.Element {
  const [modal, showModal] = useModal();
  const { i18n } = useGlobal();

  const openEmbedModal = (embedConfig: PlaygroundEmbedConfig) => {
    showModal(i18n.formatMessage({ id: embedConfig.contentName }), onClose => (
      <AutoEmbedDialog embedConfig={embedConfig} onClose={onClose} />
    ));
  };

  return (
    <>
      {modal}
      <LexicalAutoEmbedPlugin<PlaygroundEmbedConfig>
        embedConfigs={EmbedConfigs}
        onOpenEmbedModalForConfig={openEmbedModal}
        menuRenderFn={() => null}
        getMenuOptions={() => null}
      />
    </>
  );
}
