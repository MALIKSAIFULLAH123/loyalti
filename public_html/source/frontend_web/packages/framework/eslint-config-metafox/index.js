const restrictedGlobals = require('confusing-browser-globals');

module.exports = {
  root: true,
  // parser: '@babel/eslint-parser',
  parser: '@typescript-eslint/parser',
  extends: ['eslint:recommended'],
  plugins: [
    'import',
    'flowtype',
    'jsx-a11y',
    'react',
    'react-hooks',
    'jest-dom'
  ],

  env: {
    browser: true,
    commonjs: true,
    es6: true,
    jest: true,
    node: true
  },
  parserOptions: {
    ecmaVersion: 2020,
    sourceType: 'module',
    ecmaFeatures: {
      jsx: true
    }
  },

  settings: {
    react: {
      version: 'detect'
    }
  },
  overrides: [
    {
      files: ['**/*.ts?(x)'],
      // parser: '@typescript-eslint/parser',
      parserOptions: {
        ecmaVersion: 2020,
        sourceType: 'module',
        ecmaFeatures: {
          jsx: true
        },
        // typescript-eslint specific options
        warnOnUnsupportedTypeScriptVersion: true
      },
      plugins: ['@typescript-eslint'],
      // If adding a typescript-eslint version of an existing ESLint rule,
      // make sure to disable the ESLint rule here.
      rules: {
        // TypeScript's `noFallthroughCasesInSwitch` option is more robust (#6906)
        'default-case': 'off',
        // 'tsc' already handles this
        // (https://github.com/typescript-eslint/typescript-eslint/issues/291)
        'no-dupe-class-members': 'off',
        // 'tsc' already handles this
        // (https://github.com/typescript-eslint/typescript-eslint/issues/477)
        'no-undef': 'off',

        // Add TypeScript specific rules (and turn off ESLint equivalents)
        '@typescript-eslint/consistent-type-assertions': 'warn',
        'no-array-constructor': 'off',
        '@typescript-eslint/no-array-constructor': 'warn',
        'no-use-before-define': 'off',
        '@typescript-eslint/no-use-before-define': [
          'error',
          {
            functions: false,
            classes: false,
            variables: false,
            typedefs: false
          }
        ],
        'no-unused-expressions': 'off',
        '@typescript-eslint/no-unused-expressions': [
          'error',
          {
            allowShortCircuit: true,
            allowTernary: true,
            allowTaggedTemplates: true
          }
        ],
        'no-unused-vars': 'off',
        '@typescript-eslint/no-unused-vars': [
          'error',
          {
            args: 'none',
            ignoreRestSiblings: true
          }
        ],
        'no-useless-constructor': 'off',
        '@typescript-eslint/no-useless-constructor': 'warn'
      }
    }
  ],

  // NOTE: When adding rules here, you need to make sure they are compatible with
  // `typescript-eslint`, as some rules such as `no-array-constructor` aren't compatible.
  rules: {
    // http://eslint.org/docs/rules/
    'array-callback-return': 'warn',
    'default-case': [
      'warn',
      {
        commentPattern: '^no default$'
      }
    ],
    eqeqeq: ['warn', 'smart'],
    'new-parens': 'warn',
    'no-array-constructor': 'warn',
    'no-caller': 'warn',
    'no-cond-assign': ['warn', 'except-parens'],
    'no-const-assign': 'error',
    'no-control-regex': 'warn',
    'no-delete-var': 'warn',
    'no-dupe-args': 'warn',
    'no-dupe-class-members': 'error',
    'no-duplicate-imports': [
      2,
      {
        includeExports: false
      }
    ],
    'no-dupe-keys': 'warn',
    'no-duplicate-case': 'warn',
    'no-empty-character-class': 'warn',
    'no-empty-pattern': 'warn',
    'no-eval': 'warn',
    'no-ex-assign': 'warn',
    'no-extend-native': 'warn',
    'no-extra-bind': 'warn',
    'no-extra-label': 'warn',
    'no-fallthrough': 'warn',
    'no-func-assign': 'warn',
    'no-implied-eval': 'warn',
    'no-invalid-regexp': 'warn',
    'no-iterator': 'warn',
    'no-label-var': 'warn',
    'no-labels': [
      'warn',
      {
        allowLoop: true,
        allowSwitch: false
      }
    ],
    'no-lone-blocks': 'warn',
    'no-loop-func': 'warn',
    'no-mixed-operators': [
      'warn',
      {
        groups: [
          ['&', '|', '^', '~', '<<', '>>', '>>>'],
          ['==', '!=', '===', '!==', '>', '>=', '<', '<='],
          ['&&', '||'],
          ['in', 'instanceof']
        ],
        allowSamePrecedence: false
      }
    ],
    'no-multi-str': 'warn',
    'no-native-reassign': 'warn',
    'no-negated-in-lhs': 'warn',
    'no-new-func': 'warn',
    'no-new-object': 'warn',
    'no-new-symbol': 'warn',
    'no-new-wrappers': 'warn',
    'no-obj-calls': 'warn',
    'no-octal': 'warn',
    'no-octal-escape': 'warn',
    'no-redeclare': [
      'warn',
      {
        builtinGlobals: false
      }
    ],
    'no-regex-spaces': 'warn',
    'no-restricted-syntax': ['warn', 'WithStatement'],
    'no-script-url': 'warn',
    'no-self-assign': 'warn',
    'no-self-compare': 'warn',
    'no-sequences': 'warn',
    'no-shadow-restricted-names': 'warn',
    'no-sparse-arrays': 'warn',
    'no-template-curly-in-string': 'warn',
    'no-this-before-super': 'warn',
    'no-throw-literal': 'warn',
    'no-undef': 'error',
    'no-restricted-globals': ['error'].concat(restrictedGlobals),
    'no-unreachable': 'warn',
    'no-unused-expressions': [
      'error',
      {
        allowShortCircuit: true,
        allowTernary: true,
        allowTaggedTemplates: true
      }
    ],
    'no-unused-labels': 'warn',
    'no-unused-vars': [
      'warn',
      {
        args: 'none',
        ignoreRestSiblings: true
      }
    ],
    'no-use-before-define': [
      'warn',
      {
        functions: false,
        classes: false,
        variables: false
      }
    ],
    'no-useless-computed-key': 'warn',
    'no-useless-concat': 'warn',
    'no-useless-constructor': 'warn',
    'no-useless-escape': 'warn',
    'no-useless-rename': [
      'warn',
      {
        ignoreDestructuring: false,
        ignoreImport: false,
        ignoreExport: false
      }
    ],
    'no-with': 'warn',
    'space-before-function-paren': [
      2,
      {
        anonymous: 'always',
        named: 'never',
        asyncArrow: 'always'
      }
    ],
    'template-curly-spacing': [2, 'never'],
    'nonblock-statement-body-position': 0,
    'no-whitespace-before-property': 'warn',
    'react-hooks/exhaustive-deps': 'warn',
    'require-yield': 'warn',
    'yield-star-spacing': [1, 'after'],
    'rest-spread-spacing': [1, 'never'],
    strict: ['warn', 'never'],
    'unicode-bom': ['warn', 'never'],
    'use-isnan': 'warn',
    'wrap-regex': 'off',
    'sort-imports': [
      0,
      {
        ignoreCase: false,
        ignoreDeclarationSort: false,
        ignoreMemberSort: true,
        memberSyntaxSortOrder: ['none', 'all', 'single', 'multiple'],
        allowSeparatedGroups: false
      }
    ],
    'valid-typeof': 'warn',
    'no-restricted-properties': [
      'error',
      {
        object: 'require',
        property: 'ensure',
        message:
          'More info: https://facebook.github.io/create-react-app/docs/code-splitting'
      },
      {
        object: 'System',
        property: 'import',
        message:
          'More info: https://facebook.github.io/create-react-app/docs/code-splitting'
      }
    ],
    'no-multi-spaces': [
      2,
      {
        ignoreEOLComments: true,
        exceptions: {
          Property: true
        }
      }
    ],
    'comma-spacing': [
      2,
      {
        before: false,
        after: true
      }
    ],
    'getter-return': 'warn',
    'import/first': 'error',
    'import/no-amd': 'error',
    'import/no-anonymous-default-export': 'warn',
    'import/no-webpack-loader-syntax': 'error',
    'react/forbid-foreign-prop-types': [
      'warn',
      {
        allowInPropTypes: true
      }
    ],
    'react/jsx-one-expression-per-line': 0,
    'react/jsx-no-literals': 'off',
    'react/jsx-props-no-multi-spaces': 'warn',
    'react/jsx-no-useless-fragment': 'warn',
    'react/jsx-no-script-url': 'error',
    'react/jsx-no-target-blank': 'warn',
    'react/jsx-no-comment-textnodes': 'warn',
    'react/jsx-no-duplicate-props': 'error',
    'react/jsx-no-undef': 'error',
    'react/jsx-sort-props': [
      0,
      {
        callbacksLast: true,
        shorthandFirst: true,
        ignoreCase: true,
        noSortAlphabetically: false,
        reservedFirst: true
      }
    ],
    'react/jsx-pascal-case': [
      'warn',
      {
        allowAllCaps: true,
        ignore: []
      }
    ],
    'react/jsx-wrap-multilines': [
      2,
      {
        declaration: 'parens',
        assignment: 'parens',
        return: 'parens',
        arrow: 'parens',
        condition: 'ignore',
        logical: 'ignore',
        prop: 'ignore'
      }
    ],
    'react/jsx-uses-react': 'warn',
    'react/jsx-uses-vars': 'warn',
    'react/no-danger-with-children': 'warn',
    'react/no-direct-mutation-state': 'warn',
    'react/no-is-mounted': 'warn',
    'react/no-typos': 'error',
    'react/require-render-return': 'error',
    'react/no-string-refs': 'warn',
    'react/no-unsafe': 'warn',
    'react/no-this-in-sfc': 'warn',
    'react/no-unescaped-entities': 'warn',
    'react/no-unknown-property': [
      1,
      {
        ignore: []
      }
    ],
    'react/react-in-jsx-scope': 'error',
    'react/sort-prop-types': 'warn',
    'react/style-prop-object': 'warn',
    'react/forbid-dom-props': 'warn',
    'react/jsx-boolean-value': [1, 'never'],
    'react/jsx-equals-spacing': 'warn',
    'react/jsx-first-prop-new-line': 'warn',
    'react/jsx-child-element-spacing': 'warn',
    'react/jsx-closing-bracket-location': 0,
    'react/jsx-closing-tag-location': 0,
    'react/self-closing-comp': [
      0,
      {
        component: true,
        html: true
      }
    ],
    'react/jsx-max-props-per-line': [
      1,
      {
        maximum: 1,
        when: 'multiline'
      }
    ],
    'react/jsx-fragments': [1, 'syntax'],
    'jsx-a11y/alt-text': 'warn',
    'jsx-a11y/anchor-has-content': 'warn',
    'react/jsx-indent': 0,
    'react/jsx-indent-props': [1, 2],
    'react/jsx-key': 'warn',
    'react/jsx-max-depth': [
      1,
      {
        max: 15
      }
    ],
    'jsx-a11y/anchor-is-valid': [
      'warn',
      {
        aspects: ['noHref', 'invalidHref']
      }
    ],
    'jsx-a11y/aria-activedescendant-has-tabindex': 'warn',
    'jsx-a11y/aria-props': 'warn',
    'jsx-a11y/aria-proptypes': 'warn',
    'jsx-a11y/aria-role': [
      'warn',
      {
        ignoreNonDOM: true
      }
    ],

    // START eslint-recommended
    'no-console': 1,
    'no-constant-condition': 1,
    'no-debugger': 1,
    'no-empty': 1,
    'no-extra-boolean-cast': 1,
    'no-extra-semi': 1,
    'no-inner-declarations': 1,
    'no-irregular-whitespace': 1,
    'no-mixed-spaces-and-tabs': 1,
    // basic others
    indent: 0,
    'max-len': [
      2,
      120,
      4,
      {
        ignoreComments: true,
        ignoreUrls: true
      }
    ],
    'no-tabs': [
      1,
      {
        allowIndentationTabs: false
      }
    ],
    quotes: [2, 'single', { avoidEscape: true }],
    semi: [2, 'always'],
    'no-multiple-empty-lines': [
      2,
      {
        max: 1
      }
    ],
    'comma-dangle': [
      2,
      {
        arrays: 'never',
        objects: 'never',
        imports: 'never',
        exports: 'never',
        functions: 'never'
      }
    ],
    'padding-line-between-statements': [
      2,
      { blankLine: 'always', prev: 'if', next: '*' },
      { blankLine: 'always', prev: 'directive', next: '*' },
      { blankLine: 'always', prev: '*', next: ['return', 'if', 'function'] },
      { blankLine: 'always', prev: 'block-like', next: 'const' },
      {
        blankLine: 'always',
        prev: 'const',
        next: ['block-like', 'multiline-expression']
      },
      { blankLine: 'never', prev: 'import', next: 'import' },
      // { blankLine: 'never', prev: 'export', next: 'export' },
      {
        blankLine: 'always',
        prev: 'import',
        next: ['const', 'function', 'export', 'class']
      },
      { blankLine: 'always', prev: 'block', next: 'multiline-const' }
    ],
    'prefer-arrow-callback': [2],
    'object-shorthand': [2],
    'dot-location': [1, 'property'],
    'one-var': [2, 'never'],
    'no-var': [2],
    'prefer-template': [1],
    'prefer-const': ['error'],
    'prefer-destructuring': [0],
    'no-bitwise': [0],
    'id-length': [
      'error',
      {
        properties: 'never',
        exceptions: [
          'x',
          'y',
          'q',
          'i',
          'I',
          'e',
          'u',
          'n',
          'k',
          'a',
          'b',
          'c',
          's',
          't',
          '_',
          'v',
          'w',
          'h'
        ]
      }
    ],
    'func-names': [1, 'always'],
    'no-confusing-arrow': [
      1,
      {
        allowParens: true
      }
    ],
    yoda: 0,
    'object-curly-spacing': [2, 'always'],
    'array-bracket-spacing': 0,
    'no-class-assign': 'error',
    'space-unary-ops': [
      2,
      {
        words: false,
        nonwords: false
      }
    ],
    'switch-colon-spacing': [
      2,
      {
        after: true,
        before: false
      }
    ],
    'keyword-spacing': [
      'error',
      {
        after: true
      }
    ],
    'space-infix-ops': [
      2,
      {
        int32Hint: false
      }
    ],
    'space-before-blocks': [2, 'always'],
    'space-in-parens': [2, 'never'],
    'spaced-comment': [2, 'always', { markers: ['/'] }],
    'arrow-spacing': [
      2,
      {
        before: true,
        after: true
      }
    ],
    'arrow-parens': 0,
    'linebreak-style': [1, 'unix'],
    'operator-linebreak': 0,
    'function-paren-newline': 0,
    'react/prefer-stateless-function': 'off',
    'import/newline-after-import': [0],
    'react/jsx-filename-extension': [0],
    'react/forbid-prop-types': [0],
    'import/no-named-as-default': [0],
    'import/no-named-as-default-member': [0],
    'import/no-extraneous-dependencies': [0],
    'import/no-unresolved': [0],
    'import/extensions': [0],
    // END eslint-recommended
    'jsx-a11y/aria-unsupported-elements': 'warn',
    'jsx-a11y/heading-has-content': 'warn',
    'jsx-a11y/iframe-has-title': 'warn',
    'jsx-a11y/img-redundant-alt': 'warn',
    'jsx-a11y/no-access-key': 'warn',
    'jsx-a11y/no-distracting-elements': 'warn',
    'jsx-a11y/no-redundant-roles': 'warn',
    'jsx-a11y/role-has-required-aria-props': 'warn',
    'jsx-a11y/role-supports-aria-props': 'warn',
    'jsx-a11y/scope': 'warn',
    'react-hooks/rules-of-hooks': 'error',
    'flowtype/define-flow-type': 'warn',
    'flowtype/require-valid-file-annotation': 'warn',
    'flowtype/use-flow-type': 'warn',

    // eslint-plugin-jest-dom
    // https://github.com/testing-library/eslint-plugin-jest-dom
    'jest-dom/prefer-checked': 'error',
    'jest-dom/prefer-enabled-disabled': 'error',
    'jest-dom/prefer-required': 'error',
    'jest-dom/prefer-to-have-attribute': 'error'
  }
};
