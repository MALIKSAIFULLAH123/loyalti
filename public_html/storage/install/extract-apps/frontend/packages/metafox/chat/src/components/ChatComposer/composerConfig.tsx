const composerConfig = {
  editorControls: [
    { as: 'lexical.control.attachEmoji' },
    {
      as: 'commentComposer.control.attachFile',
      showWhen: [
        'and',
        ['falsy', 'editing'],
        ['falsy', 'previewFiles'],
        ['truthy', 'acl.chat.chat_message.send_attachment']
      ]
    },
    {
      as: 'chatComposer.control.buttonSubmit'
    }
  ]
};
export default composerConfig;
