<?php

namespace PHPMaker2026\Project1;

return [
    /**
     * User levels
     *
     * @var array<int, string, string>
     * [0] int User level ID
     * [1] string User level name
     * [2] string User level hierarchy
     */
    'user.levels' => [
    ['-2', 'Anonymous', '']
],

    /**
     * User roles
     *
     * @var array<int, string>
     * [0] int User level ID
     * [1] string User role name
     */
    'user.roles' => [
    ['-1', 'ROLE_ADMIN'],
    ['', 'ROLE_UNDEFINED']
],

    /**
     * User level permissions
     *
     * @var array<string, int, int>
     * [0] string Project ID + Table name
     * [1] int User level ID
     * [2] int Permissions
     */
    'user.level.privs' => [
    ['{FE4FB9E5-AAE5-4131-86B3-707F288EED86}accessories', '-2', '0'],
    ['{FE4FB9E5-AAE5-4131-86B3-707F288EED86}admins', '-2', '0'],
    ['{FE4FB9E5-AAE5-4131-86B3-707F288EED86}bookings', '-2', '0'],
    ['{FE4FB9E5-AAE5-4131-86B3-707F288EED86}cart', '-2', '0'],
    ['{FE4FB9E5-AAE5-4131-86B3-707F288EED86}jerseys', '-2', '0'],
    ['{FE4FB9E5-AAE5-4131-86B3-707F288EED86}order_items', '-2', '0'],
    ['{FE4FB9E5-AAE5-4131-86B3-707F288EED86}orders', '-2', '0'],
    ['{FE4FB9E5-AAE5-4131-86B3-707F288EED86}rent_rackets', '-2', '0'],
    ['{FE4FB9E5-AAE5-4131-86B3-707F288EED86}users', '-2', '0']
],

    /**
     * Tables
     *
     * @var array<string, string, string, bool, string>
     * [0] string Table name
     * [1] string Table variable name
     * [2] string Table caption
     * [3] bool Allowed for update (for userpriv.php)
     * [4] string Project ID
     * [5] string URL (for AppController::index)
     */
    'user.level.tables' => [
    ['accessories', 'accessories', 'accessories', true, '{FE4FB9E5-AAE5-4131-86B3-707F288EED86}', 'AccessoriesList'],
    ['admins', 'admins', 'admins', true, '{FE4FB9E5-AAE5-4131-86B3-707F288EED86}', 'AdminsList'],
    ['bookings', 'bookings', 'bookings', true, '{FE4FB9E5-AAE5-4131-86B3-707F288EED86}', 'BookingsList'],
    ['cart', 'cart', 'cart', true, '{FE4FB9E5-AAE5-4131-86B3-707F288EED86}', 'CartList'],
    ['jerseys', 'jerseys', 'jerseys', true, '{FE4FB9E5-AAE5-4131-86B3-707F288EED86}', 'JerseysList'],
    ['order_items', 'order_items', 'order items', true, '{FE4FB9E5-AAE5-4131-86B3-707F288EED86}', 'OrderItemsList'],
    ['orders', 'orders', 'orders', true, '{FE4FB9E5-AAE5-4131-86B3-707F288EED86}', 'OrdersList'],
    ['rent_rackets', 'rent_rackets', 'rent rackets', true, '{FE4FB9E5-AAE5-4131-86B3-707F288EED86}', 'RentRacketsList'],
    ['users', 'users', 'users', true, '{FE4FB9E5-AAE5-4131-86B3-707F288EED86}', 'UsersList']
],
];
