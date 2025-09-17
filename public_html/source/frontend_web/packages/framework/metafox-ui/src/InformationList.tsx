import { styled, Tooltip } from '@mui/material';
import { isEmpty } from 'lodash';
import * as React from 'react';
import LineIcon from './LineIcon';
import { useGlobal } from '@metafox/framework';

type Props = {
  values?: InfoItem[];
};
type InfoItem = {
  icon: string;
  info: any;
  description: any;
  action?: React.FC<{}>;
  status: any;
  class_style: any;
  label: string;
};

const name = 'InformationList';

const AboutIcon = styled(LineIcon, {
  name
})(({ theme }) => ({
  fontSize: theme.typography.fontSize,
  display: 'flex',
  marginRight: theme.spacing(1),
  lineHeight: '18px'
}));

const StyledItem = styled('div', {
  name,
  slot: 'itemIcon'
})(({ theme }) => ({
  ...theme.typography.body1,
  color: theme.palette.text.secondary,
  display: 'flex',
  overflow: 'hidden',
  ':not(:last-child)': {
    marginBottom: theme.spacing(1.5)
  }
}));

const StyledLabel = styled('span')(({ theme }) => ({
  display: 'block',
  overflow: 'hidden',
  wordBreak: 'break-word',
  '& span': {
    display: 'initial',
    textTransform: 'capitalize',
    '&.success': {
      color: theme.palette.success.main
    },
    '&.warning': {
      color: theme.palette.warning.main
    },
    '&.error': {
      color: theme.palette.error.main
    }
  }
}));

const InformationList = (props: Props) => {
  const { values } = props;
  const { i18n } = useGlobal();

  if (!values || !(values?.length > 0)) return null;

  return (
    <div>
      {values.map((item, index) => {
        if (!item) return null;

        return !isEmpty(item.info) ? (
          <StyledItem key={`${index}`}>
            <Tooltip
              disableHoverListener={!item?.label}
              title={i18n.formatMessage({ id: item?.label || ' ' })}
            >
              <AboutIcon icon={item.icon} />
            </Tooltip>
            <StyledLabel>
              {item.info}
              {item.status && (
                <StyledLabel className={item?.class_style}>
                  {item.status}
                </StyledLabel>
              )}
              {item.description && <div>{item.description}</div>}
              {item.action ? item.action : null}
            </StyledLabel>
          </StyledItem>
        ) : null;
      })}
    </div>
  );
};

export default InformationList;
