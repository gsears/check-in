<?php

namespace App\Security;

class Roles
{
    const LOGGED_IN = 'IS_AUTHENTICATED_FULLY';
    const STUDENT = 'ROLE_STUDENT';
    const INSTRUCTOR = 'ROLE_INSTRUCTOR';
}
