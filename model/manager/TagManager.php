<?php
// path: model/manager/TagManager.php
declare(strict_types=1);

namespace model\manager;

use model\interface\ManagerInterface;
use model\mapping\TagMapping;
use model\MyPDO;

class TagManager implements ManagerInterface
{
    private MyPDO $db;

    public function __construct(MyPDO $connect)
    {
        $this->db = $connect;
    }

    /**
     * Retourne les tags utilisés, avec le nombre réel de recettes associées.
     *
     * @return TagMapping[]
     */
    public function getCloud(): array
    {
        $query = $this->db->query(
            'SELECT t.id, t.name, t.slug, COUNT(DISTINCT rt.recipe_id) AS recipe_count
             FROM tags t
             INNER JOIN recipe_tags rt ON rt.tag_id = t.id
             GROUP BY t.id, t.name, t.slug, t.display_order
             ORDER BY t.display_order, t.name'
        );

        $tags = [];
        foreach ($query->fetchAll() as $row) {
            $tags[] = new TagMapping($row);
        }

        return $tags;
    }
}
