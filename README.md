# Check In

Check in is an online web platform for administering surveys for CS labs and analysing the responses, with the goal of flagging students at risk and assisting instructors with interventions.

## Demo Application
An evaluation version of the application is hosted at https://qlitmnms3a-vioxgpwe4okw6.eu.s5y.io/ . It will be available until November 30th, then it will be taken down.

Login using the credentials below for the test instructor and user.
  - Student: `test@student.gla.ac.uk` Pass: `password`
  - Instructor: `test@glasgow.ac.uk` Pass: `password`

## Requirements

This project is developed using the [Symfony framework](symfony.com). It is recommended that you follow their [setup instructions and install their CLI tool](https://symfony.com/doc/current/setup.html).

The main dependencies for backend development are:

- [Make](https://www.gnu.org/software/make/manual/make.html): For running the developer build scripts.
- [PHP 7.4 or above](https://www.php.net/manual/en/install.php)
- [Composer](https://getcomposer.org/): For managing PHP dependencies.
- [Symfony CLI](https://symfony.com/download): For serving the Symfony application.
- [Docker / Docker Compose](https://docs.docker.com/compose/install/): For containerising the DB.
- [Cron / Crontab](https://man7.org/linux/man-pages/man8/cron.8.html): For running the application cron tasks.

The main dependencies for frontend development are:

- [Node JS 14.2.0 or above](https://nodejs.org/)
- [Yarn](https://yarnpkg.com/)

In addition, this project uses Symfony Encore, which is a [webpack](https://webpack.js.org/) variant specifically for Symfony. This is installed via the Composer / Yarn package managers, as are other dependencies.

## Installation

All of the below commands should be run from the root of the `check-in` folder, as all commands use relative paths.

1. Run `make dev` for an interactive guide to setting up the development environment. This will prompt you to open terminal windows and run additional commands. Also it will ask you for decisions regarding the following:
   1. Running `make backend/cron_setup` to add a cron task that symfony uses for running the `src/Task/FlagStudentsTask.php` task. This may require administrator privileges / chmod-ing the file for execution. It can be omitted, but student flagging will not function as intended.
   2. Creating application fixtures (fake data). There are two variants:
   - `make fixtures/test`: This creates fixed data which was used for manual acceptance tests (see **Testing** section). The corresponding fixture class is `src/DataFixtures/TestFixtures.php`.
   - `make fixtures/evaluation`: This creates randomised data representative of a computer science programme at a university. The corresponding fixture class is `src/DataFixtures/EvaluationFixtures.php`.

Note: Though the evaluation fixtures have been optimised as much as possible, it generates considerable data (300 students). You might want to grab a coffee while it generates!

## Usage

- Navigate to the local web server in your browser (see **Installation**). You can check the port that the Local Web Server is using using `make backend/status`.
- Login using the credentials below for the test instructor and user.
  - Student: `test@student.gla.ac.uk` Pass: `password`
  - Instructor: `test@glasgow.ac.uk` Pass: `password`
- The evaluation fixtures also produce .csv files in the root directory with all generated user emails. You can use any email from these with the password `password` to login.
- You can stop the server by running `make backend/stop`.
- See `docs/UserGuide.pdf` for a comprehensive user guide.

## Code

All code has been written to be as self documenting as possible. However, comments are included where necessary and to describe key methods

The directory is structured according to Symfony best practices, with additional files and folders specific to this project. The structure is provided below with annotations for important or customised additions.

### Directory Structure and Key Items

**📦.github**

**┗ 📂workflows**

**┃ ┗ 📜php.yml**

<sup>_This contains a continuous integration script which automates builds and tests when creating a pull request on the GitHub repository._ </sup>

**📦.symfony**

<sup>_This contains configuration used for hosting the evaluation application on [Symfony Cloud](https://symfony.com/cloud/)._</sup>

**📦assets**

<sup>_This contains the frontend source code._</sup>

**┣ 📂css**

<sup>_This contains the [SASS CSS](https://sass-lang.com/) files for additional frontend styling._</sup>

**┗ 📂js**

**┃ ┣ 📂lib**

<sup>_This contains javascript libraries and custom code used by the project._</sup>

**┃ ┣ 📂vue**

<sup>_This contains custom [Vue.js](https://vuejs.org/) components for building the XY grid interface._</sup>

**┃ ┣ 📜app.js**

<sup>_This is the first of Webpack's entry points. All javascript to be inserted at the bottom of each page's \<body> tag is imported here._</sup>

**┃ ┗ 📜preload.js**

<sup>_This is the second of Webpack's entry points. All javascript to be inserted at the \<head> of each page is imported here._</sup>

**📦bin**

<sup>_This contains shell and php scripts_</sup>

**┣ ...

┣ 📜dev.sh**

<sup>_This script guides the user through the setup process for development._</sup>

**┗ 📜setup_cron.sh**

<sup>_This script sets up a `crontab` entry for the application._</sup>

**📦config**

<sup>_Configuration files for symfony and its bundles_</sup>

**┣ 📂packages**

<sup>_Contains Symfony bundle configurations for different environments_</sup>

**┃ ┣ ...**

**┃ ┣ 📜twig.yaml**

<sup>_Contains Twig globals and points the application to [custom form object templates](https://symfony.com/doc/current/form/form_themes.html#creating-your-
 own-form-theme)_</sup>

**┣ 📂routes**

<sup>_Route configuration is here. It is set up to use annotations on controller methods._</sup>

**┣ 📂secrets**

**┃ ┗ 📂dev**

<sup>_Contains encrypted [sentiment analysis API](https://monkeylearn.com/) credentials so [other developers can use the api]
 (https://symfony.com/doc/current/configuration/secrets.html). The private key is NOT included in this repository._</sup>

**┣ 📜bundles.php**

<sup>_This lists and imports all external [Symfony bundles](https://symfony.com/doc/current/bundles.html) used in this application._</sup>

**┣ 📜routes.yaml**

<sup>_Adds global routing. Namely redirecting '/' to '/courses'._</sup>

**┗ 📜services.yaml**

<sup>_Sets up [Symfony's service container](https://symfony.com/doc/current/service_container.html) which is used for dependency injection._</sup>

**📦docs**

<sup>_Contains all project documentation._</sup>

**┗ 📂testing**

**┃ ┣ 📂coverage**

**┃ ┃ ┗ 📜index.html**

<sup>_This is entry point for the HTML test coverage report._</sup>

**┃ ┗ 📜ManualAcceptanceTests.pdf**

<sup>_This is the manual acceptance testing report._</sup>

**┗ 📂evaluation**

<sup>_Contains evaluation fixture user accounts in .csv form_</sup>

**📦public**

**┗ 📜index.php**

<sup>_Entry point for the application._</sup>

**📦src**

<sup>_The backend source code_</sup>

**┣ 📂Containers**

**┃ ┣ 📂Risk**

<sup>_Classes for wrapping entities associated with risk calculation and providing helper methods for calculating risk, rendering, etc._</sup>

**┣ 📂Controller**

<sup>_Classes for [page routing and logic](https://symfony.com/doc/current/controller.html)._</sup>

**┣ 📂DataFixtures**

<sup>_Classes for [generating mock data](https://symfony.com/doc/master/bundles/DoctrineFixturesBundle/index.html)._</sup>

**┣ 📂Entity**

<sup>_[ORM classes](https://symfony.com/doc/current/doctrine.html)_</sup>

**┣ 📂Form**

**┃ ┗ 📂Type**

<sup>_[Custom form classes](https://symfony.com/doc/current/form/create_custom_field_type.html) to bind forms to entities_</sup>

**┣ 📂Migrations**

**┣ 📂Provider**

**┃ ┗ 📜DateTimeProvider.php**

<sup>_A class to provide (and mock) the application's current date and time._</sup>

**┣ 📂Repository**

<sup>_Classes for [implementing database queries and returning populated entities](https://symfony.com/doc/current/doctrine.html#querying-for-objects-the-repository)._</sup>

**┣ 📂Security**

**┃ ┣ 📂Voter**

<sup>_Classes for [determining user permissions to routes](https://symfony.com/doc/current/security/voters.html)._</sup>

**┣ 📂Service**

**┃ ┗ 📜BreadcrumbBuilder.php**

<sup>_A utility service for building breadcrumbs._</sup>

**┣ 📂Task**

**┃ ┗ 📜FlagStudentsTask.php**

<sup>_Runs a [periodic cron job](https://github.com/rewieer/TaskSchedulerBundle) which flags students based on course instance configurations_</sup>

**┣ 📂Twig**

**┃ ┗ 📜AppExtension.php**

<sup>_[Custom Twig functions](https://symfony.com/doc/current/templating/twig_extension.html) are created here, namely the `renderRisk()` function which renders `SurveyQuestionResponseRisk` subclasses on the webpage_</sup>

**┗ 📜Kernel.php**

<sup>_[The Symfony kernel](https://symfony.com/doc/current/configuration/front_controllers_and_kernel.html#the-kernel-class) which configures bundles, the [symfony container](https://symfony.com/doc/current/service_container.html) and routes._</sup>

**📦templates**

<sup>_[Twig view templates](https://twig.symfony.com/doc/3.x/) for forms, risk, and pages are here._</sup>

**┣ 📂course**

<sup>_Course page HTML view templates._</sup>

**┣ 📂form**

**┃ ┗ 📜custom_types.html.twig**

<sup>_HTML view template partials for custom forms._</sup>

**┣ 📂lab**

<sup>_Lab page HTML view templates._</sup>

**┣ 📂risk_summary**

<sup>_HTML view template partials for rendering `SurveyQuestionResponseRisk` objects._</sup>

**┣ 📂security**

<sup>_Login page HTML view templates._</sup>

**┣ ...**

**┗ 📜macros.html.twig**

<sup>_Common template partials encapsulated as macro functions._</sup>

**📦tests**

<sup>_Classes for unit and functional tests._</sup>

**📜.env**

**📜.env.test**

<sup>_Environment variables are defined in these files._</sup>

**📜.symfony.cloud.yaml**

<sup>_Symfony cloud configuration for hosting the evaluation app._</sup>


**📜Makefile**

<sup>_Contains aliases for development commands._</sup>

**📜README.md**

**📜composer.json**

<sup>_Contains PHP dependencies for backend code._</sup>

**📜docker-compose.yml**

<sup>_Contains docker compose script for database container creation._</sup>

**📜package.json**

<sup>_Contains node dependencies for frontend code._</sup>

**📜php.ini**

<sup>_PHP configuration for the project._</sup>

**📜phpunit.xml.dist**

<sup>_PHPUnit configuration for the project._</sup>

**📜webpack.config.js**

<sup>_Webpack configuration for building frontend assets._</sup>

## Symfony Bundle Code vs Project Code

Some code is automatically generated by particular Symfony bundles. All code which has been created / modified by myself is annotated with a comment at the top containing:

- The file name
- `Gareth Sears - 2493194S`

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

## Additional Documentation

Over the project's lifecycle, many processes were documented which could not be fully contained in the project's report. These are located in the `docs` folder and are referenced within the report.

## Copyright

Copyright 2020, Gareth Sears, All rights reserved.
