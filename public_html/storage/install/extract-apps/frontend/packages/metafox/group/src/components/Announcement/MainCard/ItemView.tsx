import {
  getItemSelector,
  GlobalState,
  useGlobal,
  Link
} from '@metafox/framework';
import {
  ItemSummary,
  ItemView,
  UserAvatar,
  FromNow,
  PrivacyIcon,
  UserName
} from '@metafox/ui';
import { styled, Box } from '@mui/material';
import React from 'react';
import { AnnouncementItemProps } from '../../../types';
import HtmlViewer from '@metafox/html-viewer';
import { useSelector } from 'react-redux';

type Props = AnnouncementItemProps;

const Description = styled('span')(({ theme }) => ({
  flex: 'unset',
  marginTop: 'unset'
}));
const Total = styled('div')(({ theme }) => ({
  display: 'flex'
}));

const Privacy = styled('span')(({ theme }) => ({
  '& a:first-of-type + span:before': {
    content: '"·"',
    display: 'inline-block',
    padding: `${theme.spacing(0, 0.5)}`
  }
}));

const ProfileLink = styled(UserName)(({ theme }) => ({
  marginBottom: theme.spacing(0.5),
  color: theme.palette.text.primary,
  fontSize: theme.mixins.pxToRem(13),
  fontWeight: theme.typography.fontWeightBold,
  '&:hover': {
    textDecoration: 'underline'
  }
}));

export default function AnnouncementItemView({
  item,
  wrapAs,
  wrapProps
}: Props) {
  const announcement = useSelector<GlobalState>(state =>
    getItemSelector(state, item?.item)
  );

  const user = useSelector<GlobalState>(state =>
    getItemSelector(state, item?.user)
  );
  const { navigate } = useGlobal();

  if (!item && !announcement) return null;

  const onDetail = () => {
    navigate(announcement?.link);
  };

  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid={'itemAnnouncement'}>
      <Total onClick={onDetail}>
        <Description role="button">
          <Box sx={{ display: 'flex' }}>
            <UserAvatar size={40} user={user} variant="circular" />
            <Box sx={{ display: 'flex', flexDirection: 'column' }} ml={1.5}>
              <ProfileLink to={user.link} user={user} />
              <Privacy>
                <Link color="inherit" to={announcement?.link}>
                  <FromNow value={announcement?.creation_date} />
                </Link>

                <PrivacyIcon item={announcement?.privacy_detail} />
              </Privacy>
            </Box>
          </Box>

          <ItemSummary>
            <HtmlViewer html={announcement?.status || ''} />
          </ItemSummary>
        </Description>
      </Total>
    </ItemView>
  );
}
