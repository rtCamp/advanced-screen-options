# Contributing to Advanced Screen Options as a Developer

Code contributions, bug reports, and feature requests are welcome! The following sections provide guidelines for contributing to this project, as well as information about development processes and linting.

## Table of Contents

- [Contributing to Advanced Screen Options as a Developer](#contributing-to-advanced-screen-options-as-a-developer)
  - [Table of Contents](#table-of-contents)
  - [Directory Structure](#directory-structure)
  - [Local setup](#local-setup)
    - [Prerequisites](#prerequisites)
    - [Installation](#installation)
    - [Useful Commands](#useful-commands)
    - [Building the plugin for distribution](#building-the-plugin-for-distribution)
  - [Code Contributions (Pull Requests)](#code-contributions-pull-requests)
    - [Workflow](#workflow)
    - [Code Quality / Code Standards](#code-quality--code-standards)
      - [PHP_CodeSniffer](#php_codesniffer)
      - [PHPStan](#phpstan)
      - [ESLint](#eslint)
      - [Stylelint](#stylelint)
  - [Releasing](#releasing)

## Directory Structure

<details>
<summary> Click to expand </summary>

```bash
.
├── .github/ # GitHub-specific files and CI/CD workflows.
│
├── LICENSE
│
├── assets
│   └── src
│       ├── css
│       │   └── admin.scss
│       └── js
│           └── admin.js
│   # Project documentation.
├── docs/
│   ├── CODE_OF_CONDUCT.md
│   ├── CONTRIBUTING.md     # 👈 You are here.
│   ├── DEVELOPMENT.md
│   ├── INSTALLATION.md
│   └── SECURITY.md
│
│   # PHP source files.
├── inc
│   ├── Autoloader.php
│   ├── Contracts
│   │   ├── Interfaces
│   │   │   └── Registrable.php
│   │   └── Traits
│   │       └── Singleton.php
│   ├── Main.php
│   └── Modules
│       ├── Column_Assignment
│       │   └── Role_Based_Screen_Options.php
│       ├── Core
│       │   ├── Assets.php
│       │   ├── Cache.php
│       │   └── Rest.php
│       ├── Meta
│       │   ├── Abstract_Meta_Box.php
│       │   ├── Meta_Fields.php
│       │   ├── Screen_Initializer.php
│       │   └── Screen_Options_Meta.php
│       ├── Post_Types
│       │   ├── Abstract_Post_Type.php
│       │   └── Advanced_Screen_Options.php
│       └── Settings
│           └── Admin.php
│
├── languages
│   └── advanced-screen-options.pot
│
│   # Build directories
├── build/        # assets built by webpack
├── node_modules/ # Node.js dependencies
├── vendor/       # Composer dependencies
│
├── wp-assets/    # WordPress plugin assets (banners, screenshots)
│
├── advanced-screen-options.php # Root plugin entrypoint.
├── uninstall.php # The plugin uninstaller.
│
│   # Important config files.
│   # .dist suffixes mean there may be a user-customized version without the suffix.
├── .editorconfig
├── .eslintrc.json
├── .nvmrc
├── babel.config.js
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── phpcs.xml.dist
├── phpstan.neon.dist
├── README.md
├── readme.txt
└── webpack.config.js

```

</details>

## Local setup

To set up locally, clone the repository into plugins directory of your WordPress installation:

### Prerequisites

- [Node.js](https://nodejs.org/): v22+ ([NVM](https://nvm.sh/) recommended)
- PHP 8.1+
- [Composer](https://getcomposer.org/)
- A local WordPress installation (WordPress 6.8+)

### Installation

1. Clone the repository into the `wp-content/plugins/` directory of your local WordPress installation:

   ```bash
   git clone https://github.com/rtCamp/advanced-screen-options.git
   ```

2. Change into the project folder and install the development dependencies:

   ```bash
   ## If you're using NVM, make sure to use the correct Node.js version:
   nvm install && nvm use

   ## Then install the NPM dependencies:
   npm install

   # And the Composer dependencies:
   composer install
   ```

3. Build the development assets (or watch for changes):

   ```bash
   npm run build:dev
   # or
   npm run start
   ```

4. Activate **Advanced Screen Options** from the WordPress Plugins screen.

### Useful Commands

#### Installing Dependencies

- `composer install`: Install PHP dependencies.
- `npm install`: Install JavaScript dependencies.

#### Building & Internationalization

- `npm run start`: Watch and compile JS/CSS assets during development.
- `npm run build:dev`: Build unminified development assets.
- `npm run build:prod`: Build minified production assets and optimize Composer autoloader without dev dependencies.
- `npm run plugin-zip`: Build production assets and create `advanced-screen-options.zip`.
- `npm run i18n:make-pot`: Generate `languages/advanced-screen-options.pot` using WP-CLI.

#### Linting and Formatting

- `npm run lint`:          Runs all lint checks in parallel.
- `npm run lint:css`:      Runs stylelint on the CSS code.
- `npm run lint:css:fix`:  Autofixes stylelint issues.
- `npm run lint:js`:       Runs ESLint on the JavaScript code.
- `npm run lint:js:fix`:   Autofixes ESLint issues.
- `npm run lint:php`:      Runs PHPCS linting on the PHP code.
- `npm run lint:php:fix`:  Autofixes PHPCS linting issues.
- `npm run lint:php:stan`: Runs PHPStan static analysis on the PHP code.

### Building the plugin for distribution

To build the plugin for distribution, you can use the following commands:

```bash
# IMPORTANT!: Make sure you've cleaned up any dev-dependencies from Composer first:
composer install --no-dev --optimize-autoloader

# Clean install of node modules.
npm ci

# Create a production-ready build:
npm run build:prod

# Create the zip file for distribution:
npm run plugin-zip
```

## Code Contributions (Pull Requests)

### Workflow

The `develop` branch is used for active development, while `main` contains the current stable release. Always create a new branch from `develop` when working on a new feature or bug fix.

Branches should be prefixed with the type of change (e.g. `feat`, `chore`, `tests`, `fix`, etc.) followed by a short description of the change. For example, a branch for a new feature called "Add new feature" could be named `feat/add-new-feature`.

### Code Quality / Code Standards

This project uses several tools to ensure code quality and standards are maintained:

#### PHP_CodeSniffer

This project uses [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/) to enforce WordPress Coding Standards. We use the [WPGraphQL Coding Standards ruleset](https://github.com/AxeWP/WPGraphQL-Coding-Standards), which is a superset of [WPCS](https://github.com/WordPress/WordPress-Coding-Standards), [VIPCS](https://github.com/Automattic/VIP-Coding-Standards), and [Slevomat Coding Standard](https://github.com/slevomat/coding-standard) tailored for the WPGraphQL ecosystem.

Our specific ruleset is defined in the [`phpcs.xml.dist`](../phpcs.xml.dist) file.

You can run the PHP_CodeSniffer checks using the following command:

```bash
npm run lint:php
```

PHP_CodeSniffer can automatically fix some issues. To fix issues automatically, run:

```bash
npm run lint:php:fix
```

#### PHPStan

This project uses [PHPStan](https://phpstan.org/) to perform static analysis on the PHP code. PHPStan is a PHP Static Analysis Tool that focuses on finding errors in your code without actually running it.

Our specific configuration is defined in the [`phpstan.neon.dist`](../phpstan.neon.dist) file.

You can run PHPStan using the following command:

```bash
npm run lint:php:stan
```

#### ESLint

This project uses [ESLint](https://eslint.org) through `@wordpress/scripts` and `@wordpress/eslint-plugin` for JavaScript linting, following WordPress coding standards and best practices.

Our specific ESLint configuration is defined in the [`.eslintrc.json`](../.eslintrc.json) file.

You can run ESLint on JavaScript files using:

```bash
npm run lint:js
```

To automatically fix JavaScript linting issues:

```bash
npm run lint:js:fix
```

#### Stylelint

This project uses [Stylelint](https://stylelint.io/) through `@wordpress/scripts` for CSS linting, following WordPress coding standards and best practices.

Our specific Stylelint configuration is defined in the [`.stylelintrc.json`](../.stylelintrc.json) file.

You can run Stylelint on CSS files using:

```bash
npm run lint:css
```

## Releasing
1. Ensure all changes are committed and tested.
2. Update changelogs, version numbers, and `n.e.x.t` tags.
3. Push `develop` branch to `main`.
4. Create a new GitHub release draft with the new tag.
