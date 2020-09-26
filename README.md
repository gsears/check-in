# Check In

Check in is an online web platform for administering surveys for CS labs and analysing the responses, with the goal of flagging students at risk and assisting instructors with interventions.

## Requirements

This project is developed using the [Symfony framework](symfony.com). It is recommended that you follow their [setup instructions and install their CLI tool](https://symfony.com/doc/current/setup.html).

The main dependencies for backend development are:

- [Make](https://www.gnu.org/software/make/manual/make.html)
- [PHP 7.4 or above](https://www.php.net/manual/en/install.php)
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download)
- [Cron / Crontab](https://man7.org/linux/man-pages/man8/cron.8.html)

In addition, this project uses Symfony Encore, which is a [webpack](https://webpack.js.org/) variant specifically for Symfony. Instructions for its [installation can be found on the symfony site](https://symfony.com/doc/current/frontend/encore/installation.html).

The main dependencies for frontend development are:

- [Node JS 14.2.0 or above](https://nodejs.org/)
- [Yarn](https://yarnpkg.com/)

Other dependencies are fetched using the Composer and Yarn package managers.

## Directory Structure

The directory is structured according to Symfony best practices, with additions added to facilitate development. The structure is provided below with annotations for important or customised additions:

**📦.github
┗ 📂workflows
┃ ┗ 📜php.yml**
<sup>_This contains a continuous integration script which automates builds and testing when pushing to the GitHub repository._ </sup>
**📦.symfony**
<sup>_This contains configuration used for hosting the evaluation application using [Symfony Cloud](https://symfony.com/cloud/)_</sup>
**📦assets**
<sup>_This contains the frontend files_</sup>
**┣ 📂css**
<sup>_This contains the [SASS CSS](https://sass-lang.com/) files_</sup>
**┗ 📂js
┃ ┣ 📂components**
<sup>_This contains [Vue.js](https://vuejs.org/) components_</sup>
**┃ ┣ ...
┃ ┣ 📜app.js**
<sup>_This is Webpack's main entry point. Other JS is imported here._</sup>
**📦bin**
<sup>_This contains shell and php scripts_</sup>
**┣ ...
┣ 📜dev.sh**
<sup>_This script guides the user through development setup_</sup>
**┗ 📜setup_cron.sh**
<sup>_This script sets up a `crontab` entry for the application_</sup>
**📦config**
<sup>_Configuration files for symfony and its bundles_</sup>
**┣ 📂packages**
<sup>_Contains Symfony bundle configurations for different environments_</sup>
**┃ ┣ ...
┃ ┣ 📜twig.yaml**
<sup>_Contains Twig globals and links to custom form renderers for XY components_</sup>
**┣ 📂routes**
<sup>_Route configuration is here. It is set up to use annotations on controller methods._</sup>
**┣ 📂secrets
┃ ┗ 📂dev**
<sup>_Contains encrypted sentiment analysis API credentials so other developers can use the api. The private key is NOT included in the source code._</sup>
**┣ 📜bundles.php**
<sup>_This lists and imports all external bundles used in this application._</sup>
**┣ 📜routes.yaml**
<sup>_Adds global routing. Namely redirecting '/' to '/courses'._</sup>
**┗ 📜services.yaml**
<sup>_Sets up [Symfony's service container](https://symfony.com/doc/current/service_container.html) which is used for dependency injection._</sup>
**📦docs**
<sup>_Contains all project documentation._</sup>
**┗ 📂coverage
┃ ┗ 📜index.html**
<sup>_This is entry point for the HTML test coverage report._</sup>
**┗ 📂evaluation**
<sup>_Contains evaluation fixture user accounts in .csv form_</sup>
**📦public
┣ 📜head.js**
<sup>_Essential javascript functions which need to be loaded in a web page header._</sup>
**┗ 📜index.php**
<sup>_Entry point for the application._</sup>
**📦src**
<sup>_The backend source code_</sup>
**┣ 📂Containers
┃ ┣ 📂Risk**
<sup>_Wrapper classes used for storage and calculations on entities. Risk calculation takes place here._</sup>
**┣ 📂Controller**
<sup>_The route and page logic is contained in these classes_</sup>
**┣ 📂DataFixtures**
<sup>_These classes generate mock data_</sup>
**┣ 📂Entity**
<sup>_These classes implement the ORM_</sup>
**┣ 📂Form
┃ ┗ 📂Type**
<sup>_These classes are used to map entities to forms_</sup>
**┣ 📂Migrations
┣ 📂Provider
┃ ┗ 📜DateTimeProvider.php**
<sup>_This class provides the application date and time. It is used to generate an artificial date time for evaluation._</sup>
**┣ 📂Repository**
<sup>_These classes are used to fetch entities from the database using Doctrine Query Language queries_</sup>
**┣ 📂Security
┃ ┣ 📂Voter**
<sup>_Provides the security classes for determining user permissions to routes_</sup>
**┣ 📂Service
┃ ┗ 📜BreadcrumbBuilder.php**
<sup>_A utility service for building breadcrumbs_</sup>
**┣ 📂Task
┃ ┗ 📜FlagStudentsTask.php**
<sup>_Runs a periodic cron job which flags students based on course instance configurations_</sup>
**┣ 📂Twig
┃ ┗ 📜AppExtension.php**
<sup>_Custom Twig functions are created here, namely the `renderRisk()` function which renders `SurveyQuestionResponseRisk` subclasses on the webpage_</sup>
**┗ 📜Kernel.php**
<sup>_The Symfony kernel which handles all requests and responses._</sup>
**📦templates
┣ 📂course**
<sup>_Course page HTML view templates._</sup>
**┣ 📂form
┃ ┗ 📜custom_types.html.twig**
<sup>_HTML view template partials for custom XY component forms._</sup>
**┣ 📂lab**
<sup>_Lab page HTML view templates._</sup>
**┣ 📂risk_summary**
<sup>_HTML view template partials for displaying `SurveyQuestionResponseRisk` subclasses._</sup>
**┣ 📂security**
<sup>_Login page HTML view templates._</sup>
**┣ ...
┗ 📜macros.html.twig**
<sup>_Common template partials encapsulated as macro functions._</sup>
**📦tests**
<sup>_Classes for unit and functional tests._</sup>
**┣ 📜.env
┣ 📜.env.test**
<sup>_Environment variables are defined in these files._</sup>
**┣ 📜.symfony.cloud.yaml**
<sup>_Symfony cloud configuration._</sup>
**┣ 📜Makefile**
<sup>_Contains aliases for common commands._</sup>
**┣ 📜README.md
┣ 📜composer.json**
<sup>_Contains PHP dependencies for backend code._</sup>
**┣ 📜docker-compose.yml**
<sup>_Contains docker compose script for database container creation._</sup>
**┣ 📜package.json**
<sup>_Contains node dependencies for frontend code._</sup>
**┣ 📜php.ini**
<sup>_PHP configuration for the project._</sup>
**┣ 📜phpunit.xml.dist**
<sup>_PHPUnit configuration for the project._</sup>
**┗ 📜webpack.config.js**
<sup>_Webpack configuration for building frontend assets._</sup>

## Installation

All of the below commands should be run from the root of the `check-in` folder, as all commands use relative paths.

1. Run `make dev` for an interactive guide to setting up the development environment.
2. Run `make backend/cron_setup` to add a cron task that symfony uses for running the `src/Task/FlagStudentsTask.php` task. This may require administrator privileges. It can be omitted, but student flagging will not function as expected.
3. Create application fixtures (fake data). There are two variants:
   - `make fixtures/test`: This creates fixed data which was used for manual acceptance tests (see **Testing** section). The corresponding fixture class is `src/DataFixtures/TestFixtures.php`.
   - `make fixtures/evaluation`: This creates randomised data representative of a computer science programme at a university. The corresponding fixture class is `src/DataFixtures/EvaluationFixtures.php`.

Note: Though the evaluation fixtures have been optimised as much as possible, it generates considerable data (300 students). You might want to grab a coffee while it generates!

## Usage

- Navigate to the local web server in your browser (see **Installation**). You can check the port that the Local Web Server is using using `make backend/status`.
- Login using the credentials below, for the test instructor and user.
  - Student: `test@student.gla.ac.uk` Pass: `password`
  - Instructor: `test@glasgow.ac.uk` Pass: `password`
- The evaluation fixtures also produce .csv files in the root directory with all generated user emails. You can use any email from these with the password `password` to login.
- You can stop the server by running `make backend/stop`.
- See `docs/UserGuide.pdf` for a comprehensive user guide.

## Testing

All automated tests are located in the `tests` folder. Subfolders correspond to subfolders in the `src` folder. The naming convention `*Test.php` is used, where `*` is the class name under test. The test runner is [phpunit](https://phpunit.de/).

You can run the full test suite using the command `make test/all`.

Coverage of automated tests can be found in the `coverage` folder, which is generated using `make test/coverage`.

Manual acceptance tests are defined and documented in `docs/AcceptanceTests.pdf`.

### Unit tests

Unit tests can be run using the `make test/unit` command. These unit tests use mocks to ensure each test is self contained.

This suite currently tests:

- `Containers` classes. These are responsible for calculating student risk based on their lab responses and performing sanity checks on grouped data (for example, ensuring course start dates are before end dates).
- `Entity` classes. These are the ORM classes and contain some important data validation checks.
- `Security`, which are the [voter](https://symfony.com/doc/current/security/voters.html) classes which are used to make elaborate permission checks in controllers using database queries.

### Functional tests

Functional tests can be run using the `make test/functional` command. These are integration tests which perform database transactions.

The `DataFixtures/EntityCreator.php` class is used to generate test specific entities and any transactions are 'rolledback' after each test using the [dama/doctrine-test-bundle](https://github.com/dmaicher/doctrine-test-bundle).

All functional tests inherit from the `FunctionalTestCase.php` abstract class, which created to provide useful utility functions and access to essential [services](https://symfony.com/doc/current/service_container.html).

This suite currently tests:

- Route permissions and security handling in the `Controller` classes. This is done by automating logins and page navigation and testing if (in)valid responses are given.
- `Entity` classes which use annotation hooks to run methods during a database transaction.
- `Repository` classes, whose methods are responsible for querying the database and fetching entities.

## Source code

Some code is automatically generated by particular Symfony bundles. All code which has been created and modified by myself is annotated with a comment at the top containing:

- The file name
- `Gareth Sears - 2493194S`

All code has been written to be as self documenting as possible. However, comments are included where necessary and to describe key methods.

## Additional Documentation

Over the project's lifecycle, many processes were documented which could not be fully contained in the project's report. These are located in the `docs` folder and are referenced within the report.

## Copyright

Copyright 2020, Gareth Sears, All rights reserved.
