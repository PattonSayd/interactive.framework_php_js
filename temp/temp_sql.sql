    (
        SELECT
            users.id AS id,
            CASE WHEN users.name <> '' THEN users.name
            END AS NAME,
            ('users') AS TABLE_NAME,
            ('current_table') AS current_table,
            
        FROM
           users
        LEFT JOIN categories ON users.parent_id = categories.id
        WHERE
        (
            (users.name LIKE '%te%') OR(users.content LIKE '%te%') OR(users.keywords LIKE '%te%') OR(users.image LIKE '%te%') OR(users.gallery LIKE '%te%') OR(users.alias LIKE '%te%')
        )
    )

UNION

    (
        SELECT
            id AS id,
            CASE WHEN comments.name <> '' THEN comments.name
            END AS NAME,
            ('comments') AS TABLE_NAME,
            NULL,
            NULL
        FROM
            comments
        WHERE
        (
            (comments.name LIKE '%te%') OR(comments.content LIKE '%te%')
        )
    )

UNION

    (
        SELECT
            id AS id,
            CASE WHEN pages.name <> '' THEN pages.name
            END AS NAME,
            ('pages') AS TABLE_NAME,
            NULL,
            NULL
        FROM
            pages
        WHERE
            ((pages.name LIKE '%te%'))
    )

UNION

    (
        SELECT
            id AS id,
            CASE WHEN color.name <> '' THEN color.name
            END AS NAME,
            ('color') AS TABLE_NAME,
            NULL,
            NULL
        FROM
            color
        WHERE
            ((color.name LIKE '%te%'))
    )

        ORDER BY
            current_table DESC,
            ((NAME LIKE '%te%')) DESC
    

    ------------------------------------------------------------------------------------------


    (
        SELECT
            id AS id,
            CASE WHEN users.name <> '' THEN users.name
            END AS NAME,
            ('users') AS TABLE_NAME,
            categories.name AS cat_name
        FROM
            users
        LEFT JOIN categories ON users.parent_id = categories.id
        WHERE
        (
            (users.name LIKE '%on%') OR(users.content LIKE '%on%') OR(users.keywords LIKE '%on%') OR(users.image LIKE '%on%') OR(users.gallery LIKE '%on%') OR(users.alias LIKE '%on%')
        )
    )

UNION

    (
        SELECT
            id AS id,
             CASE WHEN comments.name <> '' THEN comments.name
            END AS NAME,
            ('comments') AS TABLE_NAME,
            NULL
        FROM
            comments
        WHERE
            (
                (comments.name LIKE '%on%') OR(comments.content LIKE '%on%')
            )
    )

UNION

    (
        SELECT
            pages.id AS id,
            CASE WHEN pages.name <> '' THEN pages.name
            END AS NAME,
            ('pages') AS TABLE_NAME,
            ('current_table') AS current_table
        FROM
            pages
        WHERE
            ((pages.name LIKE '%on%'))
    )

UNION

    (
        SELECT
            id AS id,
            CASE WHEN color.name <> '' THEN color.name
            END AS NAME,
            ('color') AS TABLE_NAME,
            NULL
        FROM
            color
        WHERE
           ((color.name LIKE '%on%'))
    )
        ORDER BY
            current_table DESC,
            ((NAME LIKE '%on%')) DESC
    