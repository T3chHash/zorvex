<?php

function faoxima_render_compact_badges(array $badgesHtml, string $emptyText = 'ندارد'): string
{
    if (empty($badgesHtml)) {
        return $emptyText;
    }
    if (count($badgesHtml) === 1) {
        return $badgesHtml[0];
    }

    $first = $badgesHtml[0];
    $rest = array_slice($badgesHtml, 1);
    $restCount = count($rest);

    $out = '<div class="fx-compact-badges">';
    $out .= $first;
    $out .= '<span class="fx-compact-more">+' . $restCount . '</span>';
    $out .= '<div class="fx-compact-popover">' . implode('', $badgesHtml) . '</div>';
    $out .= '</div>';

    return $out;
}
