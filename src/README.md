# RemindMe - WeThrive Challenge by Matthew Gordon

Welcome to the **RemindMe** application, a simple web app that allows users to create reminders for their schedules. It sends email notifications to users when a reminder is due.

## Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation and Setup](#installation-and-setup)
    - [Prerequisites](#prerequisites)
    - [Cloning the Repository](#cloning-the-repository)
    - [Setting Up the Environment](#setting-up-the-environment)
    - [Database Setup](#database-setup)
    - [Scheduling Tasks](#scheduling-tasks)
- [Running the Application](#running-the-application)
- [Testing](#testing)
- [Usage](#usage)
- [Acknowledgements](#acknowledgements)

## Features

- **User Authentication**: Secure login and logout functionality with token-based authentication.
- **Reminder Management**: Create, view, edit, and delete reminders.
- **Email Notifications**: Receive email notifications when a reminder is due.
- **Token Refresh Mechanism**: Short-lived access tokens with refresh tokens to maintain session security.
- **Automated Testing**: Comprehensive unit and feature tests for backend & frontend components.
- **Responsive Frontend**: User-friendly interface built with [Vue.js](https://vuejs.org/) and [Tailwind CSS](https://tailwindcss.com/).

## Technology Stack

- **Backend**: Laravel Framework
- **Frontend**: Vue.js
- **Database**: SQLite
- **Authentication**: Laravel Sanctum
- **Email Service**: Laravel Mailpit
- **Testing**: PHPUnit for backend tests & Cypress for frontend tests

## Installation and Setup

### Prerequisites

Ensure you have the following installed:

- [PHP](https://www.php.net/downloads) >= 8.1
- [Composer](https://getcomposer.org/download/)
- [Node.js](https://nodejs.org/en/download/) & [NPM](https://www.npmjs.com/get-npm)
- [Git](https://git-scm.com/downloads)

### Cloning the Repository

```bash
git clone https://github.com/mfg9313/remindme-laravel.git
cd remindme-laravel
cd src
```

### Setting Up the Environment

```bash
cp .env.example .env
php artisan key:generate
```

### Database Setup

Database was included in `/src/database/database.sqlite`
However, if there are any issues you can run these commands for quick and easy setup

```bash
touch database/database.sqlite
php artisan migrate
php artisan db:seed
```

### Scheduling Tasks

In `app/Console/Kernel.php` there is a command for running the schedule reminder emails that runs every minute. 
If you run `php artisan schedule:work` this should allow you to run the scheduled task. 
Also, please note that `mailpit` needs to be running.

### Testing without Scheduling

An alternative way to test without running scheduling is to run the command `php artisan reminders:send-reminder-notifications`.
Please note that `mailpit` needs to be running and that task need have `remind_at` in the past in order for emails to process.

## Running the Application

Run the below commands, the app should be accessible at: `http://localhost:8000`

```bash
composer install
npm install
npm run dev
php artisan serve
```

## Testing

For running all test: 
```bash
php artisan test
```

You can also run the test suites by unit and feature:
```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

### Front End Testing

Cypress is used for front end testing. 
Jest was considered but due to vulnerabilities I went with Cypress.

Please use the following command to start the app:
```bash
php artisan serve
npm run dev
npm run cypress:open
```
#### Running Headless
To run Cypress headless (if you don't have chrome), please run the following:

```bash
npx cypress run
```

#### Running With Chrome
If you have chrome and would like to run it in browser. Please run:
```bash
npx cypress open
```
Then click on 'E2E Testing'. 
Click your preferred browser and then click the green button. 
Then you should see the test in the center. 
If you click any of the test, it will run through the test cases. 


## Usage

Please open your browser and go to 'http://localhost:8000'
There will be a login link in the upper right hand corner, or you can go to 'http://localhost:8000/login'

Please use the specified user accounts:
`alice@mail.com`
`123456`

Or you can use:
`bob@mail.com`
`123456`

## Acknowledgements

Thank you so much for the opportunity, I greatly appreciate it. 
If there is anything you need or if you have any questions please feel free to contact me.
