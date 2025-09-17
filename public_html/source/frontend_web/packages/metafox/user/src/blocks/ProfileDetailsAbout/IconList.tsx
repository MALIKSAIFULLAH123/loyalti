/**
 * @type: ui
 * name: layout.section.icon_list
 */
import { styled, Tooltip } from '@mui/material';
import * as React from 'react';
import { LineIcon, HtmlViewerWrapper } from '@metafox/ui';
import { useGlobal } from '@metafox/framework';
import HtmlViewer from '@metafox/html-viewer';

interface InfoItem {
  icon: string;
  label: string;
  value: any;
  description: any;
  status: any;
}

interface Section {
  label: string;
  description?: string;
  fields?: Record<string, InfoItem>;
}

type Props = {
  section: Section;
};

const AboutIcon = styled(LineIcon, {
  name: 'AboutIcon'
})(({ theme }) => ({
  fontSize: theme.typography.fontSize,
  display: 'flex',
  marginRight: theme.spacing(1),
  lineHeight: '18px',
  // set width to even wings text for Basic Information user Profile
  minWidth: theme.spacing(2.5)
}));

const StyledItem = styled('div', {
  name: 'ProfileDetailAbout',
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

const Field = ({ field }) => {
  const { i18n, jsxBackend } = useGlobal();

  const { value, value_text, as } = field || {};

  if (!value && !value_text) return null;

  const AsComponent = as ? jsxBackend.get(`listInfo.as.${as}`) : '';

  return (
    <StyledItem>
      <Tooltip title={i18n.formatMessage({ id: field?.label || 'info' })}>
        <AboutIcon icon={field.icon} />
      </Tooltip>
      <StyledLabel>
        {AsComponent ? (
          <AsComponent {...field} />
        ) : (
          <HtmlViewerWrapper mt={0}>
            <HtmlViewer html={value_text || value || ''} />
          </HtmlViewerWrapper>
        )}
        {field.status && <StyledLabel>{field.status}</StyledLabel>}
        {field.description && <div>{field.description}</div>}
      </StyledLabel>
    </StyledItem>
  );
};

const InformationList = ({ section }: Props) => {
  const { fields } = section;

  return (
    <div>
      {Object.values(fields).map((field, index) => {
        return <Field field={field} key={`k${index}`} />;
      })}
    </div>
  );
};

export default InformationList;
