import { useGlobal, GlobalState, getItemSelector } from '@metafox/framework';
import {
  DotSeparator,
  FormatDate,
  PrivacyIcon,
  Statistic,
  LineIcon,
  CategoryList,
  UserName
} from '@metafox/ui';
import { styled } from '@mui/material';
import { useSelector } from 'react-redux';
import React from 'react';

const name = 'AuthorInfo';
const HeadlineSpan = styled('span', { name: 'HeadlineSpan' })(({ theme }) => ({
  paddingRight: theme.spacing(0.5),
  color: theme.palette.text.secondary
}));

const ProfileLinkStyled = styled(UserName, {
  name,
  slot: 'profileLink'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  fontWeight: 'normal',
  paddingRight: theme.spacing(0.5),
  color: theme.palette.text.secondary
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
};
export default function AuthorInfo({
  item,
  categories,
  statisticDisplay = 'total_view',
  privacyDisplay = true
}: Props) {
  const { i18n, useTheme } = useGlobal();

  const theme = useTheme();
  const owner = useSelector((state: GlobalState) =>
    getItemSelector(state, item?.owner)
  );

  const user = useSelector((state: GlobalState) =>
    getItemSelector(state, item?.user)
  );

  if (!item) return null;

  return (
    <DotSeparator sx={{ color: 'text.secondary', mt: 0.5 }}>
       <FormatDate
          data-testid="publishedDate"
          value={item?.creation_date}
          format="LL"
        />
        <span>
          {i18n.formatMessage({ id: 'sevent_by' })}&nbsp;
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
        </span>
        {categories.length > 0 ? (
          <span>
            {i18n.formatMessage({ id: 'sevent_in' })}&nbsp;
            <CategoryList data={categories} sx={{ color: theme.palette.text.secondary, 
              display: 'inline-flex!important' }} />
          </span>
        ) : null}
        {item.statistic.total_view > 0 && (
          <Statistic
            values={item.statistic}
            display='total_view'
            component={'span'}
            skipZero={true}
          />
          )}
        {privacyDisplay ? (
          <PrivacyIcon value={item?.privacy} item={item?.privacy_detail} />
        ) : null}
    </DotSeparator>
  );
}
