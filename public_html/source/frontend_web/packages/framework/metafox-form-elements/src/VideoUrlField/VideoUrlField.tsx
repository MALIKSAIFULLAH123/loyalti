/**
 * @type: formElement
 * name: form.element.VideoUrl
 */
import { useGlobal, Link } from '@metafox/framework';
import { FormFieldProps } from '@metafox/form';
import { TextField, styled, Box } from '@mui/material';
import { useField } from 'formik';
import { camelCase, debounce, isEmpty } from 'lodash';
import React, { createElement } from 'react';
import Description from '../Description';
import Label from '../Label';
import VideoPlayer from '@metafox/ui/VideoPlayer';
import { TruncateText } from '@metafox/ui';

const name = 'VideoEmbedView';

const PlayerWrapper = styled('div', { name, slot: 'playerWrapper' })(
  ({ theme }) => ({})
);

const TextView = styled('div', {
  name,
  slot: 'title'
})(({ theme }) => ({
  borderBottomLeftRadius: theme.shape.borderRadius,
  borderBottomRightRadius: theme.shape.borderRadius,
  border: theme.mixins.border('secondary'),
  borderTop: 'none',
  padding: theme.spacing(2),
  display: 'flex',
  flexDirection: 'column'
}));

const Text = styled(TextField, {
  name: 'Text'
})(({ theme }) => ({
  '& input::placeholder': {
    color: theme.palette.text.hint
  }
}));

interface PropFetchLink {
  link?: string;
  description: string;
  duration: number;
  embed_code: string;
  height: number;
  width: number;
  image?: string;
  [key: string]: any;
}

const VideoUrlField = ({
  config,
  disabled: forceDisabled,
  name,
  formik
}: FormFieldProps) => {
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'VideoUrlField'
  );
  const { apiClient, compactData } = useGlobal();

  const {
    label,
    hint,
    autoComplete,
    placeholder,
    noFeedback,
    variant,
    disabled,
    margin,
    fullWidth,
    type = 'text',
    rows,
    size,
    required,
    description,
    autoFocus,
    readOnly,
    maxLength,
    fetchEndpoint,
    autoFillValueFromLink = {
      title: ':title',
      text: ':description'
    },
    showEmbed = false
  } = config;

  const query = React.useRef<string>('');
  const [loading, setLoading] = React.useState<boolean>(false);
  const [errorLink, setErrorLink] = React.useState('');
  const [state, setState] = React.useState<PropFetchLink>({});

  const debounceAutoFill = React.useMemo(() => {
    const Search = () => {
      if (query.current === '' || meta.error || loading) {
        return;
      }

      setLoading(true);
      formik.setSubmitting(true);
      apiClient
        .request({
          url: fetchEndpoint || '/link/fetch',
          method: 'post',
          data: { link: query.current }
        })
        .then(res => {
          const response = res.data?.data;
          setErrorLink('');
          showEmbed && setState(response);

          if (!isEmpty(autoFillValueFromLink)) {
            const valueAutoFill = compactData(autoFillValueFromLink, response);

            Object.keys(valueAutoFill).forEach(key =>
              formik.setFieldValue(key, valueAutoFill[key])
            );
          }
        })
        .catch(error => {
          const msg =
            error.response?.data?.error?.message ||
            error.response?.data?.message ||
            error.response?.data?.error ||
            error.message;

          setErrorLink(msg);
        })
        .finally(() => {
          if (!meta?.touched) {
            setTouched(true);
          }

          formik.setSubmitting(false);
          setLoading(false);
        });
    };

    return debounce(Search, 1000);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [apiClient, compactData, loading, setLoading, meta.error]);

  React.useEffect(() => {
    if (field.value === '' || meta.error) {
      return;
    }

    debounceAutoFill();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [meta?.error, field.value]);

  // fix: A component is changing an uncontrolled input
  if (!field.value) {
    field.value = config.defaultValue ?? '';
  }

  // how to handle changed

  const handleChange = evt => {
    setValue(evt.target.value);
    query.current = evt.target.value;
  };

  const errorMsg = errorLink || meta.error;
  const haveError = Boolean(errorMsg && (meta.touched || formik.submitCount));

  const InputVideo = createElement(Text, {
    ...field,
    disabled: disabled || forceDisabled || formik.isSubmitting || loading,
    required,
    label: <Label hint={hint} text={label} />,
    autoFocus,
    variant,
    size,
    'data-testid': camelCase(`field ${name}`),
    inputProps: {
      'data-testid': camelCase(`input ${name}`),
      maxLength,
      autoComplete,
      readOnly
    },
    rows,
    placeholder,
    onChange: handleChange,
    margin,
    error: haveError,
    fullWidth,
    type,
    helperText: noFeedback ? null : haveError ? (
      errorMsg
    ) : description ? (
      <Description text={description} hint={hint} />
    ) : null
  });

  return (
    <>
      {InputVideo}
      {!isEmpty(state) && showEmbed && (
        <Box mt={2}>
          <PlayerWrapper>
            <VideoPlayer
              src={state?.link}
              thumb_url={state?.image}
              autoplayIntersection
              embed_code={state?.embed_code}
              {...state}
            />
          </PlayerWrapper>
          <TextView data-testid="embedview">
            <Box mb={1} fontWeight={600}>
              <Link to={state?.link} asModal>
                <TruncateText variant="h4" lines={1}>
                  {state.title}
                </TruncateText>
              </Link>
            </Box>
            <Box mb={1}>
              <TruncateText variant="body1" lines={2}>
                {state.description}
              </TruncateText>
            </Box>
          </TextView>
        </Box>
      )}
    </>
  );
};

export default VideoUrlField;
