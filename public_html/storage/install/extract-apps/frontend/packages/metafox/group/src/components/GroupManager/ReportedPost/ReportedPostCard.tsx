/**
 * @type: itemView
 * name: group.itemView.reportedPost
 * chunkName: group
 */

import {
  getItemSelector,
  GlobalState,
  useGlobal,
  withItemView
} from '@metafox/framework';
import { GroupItemProps } from '@metafox/group';
import groupManagerActions from '@metafox/group/actions/groupManagerActions';
import { ButtonList, ItemSummary, ItemView, LineIcon } from '@metafox/ui';
import {
  Box,
  Button,
  CardContent,
  Divider,
  styled,
  Typography
} from '@mui/material';
import * as React from 'react';
import { useSelector } from 'react-redux';

const StyledItemView = styled(ItemView)(({ theme }) => ({
  padding: 0
}));
const StyledItemSummary = styled(ItemSummary)(({ theme }) => ({
  padding: theme.spacing(2.5, 2, 0, 2)
}));
const StyledWrapperReason = styled('div')(({ theme }) => ({
  padding: theme.spacing(0, 2, 2, 2)
}));
const StyledHeaderReason = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  marginBottom: theme.spacing(0.5)
}));

const HeadlineSpan = styled('span', { name: 'HeadlineSpan' })(({ theme }) => ({
  paddingRight: theme.spacing(0.5),
  color: theme.palette.text.secondary
}));

const StyledLink = styled('span')(({ theme }) => ({
  '& a': {
    fontWeight: theme.typography.fontWeightBold,
    color: theme.palette.text.primary
  },
  '& span': {
    fontWeight: theme.typography.fontWeightBold,
    color: theme.palette.text.primary,
    cursor: 'pointer'
  }
}));
const ListButton = styled(ButtonList)(({ theme }) => ({
  justifyContent: 'end'
}));

const ReportedPostCard = ({
  identity,
  wrapAs: WrapAs,
  wrapProps,
  actions
}: GroupItemProps) => {
  const { jsxBackend, i18n } = useGlobal();

  const reportedItem = useSelector((state: GlobalState) =>
    getItemSelector(state, identity)
  );
  const { reason } = reportedItem;

  const FeedContent = jsxBackend.get('feed.itemView.content');

  if (!identity || !FeedContent) return null;

  const ContentWrapper = withItemView({}, () => {})(FeedContent);

  const content = ContentWrapper({
    identity: reportedItem?.embed_object,
    isItemAction: false
  });

  return (
    <StyledItemView
      testid="ReportedPostCard"
      wrapAs={WrapAs}
      wrapProps={wrapProps}
    >
      <StyledItemSummary>
        <HeadlineSpan>
          {i18n.formatMessage({ id: 'total_reports' })}{' '}
          {reportedItem.total_report ? (
            <StyledLink>
              <span onClick={actions.getListReport}>
                {reportedItem.total_report}
              </span>
            </StyledLink>
          ) : null}
        </HeadlineSpan>
      </StyledItemSummary>
      <Divider />
      <CardContent>{content}</CardContent>
      {reason && reason?.feedback ? (
        <Box sx={{ pb: 2 }}>
          <StyledWrapperReason>
            <StyledHeaderReason>
              <LineIcon icon="ico-warning-o" sx={{ pr: 1 }} />
              <Typography variant="h5">
                {i18n.formatMessage({ id: 'report_reason' })}:
              </Typography>
            </StyledHeaderReason>
            <Typography variant="body2">{reason?.feedback}</Typography>
          </StyledWrapperReason>
          <Divider />
        </Box>
      ) : null}

      <ListButton sx={{ p: 2, pt: 0 }}>
        <Button
          variant="outlined"
          size="medium"
          onClick={actions.keepReportedPost}
        >
          {i18n.formatMessage({ id: 'keep_post' })}
        </Button>
        <Button
          variant="outlined"
          size="medium"
          onClick={actions.removeReportedPost}
          color="error"
        >
          {i18n.formatMessage({ id: 'remove_post' })}
        </Button>
      </ListButton>
    </StyledItemView>
  );
};

export default withItemView({}, groupManagerActions)(ReportedPostCard);
