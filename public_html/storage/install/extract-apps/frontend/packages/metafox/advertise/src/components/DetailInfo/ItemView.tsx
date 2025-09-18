/**
 * @type: itemView
 * name: advertise.block.itemDetailInfo
 * chunkName: advertise
 */

import { useGlobal } from '@metafox/framework';
import { filterShowWhen, getImageSrc } from '@metafox/utils';
import { Box, styled, Typography } from '@mui/material';
import { isEmpty } from 'lodash';
import moment from 'moment';
import * as React from 'react';
import ListButtonAction from './ListButtonAction';
import { Flag, MoreOthers, PendingFlag } from '@metafox/ui';

const name = 'Detail-Info';

const RootStyled = styled(Box, { name, slot: 'root' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row'
}));

const ContentWrap = styled(Box, { name, slot: 'content' })(({ theme }) => ({
  flex: 1,
  minWidth: 0,
  display: 'flex',
  flexDirection: 'column',
  justifyContent: 'space-between',
  paddingLeft: theme.spacing(2)
}));
const ImageStyled = styled('img', { name, slot: 'img' })(({ theme }) => ({
  width: '100px',
  height: '100px',
  borderRadius: theme.shape.borderRadius,
  objectFit: 'cover'
}));

const Title = styled(Typography, { name, slot: 'title' })(({ theme }) => ({
  ...theme.typography.h4,
  color: theme.palette.text.primary,
  marginBottom: theme.spacing(1)
}));

const RowStyled = styled(Box, { name, slot: 'row' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  padding: theme.spacing(0.5, 0)
}));

const RowCustom = styled(Box, { name, slot: 'RowCustom' })(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  padding: theme.spacing(0.5, 0),
  [theme.breakpoints.down('sm')]: {
    flexDirection: 'column'
  }
}));

const RowItemStyled = styled(Box, { name, slot: 'RowItemStyled' })(
  ({ theme }) => ({
    display: 'flex',
    flexDirection: 'row'
  })
);

const LabelStyled = styled(Typography, { name, slot: 'label' })(
  ({ theme }) => ({
    fontSize: theme.mixins.pxToRem(13),
    color: theme.palette.text.secondary,
    marginRight: theme.spacing(0.75),
    '&::after': {
      content: "':'"
    }
  })
);
const DotStyled = styled('div', { name, slot: 'dot' })(({ theme }) => ({
  marginLeft: theme.spacing(0.75),
  marginRight: theme.spacing(0.75),
  '&::before': {
    content: "'·'"
  },
  [theme.breakpoints.down('sm')]: {
    display: 'none'
  }
}));

const DashStyled = styled('div', { name, slot: 'dash' })(({ theme }) => ({
  marginLeft: theme.spacing(0.75),
  marginRight: theme.spacing(0.75),
  '&::before': {
    content: "'–'"
  }
}));

const ValueStyled = styled(Typography, {
  name,
  slot: 'value'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(13),
  color: theme.palette.text.secondary,
  fontWeight: theme.typography.fontWeightBold
}));

const ActiveStyled = styled(Typography, {
  name,
  slot: 'value',
  shouldForwardProp: props => props !== 'is_active'
})<{ is_active?: boolean }>(({ theme, is_active }) => ({
  fontSize: theme.mixins.pxToRem(13),
  color: is_active ? theme.palette.success.main : theme.palette.error.main
}));

const FlagWrapper = styled('span', {
  slot: 'FlagWrapper',
  name
})(({ theme }) => ({
  display: 'inline-flex',
  '&>span': {
    marginRight: theme.spacing(0.5),
    marginBottom: theme.spacing(0)
  }
}));

const Base = ({ item, identity, detailActionMenu }: any) => {
  const { i18n, assetUrl, useGetItem } = useGlobal();
  const {
    title,
    placement: identityPlacement,
    is_active,
    start_date,
    end_date,
    genders,
    languages,
    image,
    age_from,
    age_to,
    locations
  } = item;

  const placement = useGetItem(identityPlacement);

  const itemMenus = filterShowWhen(detailActionMenu, {
    item
  });

  const imgSrc = getImageSrc(
    image,
    '150',
    assetUrl('advertise.default_ad_thumbnail')
  );

  return (
    <RootStyled>
      <ImageStyled src={imgSrc} />
      <ContentWrap>
        <FlagWrapper>
          <PendingFlag variant="itemView" value={item?.is_pending} />
          <Flag
            data-testid="denied"
            variant="itemView"
            type="is_denied"
            color="white"
            value={item?.is_denied}
            text={item?.status_info?.label}
          />
          <Title>{title}</Title>
        </FlagWrapper>
        <RowStyled>
          <LabelStyled>{i18n.formatMessage({ id: 'placement' })}</LabelStyled>
          <ValueStyled>{placement?.title || ''}</ValueStyled>
        </RowStyled>
        <RowStyled>
          <ActiveStyled is_active={is_active}>
            {is_active
              ? i18n.formatMessage({ id: 'active' })
              : i18n.formatMessage({ id: 'inactive' })}
          </ActiveStyled>
          <DotStyled />
          <LabelStyled>
            {i18n.formatMessage({ id: end_date ? 'duration' : 'start_date' })}
          </LabelStyled>
          <Typography variant="body2" color="text.secondary">
            {start_date ? moment(start_date).format('LL') : ''}
          </Typography>
          {end_date ? (
            <>
              <DashStyled />
              <Typography variant="body2" color="text.secondary">
                {end_date ? moment(end_date).format('LL') : ''}
              </Typography>
            </>
          ) : null}
        </RowStyled>
        <RowCustom>
          {isEmpty(genders) ? null : (
            <RowItemStyled>
              <LabelStyled>
                {i18n.formatMessage({ id: 'targeting_genders' })}
              </LabelStyled>
              <ValueStyled>
                <MoreOthers data={genders} />
              </ValueStyled>
            </RowItemStyled>
          )}
          {age_from || age_to ? (
            <RowItemStyled>
              <DotStyled />
              <LabelStyled>{i18n.formatMessage({ id: 'ages' })}</LabelStyled>
              <ValueStyled style={{ display: 'inline-flex' }}>
                {age_from ? (
                  <span>{age_to ? age_from : `${age_from}+`}</span>
                ) : null}
                {age_to ? <span>-{age_to}</span> : null}
              </ValueStyled>
            </RowItemStyled>
          ) : null}
          {isEmpty(languages) ? null : (
            <RowItemStyled>
              <DotStyled />
              <LabelStyled>
                {i18n.formatMessage({ id: 'language' })}
              </LabelStyled>
              <ValueStyled>
                <MoreOthers data={languages} />
              </ValueStyled>
            </RowItemStyled>
          )}
        </RowCustom>
        {isEmpty(locations) ? null : (
          <RowStyled>
            <>
              <LabelStyled>
                {i18n.formatMessage({ id: 'locations' })}
              </LabelStyled>
              <ValueStyled>
                <MoreOthers data={locations} />
              </ValueStyled>
            </>
          </RowStyled>
        )}
        <Box sx={{ my: 1 }}>
          <ListButtonAction identity={identity} menu={itemMenus} item={item} />
        </Box>
      </ContentWrap>
    </RootStyled>
  );
};

export default Base;
