/**
 * @type: formElement
 * name: form.element.AccordionContainer
 */
import { Element } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import {
  Accordion,
  AccordionDetails,
  AccordionSummary,
  Box,
  styled,
  Typography
} from '@mui/material';
import { map } from 'lodash';
import React from 'react';

const Body = styled(Box, {
  name: 'MuiFormContainer',
  slot: 'Body'
})<{ horizontal?: boolean }>(({ theme, horizontal }) => ({
  position: 'relative',
  padding: theme.spacing(1, 0),
  ...(horizontal
    ? {
        display: 'flex',
        alignItems: 'center',
        flexWrap: 'wrap',
        '&>div': {
          paddingRight: theme.spacing(1)
        }
      }
    : {
        display: 'flex',
        flexDirection: 'column'
      })
}));

const StyledTitle = styled(Typography, {
  name: 'AccordionContainer',
  slot: 'Title'
})(({ variant }) => ({}));

const StyledDetail = styled(AccordionDetails, {
  name: 'AccordionContainer',
  slot: 'Details',
  shouldForwardProp(prop: string) {
    return !/variant/i.test(prop);
  }
})<{ variant: string }>(({ variant }) => ({}));

export default function AccordionContainer({ config }) {
  const {
    label,
    description,
    elements,
    variant,
    expanded,
    disableGutters,
    titleVariant,
    detailVariant,
    disabled,
    group = 'AccordionContainer'
  } = config;
  const { useToggleGroup } = useGlobal();
  const [opened, toggle] = useToggleGroup(group, label, expanded);

  return (
    <Accordion
      square
      expanded={opened}
      disableGutters={disableGutters}
      disabled={disabled}
      onChange={toggle}
      sx={{ boxShadow: 'none' }}
    >
      <AccordionSummary
        expandIcon={<ExpandMoreIcon />}
        aria-controls="panel1bh-content"
        id="panel1bh-header"
      >
        <StyledTitle variant={titleVariant} sx={{ flexShrink: 0 }}>
          {label}
        </StyledTitle>
      </AccordionSummary>
      <StyledDetail variant={detailVariant}>
        {description ? (
          <Typography variant="body2">{description}</Typography>
        ) : null}
        <Body horizontal={variant === 'horizontal'}>
          {map(elements, (config, key) => (
            <Element key={key.toString()} config={config} />
          ))}
        </Body>
      </StyledDetail>
    </Accordion>
  );
}
