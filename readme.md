# Skeleton Module

[![Latest Version on Packagist](https://img.shields.io/packagist/v/e2tmk/skeleton-module.svg?style=flat-square)](https://packagist.org/packages/e2tmk/skeleton-module)
[![Total Downloads](https://img.shields.io/packagist/dt/e2tmk/skeleton-module.svg?style=flat-square)](https://packagist.org/packages/e2tmk/skeleton-module)
[![License](https://img.shields.io/packagist/l/e2tmk/skeleton-module.svg?style=flat-square)](https://packagist.org/packages/e2tmk/skeleton-module)

The Skeleton module serves as a template for creating new modules in the system. It provides a streamlined structure and a convenient command-line tool to quickly generate new modules based on this skeleton.

## Installation

You can install the package via composer:

```bash
composer require e2tmk/skeleton-module
```

## Requirements

- PHP >= 8.2
- Laravel >= 11.0
- nwidart/laravel-modules >= 9.0

## Module Structure

The Skeleton module contains a basic structure that follows best practices for module development:

- Configuration files
- Service provider setup
- Basic directory structure
- Composer configuration
- Module manifest

## Module Builder Command

The Skeleton module includes a powerful command-line tool called `module-build` that helps you quickly create new modules based on this template.

### Usage

You can create a new module using the command:

```bash
php artisan module-build
```

Or specify the module name directly with the `--name` option:

```bash
php artisan module-build --name=MyNewModule
```

### What it does

The `module-build` command performs the following operations:

1. **Creates a new module directory** based on the provided name
2. **Copies all files** from the Skeleton module to the new module
3. **Renames files and directories** by replacing "Skeleton" with your module name
   - PascalCase substitution: "Skeleton" → "YourModuleName"
   - Lowercase substitution: "skeleton" → "yourmodulename"
4. **Updates file contents** to replace all occurrences of "Skeleton" with your module name
5. **Updates specific files**:
   - `composer.json`: Updates package name and namespaces
   - `module.json`: Updates name, alias, and provider paths
   - Service Provider: Updates namespace, class name, and properties
6. **Cleans up**:
   - Removes `.git` directory if it exists
   - Removes `vendor` directory if it exists
   - Removes the `ModuleBuildCommand` from the new module
7. **Registers the module**:
   - Updates `modules_statuses.json` to enable the new module
8. **Updates autoloader**:
   - Runs `composer dump-autoload` to register the new namespaces

### After Creation

Once your new module is created, it will be:

1. Properly registered in the system
2. Ready for development
3. Accessible through its namespace (`Modules\YourModuleName`)

You can then start adding your specific functionality to the new module!

## Best Practices

- Use the module-build command to ensure consistency across all modules
- Maintain the standard directory structure for new modules
- Follow the naming conventions established by the Skeleton module

## Example

To create a new "Reports" module:

```bash
php artisan module-build --name=Reports
```

This will create a fully functional Reports module with all necessary files and configurations.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Elias Olivtradet](https://github.com/edeoliv)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Support

If you discover any issues or have questions, please [open an issue](https://github.com/e2tmk/skeleton-module/issues) on GitHub.