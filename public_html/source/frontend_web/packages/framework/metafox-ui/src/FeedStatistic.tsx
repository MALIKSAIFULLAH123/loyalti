import { useGlobal } from '@metafox/framework';
import { styled, Theme, Box } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import * as React from 'react';
import { RemoteDataSource } from '@metafox/framework/types';
import { ItemReactionInformationShape } from './types';
import { isEmpty } from 'lodash';

type Props = {
  identity: string;
  reactions?: Array<ItemReactionInformationShape>;
  statistic?: Record<string, number>;
  message?: string;
  sizeIcon?: 'sm' | 'md';
  handleAction: (name: string) => void;
  messageCommentStatistic?: string;
  dataSourceCommentStatistic?: RemoteDataSource;
  handleActionCommentStatistic?: () => void;
};

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        height: theme.spacing(4),
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        width: '100%'
      },
      itemInner: {
        display: 'flex'
      },
      subInfo: {
        marginLeft: theme.spacing(2),
        fontSize: '13px',
        color: theme.palette.text.secondary,
        '&:hover': {
          textDecoration: 'underline'
        },
        [theme.breakpoints.down('sm')]: {
          fontWeight: 'normal'
        }
      },
      subInfoItem: {
        marginLeft: theme.spacing(2),
        textTransform: 'lowercase'
      },
      toggleCommentList: {
        outline: 'none'
      }
    }),
  { name: 'FeedStatistic' }
);

const TotalShare = styled('div', { name: 'TotalShare' })(({ theme }) => ({
  marginLeft: theme.spacing(2),
  textTransform: 'lowercase',
  fontSize: theme.mixins.pxToRem(13),
  color: theme.palette.text.secondary
}));

const FeedStatistic = (props: Props) => {
  const {
    identity,
    handleAction,
    reactions,
    statistic,
    sizeIcon = 'sm',
    messageCommentStatistic,
    dataSourceCommentStatistic,
    handleActionCommentStatistic
  } = props;
  const classes = useStyles();
  const { i18n, ReactionResult, TotalComment, useGetItem } = useGlobal();

  const item = useGetItem(identity);

  const canViewReaction = item.extra?.can_view_reaction && !isEmpty(reactions);

  if (!canViewReaction && !statistic?.total_comment && !statistic?.total_share)
    return null;

  return (
    <div className={classes.root}>
      <div className={classes.itemInner}>
        {ReactionResult ? (
          <ReactionResult
            identity={identity}
            handleAction={handleAction}
            data={reactions}
            size={sizeIcon}
            message={props.message}
            total={statistic?.total_like}
          />
        ) : null}
      </div>
      <Box sx={{ display: 'inline-flex' }}>
        {TotalComment &&
        statistic?.total_comment &&
        item?.extra?.can_view_comment ? (
          <TotalComment
            total={statistic?.total_comment}
            handleAction={handleActionCommentStatistic ?? handleAction}
            message={messageCommentStatistic}
            identity={identity}
            dataSource={dataSourceCommentStatistic}
          />
        ) : null}

        {statistic?.total_share ? (
          <TotalShare>
            {i18n.formatMessage(
              { id: 'total_share' },
              {
                value: statistic.total_share
              }
            )}
          </TotalShare>
        ) : null}
      </Box>
    </div>
  );
};

export default FeedStatistic;
