import { UserShape } from '@metafox/chatplus/types';
import { useGlobal } from '@metafox/framework';
import { styled } from '@mui/material';
import React from 'react';

const UICallReports = styled('div')(({ theme }) => ({
  marginTop: theme.spacing(0.5)
}));
interface Props {
  reports: { u: UserShape; t: string }[];
  user: UserShape;
}

export default function CallReports({ reports, user }: Props) {
  const { i18n } = useGlobal();

  if (!reports?.length) return null;

  return (
    <UICallReports>
      {reports.map((item, key) => (
        <div className="uiCallReport" key={`${key}`}>
          {i18n.formatMessage(
            {
              id: `${item.u._id === user._id ? 'you' : 'user'}_${item.t}`
            },
            {
              user: item.u.name
            }
          )}
        </div>
      ))}
    </UICallReports>
  );
}
