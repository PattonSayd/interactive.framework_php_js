SELECT
    comments.name, 
    comments.content, 
    comments.id,
    pages.name as TABLEpagesTABLE_page_name,
    pages.id as TABLEpagesTABLE_id,
    color.contrex as TABLEcolorTABLE_contrex,
    color.id as TABLEcolorTABLE_id 
FROM comments 
    LEFT JOIN comments_pages ON comments.id = comments_pages.com_id 
    LEFT JOIN pages ON comments_pages.page_id = pages.id 
    LEFT JOIN pages color ON pages.parent_id = color.id


    $m->select('comments', [
    'fields' => ['name', 'content'],
    'join' => [
        'comments_pages' => [
            'fields' => null,
            'on' => ['id', 'com_id']
        ],

        'pages' => [
            'fields' => ['name as page_name'],
            'on' => ['page_id', 'id']
        ],

        'pages color' => [
            'fields' => ['contrex'],
            'on' => ['parent_id', 'id']
            ]
        ],
    'join_structure' => true,
    'order_by' => 'RAND()',
    ]);