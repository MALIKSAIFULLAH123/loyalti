import { RouteLink, useGlobal } from '@metafox/framework';
import { Block, BlockContent, BlockHeader, BlockTitle } from '@metafox/layout';
import { InformationList } from '@metafox/ui';
import { Skeleton, styled, Box, Link } from '@mui/material';
import React from 'react';
import { getAdminSiteStatus } from '@metafox/admincp/actions';

const BlockContentWrapper = styled(BlockContent, {
  slot: 'BlockContentWrapper'
})(({ theme }) => ({
  '& span': {
    fontSize: 13
  }
}));

export interface Props {
  data?: Record<string, any>;
  title?: string;
}

export default function AdminItemStats({ title, data }: Props) {
  const { i18n, moment, dispatch } = useGlobal();
  const { loading, data: siteData = {} } = data || {};

  React.useEffect(() => {
    dispatch(getAdminSiteStatus(false));
  }, []);

  const handleReloadStatus = () => {
    dispatch(getAdminSiteStatus(true));
  };

  if (loading) {
    return (
      <Block>
        <BlockHeader title={i18n.formatMessage({ id: title })} />
        <BlockContent>
          <Skeleton variant="text" width={200} />
          <Skeleton variant="text" width={200} />
          <Skeleton variant="text" width={250} />
        </BlockContent>
      </Block>
    );
  }

  const {
    license_status,
    license_status_label,
    license_status_style,
    latest_version,
    license_expired_at,
    can_upgrade,
    version,
    app_channel,
    is_expired
  } = siteData;

  const showSiteStatus = license_status !== 'inactive';

  let description = `${i18n.formatMessage({
    id: 'latest_version'
  })}: ${latest_version}`;
  let action;

  if (!can_upgrade) {
    description = i18n.formatMessage({ id: 'your_site_is_up_to_date' });
    action = (
      <Box>
        <Link
          color={'primary'}
          sx={{ cursor: 'pointer' }}
          onClick={handleReloadStatus}
        >
          {i18n.formatMessage({ id: 'check_for_updates' })}
        </Link>
      </Box>
    );
  } else {
    description = [
      description,
      ' · ',
      <RouteLink to="/app/upgrade">
        {i18n.formatMessage({ id: 'upgrade' })}
      </RouteLink>
    ];
  }

  const infoItems = [
    {
      icon: 'ico-info-circle-alt-o',
      info: `${i18n.formatMessage({
        id: 'license_status'
      })}: `,
      status: `${license_status_label}`,
      class_style: `${license_status_style}`
    },
    license_expired_at
      ? {
          icon: 'ico-sandclock-end-o',
          info: `${i18n.formatMessage(
            {
              id: 'support_expire_on_description'
            },
            {
              date: moment(license_expired_at).format('DD MMMM, yyyy'),
              isExpired: is_expired
            }
          )}`
        }
      : null,
    showSiteStatus
      ? {
          icon: 'ico-file-text-alt-o',
          info: `${i18n.formatMessage({
            id: 'site_version'
          })}: ${version}`,
          description,
          action
        }
      : null,
    app_channel === 'development'
      ? {
          icon: 'ico-code',
          info: `${i18n.formatMessage({
            id: 'channel'
          })}: `,
          status: app_channel
        }
      : null
  ].filter(Boolean);

  return (
    <Block>
      <BlockHeader>
        <BlockTitle>{i18n.formatMessage({ id: title })}</BlockTitle>
      </BlockHeader>
      <BlockContentWrapper>
        {infoItems.length > 0 && <InformationList values={infoItems} />}
      </BlockContentWrapper>
    </Block>
  );
}
