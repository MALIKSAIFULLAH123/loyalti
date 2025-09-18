const composerConfig = {
  editorPlugins: [
    {
      as: 'statusComposerChat.plugin.mention'
    }
  ],
  editorControls: [
    { as: 'lexical.control.attachEmoji' },
    {
      as: 'commentComposer.control.attachFile',
      showWhen: [
        'and',
        ['falsy', 'editing'],
        ['falsy', 'previewFiles'],
        ['falsy', 'isBotRoom']
      ]
    },
    {
      as: 'chatComposer.control.buttonSubmit'
    }
  ]
};
export default composerConfig;
