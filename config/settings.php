<?php

/*
 * Central application settings.
 * Change business rules here instead of editing individual pages.
 */

// ---------- Cart / ordering (L8) ----------
define("MAX_QTY_PER_ITEM", 20);          // maximum quantity of one product per order

// ---------- Fulfilment (L9) ----------
define("MIN_LEAD_HOURS", 24);            // earliest delivery/pickup = now + this many hours
define("MAX_NOTES_LENGTH", 500);

// ---------- Loyalty (L4) ----------
// Customers earn 1 point for every LOYALTY_RS_PER_POINT rupees of a completed order.
// Rewards are redeemed with points (see the loyalty_rewards table).
define("LOYALTY_RS_PER_POINT", 1000);

// ---------- Payments (L10) ----------
// Bank details shown to customers who choose "Bank Transfer".
// Fill these in (example: "Bank: ABC Bank | Account name: Little Treats | Account no: 000000000").
// While empty, customers are told that the bakery will contact them with bank details.
define("BANK_TRANSFER_DETAILS", "");

// ---------- Sessions / login protection (S4, S5) ----------
define("ADMIN_IDLE_TIMEOUT", 1800);      // seconds of admin inactivity before re-login (30 min)
define("LOGIN_MAX_ATTEMPTS", 5);         // failed attempts per email before lock
define("LOGIN_MAX_ATTEMPTS_PER_IP", 20); // failed attempts per IP before lock
define("LOGIN_LOCK_MINUTES", 15);

// ---------- Uploads (S7) ----------
define("MAX_UPLOAD_BYTES", 5 * 1024 * 1024);

// ---------- Misc ----------
define("APP_DEBUG", false);              // true shows PHP errors on screen (development only)


// ---------- Shared lists (single source of truth) ----------
define("SITE_NAME", "Little Treats Bakery");

define("ORDER_STATUSES", ["Pending", "Confirmed", "Preparing", "Ready", "Completed", "Cancelled"]);
define("ORDER_FLOW", ["Pending", "Confirmed", "Preparing", "Ready", "Completed"]);   // timeline steps
define("PAYMENT_STATUSES", ["Pending", "Paid", "Failed", "Cancelled"]);
define("PAYMENT_METHODS", ["Cash on Delivery", "Bank Transfer"]);
define("REDEMPTION_STATUSES", ["Pending", "Approved", "Collected", "Cancelled"]);
define("ADMIN_ROLES", ["owner", "staff"]);

// ---------- Lists / paging ----------
define("PAGE_SIZE", 15);

// ---------- Email ----------
// XAMPP has no mail server by default; every message is also written to logs/mail.log
define("MAIL_FROM", "no-reply@littletreats.local");
// Set to true once PHP mail() is configured (SMTP settings in php.ini). While false, emails are only written to the log.
define("MAIL_ENABLED", false);