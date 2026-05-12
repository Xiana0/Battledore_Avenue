<?php

namespace PHPMaker2026\Project1;

// Language
$language = Language();

// Navbar menu
$topMenu = new Menu("navbar", true, true);
echo $topMenu->toScript();

// Sidebar menu
$sideMenu = new Menu("menu", true, false);
$sideMenu->addMenuItem(1, "mi_accessories", $language->menuPhrase("1", "MenuText"), "AccessoriesList", -1, "", true, false, false, "", "", false, true);
$sideMenu->addMenuItem(2, "mi_admins", $language->menuPhrase("2", "MenuText"), "AdminsList", -1, "", true, false, false, "", "", false, true);
$sideMenu->addMenuItem(3, "mi_bookings", $language->menuPhrase("3", "MenuText"), "BookingsList", -1, "", true, false, false, "", "", false, true);
$sideMenu->addMenuItem(4, "mi_cart", $language->menuPhrase("4", "MenuText"), "CartList", -1, "", true, false, false, "", "", false, true);
$sideMenu->addMenuItem(5, "mi_jerseys", $language->menuPhrase("5", "MenuText"), "JerseysList", -1, "", true, false, false, "", "", false, true);
$sideMenu->addMenuItem(6, "mi_order_items", $language->menuPhrase("6", "MenuText"), "OrderItemsList", -1, "", true, false, false, "", "", false, true);
$sideMenu->addMenuItem(7, "mi_orders", $language->menuPhrase("7", "MenuText"), "OrdersList", -1, "", true, false, false, "", "", false, true);
$sideMenu->addMenuItem(8, "mi_rent_rackets", $language->menuPhrase("8", "MenuText"), "RentRacketsList", -1, "", true, false, false, "", "", false, true);
$sideMenu->addMenuItem(9, "mi_users", $language->menuPhrase("9", "MenuText"), "UsersList", -1, "", true, false, false, "", "", false, true);
echo $sideMenu->toScript();
