<?php


declare(strict_types=1);

require_once __DIR__ . '/BaseHandler.php';

final class CategoriesHandler extends BaseHandler
{
    public function handle(): void
    {
        $this->requireMethod('GET');

        $codePanel = $this->resolveCountryId();
        if ($codePanel === '') {
            FaoximaResponse::badRequest('country_id is required');
        }
        $panel = $this->loadPanelByCode($codePanel);
        if (!panel_feature_enabled($panel, 'categorygeneral')) {
            FaoximaResponse::ok([]);
        }

        $allCategories = FaoximaDb::fetchAll('SELECT * FROM category');

        $userAgent = !empty($this->user['agent']) ? $this->user['agent'] : 'f';
        $list = [];
        foreach ($allCategories as $cat) {
            $count = (int) FaoximaDb::fetchScalar(
                "SELECT COUNT(*) FROM product
                  WHERE (Location = '/all' OR Location = 'all' OR Location IS NULL OR Location = '' OR :location = '/all' OR FIND_IN_SET(:location, REPLACE(Location, ' ', '')) > 0 OR Location LIKE :location_like)
                    AND (category = :category OR FIND_IN_SET(:category, REPLACE(category, ' ', '')) > 0)
                    AND (agent = :agent OR agent IN ('all', 'allusers', '') OR agent IS NULL OR FIND_IN_SET(:agent, REPLACE(agent, ' ', '')) > 0)",
                [
                    ':location'      => (string)($panel['name_panel'] ?? ''),
                    ':location_like' => '%' . (string)($panel['name_panel'] ?? '') . '%',
                    ':category'      => $cat['remark'],
                    ':agent'         => $userAgent,
                ]
            );
            if ($count === 0 && count($allCategories) > 0) {
                // If specific location has no count, check global count
                $count = (int) FaoximaDb::fetchScalar(
                    "SELECT COUNT(*) FROM product WHERE (category = :category OR FIND_IN_SET(:category, REPLACE(category, ' ', '')) > 0)",
                    [':category' => $cat['remark']]
                );
            }
            if ($count === 0) continue;

            $list[] = [
                'id' => $cat['id'],
                'name' => $cat['remark'],
            ];
        }

        FaoximaResponse::ok($list);
    }
}

