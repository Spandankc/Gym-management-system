# Fitness Hub

PHP/MySQL gym management application based on `modi.pages`, with the project name changed to Fitness Hub.

## Local setup

Start Apache and MySQL in XAMPP Manager. This installation uses MySQL port **3308**. Open:

`http://localhost/Projecti/Gym-Management-System/`

For a new, empty database, run these commands from this directory:

```sh
/Applications/XAMPP/xamppfiles/bin/mysql --no-defaults -h 127.0.0.1 -P 3308 -u root -e "CREATE DATABASE gymnsb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
/Applications/XAMPP/xamppfiles/bin/mysql --no-defaults -h 127.0.0.1 -P 3308 -u root gymnsb < gymnsb.sql
```

For an existing installation, apply the migrations instead of importing the sample database:

```sh
/Applications/XAMPP/xamppfiles/bin/mysql --no-defaults -h 127.0.0.1 -P 3308 -u root gymnsb < migrations/001_fitness_hub.sql
/Applications/XAMPP/xamppfiles/bin/mysql --no-defaults -h 127.0.0.1 -P 3308 -u root gymnsb < migrations/002_payments.sql
/Applications/XAMPP/xamppfiles/bin/mysql --no-defaults -h 127.0.0.1 -P 3308 -u root gymnsb < migrations/003_demo_accounts.sql
```

Optional `FITNESS_DB_HOST`, `FITNESS_DB_PORT`, `FITNESS_DB_USER`, `FITNESS_DB_PASSWORD`, and `FITNESS_DB_NAME` environment variables override the local defaults in `dbcon.php`.

## Accounts and functions

- Members register through `customer/signup.php`, choose a service and membership duration, then log in at `customer/login.php`. The original `customer/index.php` login URL remains available.
- Trainers log in at `staffs/index.php`; administrators log in at `admin/index.php`. The member login page also links to these role logins.
- Admin and trainer demo logins both use username `@spandan` and password `123`. These accounts are included in the sample database and in `migrations/003_demo_accounts.sql`.
- Members manage their own exercise tasks and view announcements, reminders, workout plans, class schedules, progress, membership, and recorded payments.
- Trainers manage assigned members' profiles, progress, training plans, payments, reminders, reports, and their own classes.
- Administrators manage members, trainer assignments, staff, equipment, announcements, reports, training plans, and class schedules.
- Payments renew membership, clear reminders, save a printable receipt and payment history, and contribute their actual amounts to dashboard earnings. Refreshing a saved receipt does not record another payment. Imported sample payment dates are retained; payment amounts predating this history table are not inferred.
- Membership expiry uses the registration or latest payment date and supports one, three, six, and twelve months.

New member and staff passwords are hashed. Existing MD5 member/admin credentials and older trainer credentials remain compatible. Every role is restricted to its own pages, and logout ends the session.

## Reference interpretation

The supplied Pages document defines the application's requirements; its literature-review examples and future recommendations are separate. Class schedules are mentioned without a detailed screen design, so the implementation provides admin/trainer management and member viewing. Training plans use Google Sheets links and the sheet's own sharing permissions.
