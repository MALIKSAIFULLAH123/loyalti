import { useGlobal, GlobalState, getItemSelector } from '@metafox/framework';
import {
  DotSeparator,
  FormatDate,
  ItemUserShape,
  PrivacyIcon,
  Statistic,
  UserAvatar,
  LineIcon,
  UserName
} from '@metafox/ui';
import { Box, styled, SxProps } from '@mui/material';
import { useSelector } from 'react-redux';
import React from 'react';
import { isString } from 'lodash';

const name = 'AuthorInfo';

const AvatarWrapper = styled('div', { name, slot: 'AvatarWrapper' })(
  ({ theme }) => ({
    marginRight: theme.spacing(1.5)
  })
);

const HeaderProfileInfo = styled(Box, { slot: 'HeaderProfileInfo' })(
  ({ theme }) => ({
    display: 'flex',
    marginTop: theme.spacing(2)
  })
);

const HeaderSubInfo = styled(Box, { name: 'HeaderSubInfo' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'center'
}));

const HeadlineSpan = styled('span', { name: 'HeadlineSpan' })(({ theme }) => ({
  paddingRight: theme.spacing(0.5),
  color: theme.palette.text.secondary
}));

const ProfileLinkStyled = styled(UserName, {
  name,
  slot: 'profileLink'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  fontWeight: theme.typography.fontWeightBold,
  paddingRight: theme.spacing(0.5),
  color: theme.palette.text.primary
}));

const OwnerStyled = styled(UserName, { name: 'OwnerStyled' })(({ theme }) => ({
  fontWeight: theme.typography.fontWeightBold,
  color: theme.palette.text.primary,
  fontSize: theme.mixins.pxToRem(15),
  '&:hover': {
    textDecoration: 'underline'
  }
}));
type Props = {
  item?: Record<string, any>;
  children?: React.ReactNode;
  statisticDisplay?: string | boolean;
  privacyDisplay?: boolean;
  sx?: SxProps;
};
export default function AuthorInfo({
  item,
  statisticDisplay = 'total_view',
  privacyDisplay = true,
  sx
}: Props) {
  const { i18n } = useGlobal();

  const owner = useSelector((state: GlobalState) =>
    getItemSelector(state, item?.owner)
  );

  const user = useSelector((state: GlobalState) =>
    getItemSelector(state, item?.user)
  );

  if (!item) return null;

  return (
    <HeaderProfileInfo sx={sx}>
      <AvatarWrapper>
        <UserAvatar user={user as ItemUserShape} size={48} />
      </AvatarWrapper>
      <HeaderSubInfo>
        <Box>
          <ProfileLinkStyled user={user} data-testid="headline" />
          {owner?.id !== user?.id && (
            <HeadlineSpan>
              {i18n.formatMessage(
                {
                  id: 'to_parent_user'
                },
                {
                  icon: () => <LineIcon icon="ico-caret-right" />,
                  parent_user: () => <OwnerStyled user={owner} />
                }
              )}
            </HeadlineSpan>
          )}
        </Box>
        <DotSeparator sx={{ color: 'text.secondary', mt: 0.5 }}>
          <FormatDate
            data-testid="publishedDate"
            value={item?.creation_date}
            format="LL"
            tooltipFormat="LLLL"
          />
          {statisticDisplay && isString(statisticDisplay) ? (
            <Statistic
              values={item.statistic}
              display={statisticDisplay}
              component={'span'}
              skipZero={false}
            />
          ) : null}
          {privacyDisplay ? (
            <PrivacyIcon value={item?.privacy} item={item?.privacy_detail} />
          ) : null}
        </DotSeparator>
      </HeaderSubInfo>
    </HeaderProfileInfo>
  );
}
