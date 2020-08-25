<?php

/*
Roles.php
Gareth Sears - 2493194S
*/

namespace App\Security;

/**
 * Helper (enum-ish) class to define user roles
 */
class Roles
{
    const LOGGED_IN = 'IS_AUTHENTICATED_FULLY';
    const STUDENT = 'ROLE_STUDENT';
    const INSTRUCTOR = 'ROLE_INSTRUCTOR';
}
