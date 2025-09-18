const composerConfig = {
  editorPlugins: [
    { as: 'comment.plugin.mention' },
    { as: 'comment.plugin.hashtag' }
  ],
  editorControls: [
    {
      as: 'commentComposer.control.attachPhoto',
      showWhen: [
        'and',
        ['falsy', 'hasExtraContent'],
        ['truthy', 'settings.comment.enable_photo'],
        ['falsy', 'config.disable_photo']
      ]
    },
    {
      as: 'lexical.control.attachEmoji',
      showWhen: ['truthy', 'settings.comment.enable_emoticon']
    },
    {
      as: 'giphy.control.attachGif',
      showWhen: [
        'and',
        ['falsy', 'hasExtraContent'],
        ['truthy', 'settings.comment.enable_giphy'],
        ['truthy', 'settings.giphy']
      ]
    },
    {
      as: 'commentComposer.control.attachSticker',
      showWhen: [
        'and',
        ['falsy', 'hasExtraContent'],
        ['truthy', 'settings.comment.enable_sticker'],
        ['truthy', 'settings.sticker']
      ]
    }
  ]
};
export default composerConfig;
