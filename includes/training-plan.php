<?php

/** Return a safe Google Sheets link compatible with members.plan_link. */
function fitness_training_plan_url($input): ?string
{
    if (!is_string($input)) {
        return null;
    }

    $link = trim($input);
    if ($link === '' || strlen($link) > 255 || filter_var($link, FILTER_VALIDATE_URL) === false) {
        return null;
    }

    $url = parse_url($link);
    if (!$url || strtolower($url['scheme'] ?? '') !== 'https'
        || strtolower($url['host'] ?? '') !== 'docs.google.com'
        || isset($url['user']) || isset($url['pass']) || isset($url['port'])) {
        return null;
    }

    $path = $url['path'] ?? '';
    $spreadsheet = '#^/spreadsheets/(?:u/[0-9]+/)?d/[A-Za-z0-9_-]+(?:/(?:edit|view|preview|htmlview|copy))?/?$#';
    $published = '#^/spreadsheets/d/e/[A-Za-z0-9_-]+/(?:pub|pubhtml)/?$#';
    if (!preg_match($spreadsheet, $path) && !preg_match($published, $path)) {
        return null;
    }

    return $link;
}
