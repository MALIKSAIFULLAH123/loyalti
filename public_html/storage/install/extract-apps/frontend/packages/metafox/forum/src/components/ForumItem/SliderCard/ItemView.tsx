import { Link } from '@metafox/framework';
import React from 'react';
import LoadingSkeleton from './LoadingSkeleton';
import { ItemTitle, ItemView, Statistic } from '@metafox/ui';
import { Box, styled } from '@mui/material';
import { slugify } from '@metafox/utils';

const name = 'ForumItemMainCard';

const ForumStyled = styled(Box, { name, slot: 'forumStyled' })(({ theme }) => ({
  position: 'relative',

  '&:before': {
    content: '""',
    display: 'block',
    paddingBottom: '56.25%'
  }
}));

const ForumContentStyled = styled(Link, { name, slot: 'forumContentStyled' })(
  ({ theme }) => ({
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    top: 0,
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    textAlign: 'center',
    justifyContent: 'center',
    backgroundColor: '#e0dddd',
    borderRadius: theme.shape.borderRadius * 2,
    padding: theme.spacing(2),
    transition: 'all .2s ease',
    '&:hover': {
      backgroundColor: theme.palette.primary.main,
      '& *': {
        color: 'white !important'
      }
    }
  })
);

export default function ForumItemMainCard({
  item,
  identity,
  itemProps,
  user,
  state,
  handleAction,
  wrapAs,
  wrapProps
}) {
  const to = `/forum/${item?.id}/${slugify(item?.title)}`;
  const { title, statistic } = item;

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid={'itemAnnouncement'}>
      <ForumStyled>
        <ForumContentStyled
          color="inherit"
          to={to}
          sx={{ textDecoration: 'none !important' }}
        >
          <ItemTitle role="button">{title}</ItemTitle>
          <Box mt={1}>
            <Statistic
              values={statistic}
              display={
                statistic.total_sub_forum > 0
                  ? 'total_sub_forum, total_all_thread'
                  : 'total_all_thread'
              }
              component={'span'}
              skipZero={false}
              variant={'subtitle2'}
              color={'text.hint'}
              sx={{
                fontWeight: 700,
                '& >span:before': {
                  color: 'inherit !important'
                }
              }}
            />
          </Box>
        </ForumContentStyled>
      </ForumStyled>
    </ItemView>
  );
}

ForumItemMainCard.displayName = 'ForumItem(MainCard)';
ForumItemMainCard.LoadingSkeleton = LoadingSkeleton;
