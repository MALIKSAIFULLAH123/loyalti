const mentionStyles = {
  control: {
    backgroundColor: theme.mixins.backgroundColor('paper'),
    fontSize: 14,
    fontWeight: 'normal'
  },

  highlighter: {
    overflow: 'hidden'
  },

  input: {
    margin: 0,
    padding: '8px 8px 8px 16px',
    outline: 0,
    border: 0,
    lineHeight: 1.5
  },

  suggestions: {
    marginTop: 0,
    position: 'absolute',
    display: 'block',
    left: 0,
    right: 0,
    top: 0,
    list: {
      position: 'absolute',
      backgroundColor: theme.mixins.backgroundColor('paper'),
      border: '1px solid rgba(0,0,0,0.15)',
      borderBottomWidth: 0,
      bottom: 0,
      top: 'auto',
      left: 8,
      right: 8
    },

    item: {
      padding: '5px 15px',
      borderBottom: '1px solid rgba(0,0,0,0.15)',
      '&focused': {
        backgroundColor: '#cee4e5'
      }
    }
  }
};

export default mentionStyles;
