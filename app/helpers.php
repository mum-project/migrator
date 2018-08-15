<?php

/**
 * Gets the domain of an email address.
 * Since the local part may also contain "@" characters, a simple explode()
 * is not a good idea. Please use this function instead.
 *
 * @param string $address
 * @return string
 */
function getDomainOfEmailAddress(string $address): string
{
    $explodedEmail = explode('@', $address);
    return end($explodedEmail);
}

/**
 * Gets the local part of an email address.
 * Since the local part may also contain "@" characters, a simple explode()
 * is not a good idea. Please use this function instead.
 *
 * @param string $address
 * @return string
 */
function getLocalPartOfEmailAddress(string $address): string
{
    $explodedEmail = explode('@', $address);
    return implode('@', array_slice($explodedEmail, 0, -1));
}

/**
 * Get bytes in gibibytes.
 *
 * @param int $bytes
 * @return int
 */
function getGbFromB(int $bytes): int
{
    return $bytes / 1024 / 1024 / 1024;
}