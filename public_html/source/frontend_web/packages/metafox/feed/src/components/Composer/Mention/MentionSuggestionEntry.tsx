import { UserAvatar, Statistic, DotSeparator, TruncateText } from '@metafox/ui';
import { Typography, Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import React from 'react';
import { useGlobal } from '@metafox/framework';

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
  const { i18n } = useGlobal();

  if (!mention) return null;

  const user = {
    full_name: mention.name,
    avatar: mention.avatar
  };
  const { moduleName, statistic: statisticDefault, name, type } = mention;
  let displayStatistic = '';
  const statistic = { ...statisticDefault };

  switch (moduleName) {
    case 'page':
      displayStatistic = 'total_page_like';
      break;
    case 'group':
      displayStatistic = 'total_group_member';
      break;
    case 'user':
      displayStatistic = 'total_mutual';
      break;
    default:
      break;
  }
  statistic[displayStatistic] = statisticDefault?.total_people;

  return (
    <div {...otherProps}>
      <OptionWrapper>
        <UserAvatar
          size={32}
          user={user}
          variant={moduleName === 'group' ? 'rounded' : 'circular'}
        />
        <Box ml={1} sx={{ display: 'flex', flexDirection: 'column' }}>
          <Box sx={{ maxWidth: 250 }}>
            <TruncateText lines={1} component={'span'} variant="h6">
              {name}
            </TruncateText>
          </Box>
          <DotSeparator sx={{ color: 'text.secondary', mt: '2px' }}>
            {type ? (
              <Typography variant="body2" component={'span'}>
                {i18n.formatMessage({
                  id: type
                })}
              </Typography>
            ) : null}
            {statisticDefault?.total_people ? (
              <Statistic
                color="text.secondary"
                component={'span'}
                variant="body2"
                values={statistic}
                display={displayStatistic}
                skipZero
              />
            ) : null}
          </DotSeparator>
        </Box>
      </OptionWrapper>
    </div>
  );
}
