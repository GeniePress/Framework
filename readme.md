## About GeniePress

GeniePress is a WordPress plugin and theme framework with expressive, elegant syntax.

## Learning GeniePress

Have a look at the [GeniePress documentation](https://geneipress.org)

## Security Vulnerabilities

If you discover a security vulnerability within GeniePress, please send an e-mail to Sunil Jaiswal via [sunil@lnk7.com](mailto:sunil@lnk7.com).

All security vulnerabilities will be promptly addressed.

## License

The GeniePress framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Version 2.3.0

## Change Log

### 2.3.0

- `setDefaults()` now looks at field definitions
- Move to Twig 3

### 2.2.0

- New `cast()` method for fields definitions.

### 2.1.0

- Code cleanup
- `BackgroundJob` now accepts a list of actions to call rather than a list of callbacks.

### 2.0.2

- Hide warnings on SendEmail

### 2.0.1

- `CreateTaxonomy` and `CreateSchema` are now activated on plugin activation
- New methods in CreateTaxonomy to get the definition and set and get the taxonomy

### 2.0.0

- `CustomPost` methods `beforeSave`, `afterSave`, `checkValidity`, `beforeCache`, `afterRead` and `override` visibility changed to `protected`.
- `CustomPost` method `preDelete` renamed to `beforeDelete` and visibility changed to `protected`
- `CustomPost` method `save` return `$this` rather than the ID
- Attaching schema to a `CustomPost` is now handled in `CreateSchema`
- Missing `getActionName` added to `AjaxHandler`
- Added Field filters and actions `formatValue`, `loadField`, `loadValue`, `prepareField`, `renderField`, `updateField`, `updateValue`, `validateAttachment` and `validateValue`
- New `setHookPrefix` method on `Genie` that will prefix all hooks and filters.

### 1.1.1

- Removed dependencies for Laravel Collection and Symphony EnglishInflector
