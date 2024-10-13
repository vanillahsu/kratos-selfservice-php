<?php

function getUrlForFlow(string $base, string $flow, array $params)
{
    $url = $base . "/self-service/$flow/browser";

    $query = join('&', $params);
    if (strlen($query) > 0) {
        $url .= "?$query";
    }

    return $url;
}

# vim: set et sw=4 sts=4 ts=4:
