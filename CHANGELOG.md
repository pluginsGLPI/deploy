# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

### Fixed

- Fix plugin installation failing on raw queries executed through `DBmysql::request()`
- Fix fatal error caused by missing `bool` return types on computer group rights methods
- Replace deprecated `Html::displayErrorAndDie()` and `Html::displayRightError()` calls with HTTP exceptions
- CI: fix PHPStan bootstrap for GLPI 11, migrate ESLint configuration to flat config, drop the stray `glpi-project/tools` composer dependency, declare a unique composer autoloader suffix
