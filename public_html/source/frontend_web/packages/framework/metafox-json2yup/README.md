# JSON 2 Yup

Build a Yup schema object out of a simple JSON object.

This library has been built for a use case which requires you to store form validation configuration on a server for use on your client side applications.

### Install

```sh
npm install -S json-2-yup

yarn add json-2-yup
```

### Basic Usage

- [Array](docs/array.md)
- [Boolean](docs/boolean.md)
- [Date](docs/date.md)
- [Number](docs/number.md)
- [Object](docs/object.md)
- [String](docs/string.md)
- [When](docs/when.md)
- [Custom Errors](docs/errors.md)

```typescript
import { toYup, NumberTypeSchema } from 'json-2-yup';
import * as yup from 'yup';

// json-2-yup

const schema: NumberTypeSchema = {
  type: 'number',
  strict: true,
  required: true,
  min: 5,
  errors: {
    min: 'My custom min value message',
    required: 'My custom required message'
  }
};

const yupSchema = toYup(schema);

console.log(yupSchema.isValidSync(5)); //true
console.log(yupSchema.isValidSync(1)); //false

// Equivalent to

const yupNumberSchema = yup
  .number()
  .min(5, 'My custom min value message')
  .required('My custom required message')
  .strict(true);

console.log(yupNumberSchema.isValidSync(5)); //true
console.log(yupNumberSchema.isValidSync(1)); //false
```
