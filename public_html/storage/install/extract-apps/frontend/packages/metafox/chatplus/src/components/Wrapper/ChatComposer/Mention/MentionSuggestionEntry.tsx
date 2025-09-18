import { TruncateText, UserAvatar } from '@metafox/ui';
import { Box, Typography } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';

const OptionWrapper = styled('div', {
  name: 'MentionSuggestionEntry',
  slot: 'OptionWrapper'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  padding: theme.spacing(0.5, 1),
  cursor: 'pointer',
  color: theme.palette.text.primary,
  '&:hover': {
    backgroundColor: theme.palette.background.default
  }
}));

export default function MentionSuggestionEntry({
  mention,
  isFocused,
  theme,
  searchValue,
  ...otherProps
}: any) {
  if (!mention) return null;

  const user = {
    full_name: mention.name,
    avatar: mention.avatar
  };
  const { label, isNotify } = mention;

  return (
    <div {...otherProps}>
      <OptionWrapper>
        {isNotify ? null : (
          <UserAvatar size={32} user={user} variant={'circular'} />
        )}
        <Box
          ml={1}
          sx={{
            display: 'flex',
            flexDirection: 'column',
            flex: 1,
            minWidth: 0
          }}
        >
          <Box sx={{ maxWidth: 260 }}>
            {isNotify ? (
              <Typography variant="body1"> {label}</Typography>
            ) : (
              <TruncateText lines={1} component={'span'} variant="h6">
                {label}
              </TruncateText>
            )}
          </Box>
        </Box>
      </OptionWrapper>
    </div>
  );
}
